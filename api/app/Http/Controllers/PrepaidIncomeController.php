<?php

namespace App\Http\Controllers;

use App\Actions\PrepaidIncome\PrepaidIncomeActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\ExecuteGetDTO;
use App\DTOs\ExecutePaginationDTO;
use App\DTOs\PrepaidIncomeCreateDTO;
use App\DTOs\PrepaidIncomeUpdateDTO;
use App\Helpers\HashidsHelper;
use App\Http\Requests\PrepaidIncome\PrepaidIncomeStoreRequest;
use App\Http\Requests\PrepaidIncome\PrepaidIncomeUpdateRequest;
use App\Http\Resources\PrepaidIncomeResource;
use App\Models\PrepaidIncome;
use App\Rules\ExistsForCompany;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCompany;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PrepaidIncomeController extends BaseController
{
    public function __construct(
        private readonly PrepaidIncomeActions $prepaidIncomeActions,
    ) {
        parent::__construct();
    }

    public function readAny(Request $request)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('viewAny', PrepaidIncome::class);

        $request->merge([
            'company_id' => $request->filled('company_id') ? HashidsHelper::decodeId($request->company_id) : null,
            'branch_id' => $request->filled('branch_id') ? HashidsHelper::decodeId($request->branch_id) : null,
            'income_category_id' => $request->filled('income_category_id') ? HashidsHelper::decodeId($request->income_category_id) : null,
            'include_id' => $request->filled('include_id') ? HashidsHelper::decodeId($request->include_id) : null,
        ]);

        $validatedRequest = $request->validate([
            'with_trashed' => ['required', 'boolean'],
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'branch_id' => ['nullable', 'integer', new IsValidBranch($request->company_id, false)],
            'search' => ['nullable', 'string'],

            'income_category_id' => ['nullable', 'integer', new ExistsForCompany('income_categories', $request->company_id)],
            'is_amount_receivable_paid_off' => ['nullable', 'boolean'],
            'include_id' => ['nullable', 'integer', new ExistsForCompany('prepaid_incomes', $request->company_id)],

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
            $result = $this->prepaidIncomeActions->readAny(
                withTrashed: $validatedRequest['with_trashed'],
                companyId: $validatedRequest['company_id'],
                branchId: $validatedRequest['branch_id'] ?? null,
                search: $validatedRequest['search'] ?? null,

                categoryId: $validatedRequest['income_category_id'] ?? null,
                isAmountReceivablePaidOff: $validatedRequest['is_amount_receivable_paid_off'] ?? null,
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

        return is_null($result) ? response()->error($errorMsg) : PrepaidIncomeResource::collection($result);
    }

    public function read(PrepaidIncome $prepaidIncome)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('view', $prepaidIncome);

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->prepaidIncomeActions->read($prepaidIncome);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : new PrepaidIncomeResource($result);
    }

    public function store(PrepaidIncomeStoreRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            $isUniqueCode = $this->prepaidIncomeActions->isUniqueCode(
                $validatedRequest['company_id'],
                $validatedRequest['code'],
                null,
            );
            if (! $isUniqueCode) return response()->error(['code' => [trans('rules.unique_code')]], 422);

            DB::beginTransaction();
            $dto = new PrepaidIncomeCreateDTO(
                companyId: $validatedRequest['company_id'],
                branchId: $validatedRequest['branch_id'],
                code: $validatedRequest['code'],
                date: $validatedRequest['date'],
                incomeCategoryId: $validatedRequest['income_category_id'],
                estimatedUsefulLife: $validatedRequest['estimated_useful_life'],
                paidImmediatelyCashAccountId: $validatedRequest['paid_immediately_cash_account_id'],
                amountPaidImmediately: (float) $validatedRequest['amount_paid_immediately'],
                amountReceivable: (float) $validatedRequest['amount_receivable'],
                dueDays: $validatedRequest['due_days'],
                remarks: $validatedRequest['remarks'],
                images: $validatedRequest['image_hashes'],
                payments: $validatedRequest['payments'],
            );
            $result = $this->prepaidIncomeActions->create(
                data: $dto,
            );
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function update(PrepaidIncome $prepaidIncome, PrepaidIncomeUpdateRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            $isUniqueCode = $this->prepaidIncomeActions->isUniqueCode(
                $prepaidIncome->company_id,
                $validatedRequest['code'],
                $prepaidIncome->id,
            );
            if (! $isUniqueCode) return response()->error(['code' => [trans('rules.unique_code')]], 422);

            DB::beginTransaction();
            $result = $this->prepaidIncomeActions->update(
                prepaidIncome: $prepaidIncome,
                data: new PrepaidIncomeUpdateDTO(
                    code: $validatedRequest['code'],
                    date: $validatedRequest['date'],
                    incomeCategoryId: $validatedRequest['income_category_id'],
                    estimatedUsefulLife: $validatedRequest['estimated_useful_life'],
                    paidImmediatelyCashAccountId: $validatedRequest['paid_immediately_cash_account_id'],
                    amountPaidImmediately: (float) $validatedRequest['amount_paid_immediately'],
                    amountReceivable: (float) $validatedRequest['amount_receivable'],
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

    public function delete(PrepaidIncome $prepaidIncome)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('delete', $prepaidIncome);

        $result = false;
        $errorMsg = '';

        try {
            DB::beginTransaction();
            $result = $this->prepaidIncomeActions->delete($prepaidIncome);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
