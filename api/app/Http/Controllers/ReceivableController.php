<?php

namespace App\Http\Controllers;

use App\Actions\Receivable\ReceivableActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\ExecuteGetDTO;
use App\DTOs\ExecutePaginationDTO;
use App\DTOs\ReceivableCreateDTO;
use App\DTOs\ReceivableUpdateDTO;
use App\Helpers\HashidsHelper;
use App\Http\Requests\Receivable\ReceivableStoreRequest;
use App\Http\Requests\Receivable\ReceivableUpdateRequest;
use App\Http\Resources\ReceivableResource;
use App\Models\Receivable;
use App\Rules\ExistsForCompany;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCompany;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReceivableController extends BaseController
{
    public function __construct(
        private readonly ReceivableActions $receivableActions,
    ) {
        parent::__construct();
    }

    public function readAny(Request $request)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('viewAny', Receivable::class);

        $request->merge([
            'company_id' => $request->filled('company_id') ? HashidsHelper::decodeId($request->company_id) : null,
            'branch_id' => $request->filled('branch_id') ? HashidsHelper::decodeId($request->branch_id) : null,
            'category_id' => $request->filled('category_id') ? HashidsHelper::decodeId($request->category_id) : null,
            'customer_id' => $request->filled('customer_id') ? HashidsHelper::decodeId($request->customer_id) : null,
            'include_id' => $request->filled('include_id') ? HashidsHelper::decodeId($request->include_id) : null,
        ]);

        $validatedRequest = $request->validate([
            'with_trashed' => ['required', 'boolean'],
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'branch_id' => ['nullable', 'integer', new IsValidBranch($request->company_id, false)],
            'search' => ['nullable', 'string'],

            'category_id' => ['nullable', 'integer', new ExistsForCompany('receivable_categories', $request->company_id)],
            'customer_id' => ['nullable', 'integer', new ExistsForCompany('customers', $request->company_id)],
            'is_paid_off' => ['nullable', 'boolean'],
            'include_id' => ['nullable', 'integer', new ExistsForCompany('receivables', $request->company_id)],

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
            $result = $this->receivableActions->readAny(
                withTrashed: $validatedRequest['with_trashed'],
                companyId: $validatedRequest['company_id'],
                branchId: $validatedRequest['branch_id'] ?? null,
                search: $validatedRequest['search'] ?? null,

                categoryId: $validatedRequest['category_id'] ?? null,
                customerId: $validatedRequest['customer_id'] ?? null,
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

        return is_null($result) ? response()->error($errorMsg) : ReceivableResource::collection($result);
    }

    public function read(Receivable $receivable)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('view', $receivable);

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->receivableActions->read($receivable);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : new ReceivableResource($result);
    }

    public function store(ReceivableStoreRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            $isUniqueCode = $this->receivableActions->isUniqueCode(
                $validatedRequest['company_id'],
                $validatedRequest['code'],
                null,
            );
            if (! $isUniqueCode) return response()->error(['code' => [trans('rules.unique_code')]], 422);

            DB::beginTransaction();
            $dto = new ReceivableCreateDTO(
                companyId: $validatedRequest['company_id'],
                branchId: $validatedRequest['branch_id'],
                code: $validatedRequest['code'],
                date: $validatedRequest['date'],
                categoryId: $validatedRequest['category_id'],
                customerId: $validatedRequest['customer_id'],
                cashAccountId: $validatedRequest['cash_account_id'],
                directAmountReceived: (float) $validatedRequest['direct_amount_received'],
                openingAmountDue: (float) $validatedRequest['opening_amount_due'],
                dueDays: $validatedRequest['due_days'],
                remarks: $validatedRequest['remarks'],
                payments: $validatedRequest['payments'],
            );
            $result = $this->receivableActions->create(
                data: $dto,
            );
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function update(Receivable $receivable, ReceivableUpdateRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            $isUniqueCode = $this->receivableActions->isUniqueCode(
                $receivable->company_id,
                $validatedRequest['code'],
                $receivable->id,
            );
            if (! $isUniqueCode) return response()->error(['code' => [trans('rules.unique_code')]], 422);

            DB::beginTransaction();
            $result = $this->receivableActions->update(
                receivable: $receivable,
                data: new ReceivableUpdateDTO(
                    code: $validatedRequest['code'],
                    date: $validatedRequest['date'],
                    categoryId: $validatedRequest['category_id'],
                    customerId: $validatedRequest['customer_id'],
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

    public function delete(Receivable $receivable)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('delete', $receivable);

        $result = false;
        $errorMsg = '';

        try {
            DB::beginTransaction();
            $result = $this->receivableActions->delete($receivable);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
