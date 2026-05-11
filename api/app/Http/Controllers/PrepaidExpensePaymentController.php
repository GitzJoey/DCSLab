<?php

namespace App\Http\Controllers;

use App\Actions\PrepaidExpensePayment\PrepaidExpensePaymentActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\ExecuteGetDTO;
use App\DTOs\ExecutePaginationDTO;
use App\DTOs\PrepaidExpensePaymentCreateDTO;
use App\DTOs\PrepaidExpensePaymentUpdateDTO;
use App\Helpers\HashidsHelper;
use App\Http\Requests\PrepaidExpensePayment\PrepaidExpensePaymentStoreRequest;
use App\Http\Requests\PrepaidExpensePayment\PrepaidExpensePaymentUpdateRequest;
use App\Http\Resources\PrepaidExpensePaymentResource;
use App\Models\PrepaidExpensePayment;
use App\Rules\ExistsForCompany;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCompany;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PrepaidExpensePaymentController extends BaseController
{
    public function __construct(
        private readonly PrepaidExpensePaymentActions $prepaidExpensePaymentActions,
    ) {
        parent::__construct();
    }

    public function readAny(Request $request)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('viewAny', PrepaidExpensePayment::class);

        $request->merge([
            'company_id' => $request->filled('company_id') ? HashidsHelper::decodeId($request->company_id) : null,
            'branch_id' => $request->filled('branch_id') ? HashidsHelper::decodeId($request->branch_id) : null,
            'prepaid_expense_id' => $request->filled('prepaid_expense_id') ? HashidsHelper::decodeId($request->prepaid_expense_id) : null,
            'include_id' => $request->filled('include_id') ? HashidsHelper::decodeId($request->include_id) : null,
        ]);

        $validatedRequest = $request->validate([
            'with_trashed' => ['required', 'boolean'],
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'branch_id' => ['nullable', 'integer', new IsValidBranch($request->company_id, false)],
            'search' => ['nullable', 'string'],

            'prepaid_expense_id' => ['nullable', 'integer', new ExistsForCompany('prepaid_expenses', $request->company_id)],
            'include_id' => ['nullable', 'integer', new ExistsForCompany('prepaid_expense_payments', $request->company_id)],

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
            $result = $this->prepaidExpensePaymentActions->readAny(
                withTrashed: $validatedRequest['with_trashed'],
                companyId: $validatedRequest['company_id'],
                branchId: $validatedRequest['branch_id'] ?? null,
                search: $validatedRequest['search'] ?? null,

                prepaidExpenseId: $validatedRequest['prepaid_expense_id'] ?? null,
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

        return is_null($result) ? response()->error($errorMsg) : PrepaidExpensePaymentResource::collection($result);
    }

    public function read(PrepaidExpensePayment $prepaidExpensePayment)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('view', $prepaidExpensePayment);

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->prepaidExpensePaymentActions->read($prepaidExpensePayment);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : new PrepaidExpensePaymentResource($result);
    }

    public function store(PrepaidExpensePaymentStoreRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            $isUniqueCode = $this->prepaidExpensePaymentActions->isUniqueCode(
                $validatedRequest['company_id'],
                $validatedRequest['code'],
                null,
            );
            if (! $isUniqueCode) return response()->error(['code' => [trans('rules.unique_code')]], 422);

            DB::beginTransaction();
            $dto = new PrepaidExpensePaymentCreateDTO(
                companyId: $validatedRequest['company_id'],
                branchId: $validatedRequest['branch_id'],
                code: $validatedRequest['code'],
                date: $validatedRequest['date'],
                prepaidExpenseId: $validatedRequest['prepaid_expense_id'],
                cashAccountId: $validatedRequest['cash_account_id'],
                amount: $validatedRequest['amount'],
                remarks: $validatedRequest['remarks'],
            );
            $result = $this->prepaidExpensePaymentActions->create($dto);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function update(PrepaidExpensePayment $prepaidExpensePayment, PrepaidExpensePaymentUpdateRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            $isUniqueCode = $this->prepaidExpensePaymentActions->isUniqueCode(
                $prepaidExpensePayment->company_id,
                $validatedRequest['code'],
                $prepaidExpensePayment->id,
            );
            if (! $isUniqueCode) return response()->error(['code' => [trans('rules.unique_code')]], 422);

            DB::beginTransaction();
            $result = $this->prepaidExpensePaymentActions->update($prepaidExpensePayment, new PrepaidExpensePaymentUpdateDTO(
                code: $validatedRequest['code'],
                date: $validatedRequest['date'],
                cashAccountId: $validatedRequest['cash_account_id'],
                amount: $validatedRequest['amount'],
                remarks: $validatedRequest['remarks'],
            ));
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(PrepaidExpensePayment $prepaidExpensePayment)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('delete', $prepaidExpensePayment);

        $result = false;
        $errorMsg = '';

        try {
            DB::beginTransaction();
            $result = $this->prepaidExpensePaymentActions->delete($prepaidExpensePayment);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
