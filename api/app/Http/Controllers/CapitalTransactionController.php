<?php

namespace App\Http\Controllers;

use App\Actions\CapitalTransaction\CapitalTransactionActions;
use App\DTOs\CapitalTransactionCreateDTO;
use App\DTOs\CapitalTransactionUpdateDTO;
use App\DTOs\ExecuteDTO;
use App\DTOs\ExecuteGetDTO;
use App\DTOs\ExecutePaginationDTO;
use App\Enums\CapitalTransactionTypeEnum;
use App\Helpers\HashidsHelper;
use App\Http\Requests\CapitalTransaction\CapitalTransactionStoreRequest;
use App\Http\Requests\CapitalTransaction\CapitalTransactionUpdateRequest;
use App\Http\Resources\CapitalTransactionResource;
use App\Models\CapitalTransaction;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCashAccount;
use App\Rules\IsValidCompany;
use App\Rules\IsValidInvestor;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Enum;

class CapitalTransactionController extends BaseController
{
    public function __construct(
        private CapitalTransactionActions $capitalTransactionActions
    ) {
        parent::__construct();
    }

    public function readAny(Request $request)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('viewAny', CapitalTransaction::class);

        $request->merge([
            'company_id' => $request->filled('company_id') ? HashidsHelper::decodeId($request->company_id) : null,
            'branch_id' => $request->filled('branch_id') ? HashidsHelper::decodeId($request->branch_id) : null,
            'investor_id' => $request->filled('investor_id') ? HashidsHelper::decodeId($request->investor_id) : null,
            'cash_account_id' => $request->filled('cash_account_id') ? HashidsHelper::decodeId($request->cash_account_id) : null,
            'type' => CapitalTransactionTypeEnum::isValid($request->type) ? CapitalTransactionTypeEnum::resolveToEnum($request->type)->value : null,
        ]);

        $validatedRequest = $request->validate([
            'refresh' => ['required', 'boolean'],
            'with_trashed' => ['required', 'boolean'],

            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'branch_id' => ['nullable', 'integer', 'bail', new IsValidBranch($request->company_id, true)],
            'search' => ['nullable', 'string'],
            'investor_id' => ['nullable', 'integer', 'bail', new IsValidInvestor($request->company_id)],
            'cash_account_id' => ['nullable', 'integer', 'bail', new IsValidCashAccount($request->branch_id)],
            'type' => ['nullable', new Enum(CapitalTransactionTypeEnum::class)],

            'paginate' => ['nullable', 'array', 'required_without:get', 'prohibits:get'],
            'paginate.page' => ['required_with:paginate', 'integer', 'min:1'],
            'paginate.per_page' => ['required_with:paginate', 'integer', 'min:1'],
            'get' => ['nullable', 'array', 'required_without:paginate', 'prohibits:paginate'],
            'get.limit' => ['required_with:get', 'integer', 'min:1'],
        ]);

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->capitalTransactionActions->readAny(
                withTrashed: $validatedRequest['with_trashed'],
                companyId: $validatedRequest['company_id'],
                branchId: $validatedRequest['branch_id'] ?? null,
                search: $validatedRequest['search'] ?? null,

                investorId: $validatedRequest['investor_id'] ?? null,
                cashAccountId: $validatedRequest['cash_account_id'] ?? null,
                type: $validatedRequest['type'] ?? null,

                execute: new ExecuteDTO(
                    useCache: ! $validatedRequest['refresh'],
                    pagination: (function () use ($validatedRequest) {
                        $pagination = null;
                        if (isset($validatedRequest['paginate'])) {
                            $pagination = new ExecutePaginationDTO(
                                page: $validatedRequest['paginate']['page'],
                                perPage: $validatedRequest['paginate']['per_page'],
                            );
                        }

                        return $pagination;
                    })(),
                    get: (function () use ($validatedRequest) {
                        $get = null;
                        if (isset($validatedRequest['get'])) {
                            $get = new ExecuteGetDTO(
                                limit: $validatedRequest['get']['limit'],
                            );
                        }

                        return $get;
                    })(),
                )
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        }

        return CapitalTransactionResource::collection($result);
    }

    public function getTypes()
    {
        return $this->capitalTransactionActions->getTypes();
    }

    public function read(CapitalTransaction $capitalTransaction)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('view', $capitalTransaction);

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->capitalTransactionActions->read($capitalTransaction);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        }

        return new CapitalTransactionResource($result);
    }

    public function store(CapitalTransactionStoreRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            if ($validatedRequest['code'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUnique = $this->capitalTransactionActions->isUniqueCode(
                    $validatedRequest['company_id'],
                    $validatedRequest['code'],
                    null
                );
                if (! $isUnique) return response()->error(['code' => [trans('rules.unique_code')]], 422);
            }

            DB::beginTransaction();

            $result = $this->capitalTransactionActions->create(
                data: new CapitalTransactionCreateDTO(
                    companyId: $validatedRequest['company_id'],
                    branchId: $validatedRequest['branch_id'],
                    code: $validatedRequest['code'],
                    date: $validatedRequest['date'],
                    investorId: $validatedRequest['investor_id'],
                    cashAccountId: $validatedRequest['cash_account_id'],
                    type: $validatedRequest['type'],
                    amount: $validatedRequest['amount'],
                    remarks: $validatedRequest['remarks'],
                )
            );

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function update(CapitalTransaction $capitalTransaction, CapitalTransactionUpdateRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            if ($validatedRequest['code'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUnique = $this->capitalTransactionActions->isUniqueCode(
                    $validatedRequest['company_id'],
                    $validatedRequest['code'],
                    $capitalTransaction->id
                );
                if (! $isUnique) return response()->error(['code' => [trans('rules.unique_code')]], 422);
            }

            DB::beginTransaction();

            $result = $this->capitalTransactionActions->update(
                capitalTransaction: $capitalTransaction,
                data: new CapitalTransactionUpdateDTO(
                    companyId: $validatedRequest['company_id'],
                    branchId: $validatedRequest['branch_id'],
                    code: $validatedRequest['code'],
                    date: $validatedRequest['date'],
                    investorId: $validatedRequest['investor_id'],
                    cashAccountId: $validatedRequest['cash_account_id'],
                    type: $validatedRequest['type'],
                    amount: $validatedRequest['amount'],
                    remarks: $validatedRequest['remarks'],
                )
            );

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(CapitalTransaction $capitalTransaction)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('delete', $capitalTransaction);

        $result = false;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            $result = $this->capitalTransactionActions->delete($capitalTransaction);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
