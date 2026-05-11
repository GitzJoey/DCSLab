<?php

namespace App\Http\Controllers;

use App\Actions\Debt\DebtActions;
use App\DTOs\DebtCreateDTO;
use App\DTOs\DebtUpdateDTO;
use App\DTOs\ExecuteDTO;
use App\DTOs\ExecuteGetDTO;
use App\DTOs\ExecutePaginationDTO;
use App\Helpers\HashidsHelper;
use App\Http\Requests\Debt\DebtStoreRequest;
use App\Http\Requests\Debt\DebtUpdateRequest;
use App\Http\Resources\DebtResource;
use App\Models\Debt;
use App\Rules\ExistsForCompany;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCompany;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DebtController extends BaseController
{
    public function __construct(
        private readonly DebtActions $debtActions,
    ) {
        parent::__construct();
    }

    public function readAny(Request $request)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('viewAny', Debt::class);

        $request->merge([
            'company_id' => $request->filled('company_id') ? HashidsHelper::decodeId($request->company_id) : null,
            'branch_id' => $request->filled('branch_id') ? HashidsHelper::decodeId($request->branch_id) : null,
            'category_id' => $request->filled('category_id') ? HashidsHelper::decodeId($request->category_id) : null,
            'creditor_id' => $request->filled('creditor_id') ? HashidsHelper::decodeId($request->creditor_id) : null,
            'supplier_id' => $request->filled('supplier_id') ? HashidsHelper::decodeId($request->supplier_id) : null,
            'include_id' => $request->filled('include_id') ? HashidsHelper::decodeId($request->include_id) : null,
        ]);

        $validatedRequest = $request->validate([
            'with_trashed' => ['required', 'boolean'],
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'branch_id' => ['nullable', 'integer', new IsValidBranch($request->company_id, false)],
            'search' => ['nullable', 'string'],

            'category_id' => ['nullable', 'integer', new ExistsForCompany('debt_categories', $request->company_id)],
            'creditor_id' => ['nullable', 'integer', new ExistsForCompany('debt_creditors', $request->company_id)],
            'supplier_id' => ['nullable', 'integer', new ExistsForCompany('suppliers', $request->company_id)],
            'is_paid_off' => ['nullable', 'boolean'],
            'include_id' => ['nullable', 'integer', new ExistsForCompany('debts', $request->company_id)],

            'refresh' => ['required', 'boolean'],
            'paginate' => ['nullable', 'array', 'required_without:get', 'prohibits:get'],
            'paginate.page' => ['required_with:paginate', 'integer', 'min:1'],
            'paginate.per_page' => ['required_with:paginate', 'integer', 'min:1'],
            'get' => ['nullable', 'array', 'required_without:paginate', 'prohibits:paginate'],
            'get.limit' => ['required_with:get', 'integer', 'min:1'],
        ]);

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->debtActions->readAny(
                withTrashed: $validatedRequest['with_trashed'],
                companyId: $validatedRequest['company_id'],
                branchId: $validatedRequest['branch_id'] ?? null,
                search: $validatedRequest['search'] ?? null,

                categoryId: $validatedRequest['category_id'] ?? null,
                creditorId: $validatedRequest['creditor_id'] ?? null,
                supplierId: $validatedRequest['supplier_id'] ?? null,
                isPaidOff: $validatedRequest['is_paid_off'] ?? null,
                includeId: $validatedRequest['include_id'] ?? null,

                execute: new ExecuteDTO(
                    useCache: ! $validatedRequest['refresh'],
                    pagination: isset($validatedRequest['paginate'])
                        ? new ExecutePaginationDTO(
                            page: $validatedRequest['paginate']['page'],
                            perPage: $validatedRequest['paginate']['per_page'],
                        )
                        : null,
                    get: isset($validatedRequest['get'])
                        ? new ExecuteGetDTO(limit: $validatedRequest['get']['limit'])
                        : null,
                ),
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : DebtResource::collection($result);
    }

    public function read(Debt $debt)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('view', $debt);

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->debtActions->read($debt);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : new DebtResource($result);
    }

    public function store(DebtStoreRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            $isUniqueCode = $this->debtActions->isUniqueCode(
                $validatedRequest['company_id'],
                $validatedRequest['code'],
                null,
            );
            if (! $isUniqueCode) return response()->error(['code' => [trans('rules.unique_code')]], 422);

            DB::beginTransaction();
            $dto = new DebtCreateDTO(
                companyId: $validatedRequest['company_id'],
                branchId: $validatedRequest['branch_id'],
                code: $validatedRequest['code'],
                date: $validatedRequest['date'],
                categoryId: $validatedRequest['category_id'],
                creditorId: $validatedRequest['creditor_id'],
                supplierId: $validatedRequest['supplier_id'],
                cashAccountId: $validatedRequest['cash_account_id'],
                directAmountReceived: (float) $validatedRequest['direct_amount_received'],
                openingAmountDue: (float) $validatedRequest['opening_amount_due'],
                dueDays: $validatedRequest['due_days'],
                remarks: $validatedRequest['remarks'],
                payments: $validatedRequest['payments'],
            );
            $result = $this->debtActions->create(
                data: $dto,
            );
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function update(Debt $debt, DebtUpdateRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            $isUniqueCode = $this->debtActions->isUniqueCode(
                $debt->company_id,
                $validatedRequest['code'],
                $debt->id,
            );
            if (! $isUniqueCode) return response()->error(['code' => [trans('rules.unique_code')]], 422);

            DB::beginTransaction();
            $result = $this->debtActions->update(
                debt: $debt,
                data: new DebtUpdateDTO(
                    code: $validatedRequest['code'],
                    date: $validatedRequest['date'],
                    categoryId: $validatedRequest['category_id'],
                    creditorId: $validatedRequest['creditor_id'],
                    supplierId: $validatedRequest['supplier_id'],
                    cashAccountId: $validatedRequest['cash_account_id'],
                    directAmountReceived: (float) $validatedRequest['direct_amount_received'],
                    openingAmountDue: (float) $validatedRequest['opening_amount_due'],
                    dueDays: $validatedRequest['due_days'],
                    remarks: $validatedRequest['remarks'],
                    deletePaymentIds: $validatedRequest['delete_payment_ids'],
                    payments: $validatedRequest['payments'],
                ),
            );
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(Debt $debt)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('delete', $debt);

        $result = false;
        $errorMsg = '';

        try {
            DB::beginTransaction();
            $result = $this->debtActions->delete($debt);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
