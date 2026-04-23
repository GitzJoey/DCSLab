<?php

namespace App\Http\Controllers;

use App\Actions\Expense\ExpenseActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\ExecuteGetDTO;
use App\DTOs\ExecutePaginationDTO;
use App\DTOs\ExpenseCreateDTO;
use App\DTOs\ExpenseUpdateDTO;
use App\Helpers\HashidsHelper;
use App\Http\Requests\Expense\ExpenseStoreRequest;
use App\Http\Requests\Expense\ExpenseUpdateRequest;
use App\Http\Resources\ExpenseResource;
use App\Models\Expense;
use App\Rules\ExistsForCompany;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCompany;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ExpenseController extends BaseController
{
    public function __construct(
        private readonly ExpenseActions $expenseActions,
    ) {
        parent::__construct();
    }

    public function readAny(Request $request)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('viewAny', Expense::class);

        $request->merge([
            'company_id' => $request->filled('company_id') ? HashidsHelper::decodeId($request->company_id) : null,
            'branch_id' => $request->filled('branch_id') ? HashidsHelper::decodeId($request->branch_id) : null,
            'expense_category_id' => $request->filled('expense_category_id') ? HashidsHelper::decodeId($request->expense_category_id) : null,
            'include_id' => $request->filled('include_id') ? HashidsHelper::decodeId($request->include_id) : null,
        ]);

        $validatedRequest = $request->validate([
            'with_trashed' => ['required', 'boolean'],
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'branch_id' => ['nullable', 'integer', new IsValidBranch($request->company_id, false)],
            'search' => ['nullable', 'string'],

            'expense_category_id' => ['nullable', 'integer', new ExistsForCompany('expense_categories', $request->company_id)],
            'is_amount_payable_paid_off' => ['nullable', 'boolean'],
            'include_id' => ['nullable', 'integer', new ExistsForCompany('expenses', $request->company_id)],

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
            $result = $this->expenseActions->readAny(
                withTrashed: $validatedRequest['with_trashed'],
                companyId: $validatedRequest['company_id'],
                branchId: $validatedRequest['branch_id'] ?? null,
                search: $validatedRequest['search'] ?? null,

                categoryId: $validatedRequest['expense_category_id'] ?? null,
                isAmountPayablePaidOff: $validatedRequest['is_amount_payable_paid_off'] ?? null,
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

        return is_null($result) ? response()->error($errorMsg) : ExpenseResource::collection($result);
    }

    public function read(Expense $expense)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('view', $expense);

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->expenseActions->read($expense);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : new ExpenseResource($result);
    }

    public function store(ExpenseStoreRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            $isUniqueCode = $this->expenseActions->isUniqueCode(
                $validatedRequest['company_id'],
                $validatedRequest['code'],
                null,
            );
            if (! $isUniqueCode) return response()->error(['code' => [trans('rules.unique_code')]], 422);

            DB::beginTransaction();
            $result = $this->expenseActions->create(
                data: new ExpenseCreateDTO(
                    companyId: $validatedRequest['company_id'],
                    branchId: $validatedRequest['branch_id'],
                    code: $validatedRequest['code'],
                    date: $validatedRequest['date'],
                    expenseCategoryId: $validatedRequest['expense_category_id'],
                    paidImmediatelyCashAccountId: $validatedRequest['paid_immediately_cash_account_id'],
                    amountPaidImmediately: (float) $validatedRequest['amount_paid_immediately'],
                    amountPayable: (float) $validatedRequest['amount_payable'],
                    dueDays: $validatedRequest['due_days'],
                    remarks: $validatedRequest['remarks'],
                    images: $validatedRequest['image_hashes'],
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

    public function update(Expense $expense, ExpenseUpdateRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            $isUniqueCode = $this->expenseActions->isUniqueCode(
                $expense->company_id,
                $validatedRequest['code'],
                $expense->id,
            );
            if (! $isUniqueCode) return response()->error(['code' => [trans('rules.unique_code')]], 422);

            DB::beginTransaction();
            $result = $this->expenseActions->update(
                expense: $expense,
                data: new ExpenseUpdateDTO(
                    code: $validatedRequest['code'],
                    date: $validatedRequest['date'],
                    expenseCategoryId: $validatedRequest['expense_category_id'],
                    paidImmediatelyCashAccountId: $validatedRequest['paid_immediately_cash_account_id'],
                    amountPaidImmediately: (float) $validatedRequest['amount_paid_immediately'],
                    amountPayable: (float) $validatedRequest['amount_payable'],
                    dueDays: $validatedRequest['due_days'],
                    remarks: $validatedRequest['remarks'],
                    deleteImageIds: $validatedRequest['delete_image_ids'],
                    deletePaymentIds: $validatedRequest['delete_payment_ids'],
                    images: $validatedRequest['image_hashes'],
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

    public function delete(Expense $expense)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('delete', $expense);

        $result = false;
        $errorMsg = '';

        try {
            DB::beginTransaction();
            $result = $this->expenseActions->delete($expense);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
