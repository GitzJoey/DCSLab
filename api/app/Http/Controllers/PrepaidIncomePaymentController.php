<?php

namespace App\Http\Controllers;

use App\Actions\PrepaidIncomePayment\PrepaidIncomePaymentActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\ExecuteGetDTO;
use App\DTOs\ExecutePaginationDTO;
use App\DTOs\PrepaidIncomePaymentCreateDTO;
use App\DTOs\PrepaidIncomePaymentUpdateDTO;
use App\Helpers\HashidsHelper;
use App\Http\Requests\PrepaidIncomePayment\PrepaidIncomePaymentStoreRequest;
use App\Http\Requests\PrepaidIncomePayment\PrepaidIncomePaymentUpdateRequest;
use App\Http\Resources\PrepaidIncomePaymentResource;
use App\Models\PrepaidIncomePayment;
use App\Rules\ExistsForCompany;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCompany;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PrepaidIncomePaymentController extends BaseController
{
    public function __construct(
        private readonly PrepaidIncomePaymentActions $prepaidIncomePaymentActions,
    ) {
        parent::__construct();
    }

    public function readAny(Request $request)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('viewAny', PrepaidIncomePayment::class);

        $request->merge([
            'company_id' => $request->filled('company_id') ? HashidsHelper::decodeId($request->company_id) : null,
            'branch_id' => $request->filled('branch_id') ? HashidsHelper::decodeId($request->branch_id) : null,
            'prepaid_income_id' => $request->filled('prepaid_income_id') ? HashidsHelper::decodeId($request->prepaid_income_id) : null,
            'include_id' => $request->filled('include_id') ? HashidsHelper::decodeId($request->include_id) : null,
        ]);

        $validatedRequest = $request->validate([
            'with_trashed' => ['required', 'boolean'],
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'branch_id' => ['nullable', 'integer', new IsValidBranch($request->company_id, false)],
            'search' => ['nullable', 'string'],

            'prepaid_income_id' => ['nullable', 'integer', new ExistsForCompany('prepaid_incomes', $request->company_id)],
            'include_id' => ['nullable', 'integer', new ExistsForCompany('prepaid_income_payments', $request->company_id)],

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
            $result = $this->prepaidIncomePaymentActions->readAny(
                withTrashed: $validatedRequest['with_trashed'],
                companyId: $validatedRequest['company_id'],
                branchId: $validatedRequest['branch_id'] ?? null,
                search: $validatedRequest['search'] ?? null,

                prepaidIncomeId: $validatedRequest['prepaid_income_id'] ?? null,
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

        return is_null($result) ? response()->error($errorMsg) : PrepaidIncomePaymentResource::collection($result);
    }

    public function read(PrepaidIncomePayment $prepaidIncomePayment)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('view', $prepaidIncomePayment);

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->prepaidIncomePaymentActions->read($prepaidIncomePayment);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : new PrepaidIncomePaymentResource($result);
    }

    public function store(PrepaidIncomePaymentStoreRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            $isUniqueCode = $this->prepaidIncomePaymentActions->isUniqueCode(
                $validatedRequest['company_id'],
                $validatedRequest['code'],
                null,
            );
            if (! $isUniqueCode) return response()->error(['code' => [trans('rules.unique_code')]], 422);

            DB::beginTransaction();
            $dto = new PrepaidIncomePaymentCreateDTO(
                companyId: $validatedRequest['company_id'],
                branchId: $validatedRequest['branch_id'],
                code: $validatedRequest['code'],
                date: $validatedRequest['date'],
                prepaidIncomeId: $validatedRequest['prepaid_income_id'],
                cashAccountId: $validatedRequest['cash_account_id'],
                amount: $validatedRequest['amount'],
                remarks: $validatedRequest['remarks'],
            );
            $result = $this->prepaidIncomePaymentActions->create($dto);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function update(PrepaidIncomePayment $prepaidIncomePayment, PrepaidIncomePaymentUpdateRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            $isUniqueCode = $this->prepaidIncomePaymentActions->isUniqueCode(
                $prepaidIncomePayment->company_id,
                $validatedRequest['code'],
                $prepaidIncomePayment->id,
            );
            if (! $isUniqueCode) return response()->error(['code' => [trans('rules.unique_code')]], 422);

            DB::beginTransaction();
            $result = $this->prepaidIncomePaymentActions->update($prepaidIncomePayment, new PrepaidIncomePaymentUpdateDTO(
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

    public function delete(PrepaidIncomePayment $prepaidIncomePayment)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('delete', $prepaidIncomePayment);

        $result = false;
        $errorMsg = '';

        try {
            DB::beginTransaction();
            $result = $this->prepaidIncomePaymentActions->delete($prepaidIncomePayment);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
