<?php

namespace App\Http\Controllers;

use App\Actions\ExpensePayment\ExpensePaymentActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\ExecuteGetDTO;
use App\DTOs\ExecutePaginationDTO;
use App\Helpers\HashidsHelper;
use App\Http\Requests\ExpensePayment\ExpensePaymentStoreRequest;
use App\Http\Requests\ExpensePayment\ExpensePaymentUpdateRequest;
use App\Http\Resources\ExpensePaymentResource;
use App\Models\ExpensePayment;
use App\Rules\ExistsForCompany;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCompany;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ExpensePaymentController extends BaseController
{
    public function __construct(
        private readonly ExpensePaymentActions $expensePaymentActions,
    ) {
        parent::__construct();
    }

    public function readAny(Request $request)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('viewAny', ExpensePayment::class);

        $request->merge([
            'company_id' => $request->filled('company_id') ? HashidsHelper::decodeId($request->company_id) : null,
            'branch_id' => $request->filled('branch_id') ? HashidsHelper::decodeId($request->branch_id) : null,
            'expense_id' => $request->filled('expense_id') ? HashidsHelper::decodeId($request->expense_id) : null,
            'include_id' => $request->filled('include_id') ? HashidsHelper::decodeId($request->include_id) : null,
        ]);

        $validatedRequest = $request->validate([
            'with_trashed' => ['required', 'boolean'],
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'branch_id' => ['nullable', 'integer', new IsValidBranch($request->company_id, false)],
            'search' => ['nullable', 'string'],

            'expense_id' => ['nullable', 'integer', new ExistsForCompany('expenses', $request->company_id)],
            'include_id' => ['nullable', 'integer', new ExistsForCompany('expense_payments', $request->company_id)],

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
            $result = $this->expensePaymentActions->readAny(
                withTrashed: $validatedRequest['with_trashed'],
                companyId: $validatedRequest['company_id'],
                branchId: $validatedRequest['branch_id'] ?? null,
                search: $validatedRequest['search'] ?? null,

                expenseId: $validatedRequest['expense_id'] ?? null,
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

        return is_null($result) ? response()->error($errorMsg) : ExpensePaymentResource::collection($result);
    }

    public function read(ExpensePayment $expense_payment)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('view', $expense_payment);

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->expensePaymentActions->read($expense_payment);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : new ExpensePaymentResource($result);
    }

    public function store(ExpensePaymentStoreRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            $isUniqueCode = $this->expensePaymentActions->isUniqueCode(
                $validatedRequest['company_id'],
                $validatedRequest['code'],
                null,
            );
            if (! $isUniqueCode) return response()->error(['code' => [trans('rules.unique_code')]], 422);

            DB::beginTransaction();
            $result = $this->expensePaymentActions->create($validatedRequest);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function update(ExpensePayment $expense_payment, ExpensePaymentUpdateRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            $isUniqueCode = $this->expensePaymentActions->isUniqueCode(
                $expense_payment->company_id,
                $validatedRequest['code'],
                $expense_payment->id,
            );
            if (! $isUniqueCode) return response()->error(['code' => [trans('rules.unique_code')]], 422);

            DB::beginTransaction();
            $result = $this->expensePaymentActions->update($expense_payment, $validatedRequest);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(ExpensePayment $expense_payment)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('delete', $expense_payment);

        $result = false;
        $errorMsg = '';

        try {
            DB::beginTransaction();
            $result = $this->expensePaymentActions->delete($expense_payment);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
