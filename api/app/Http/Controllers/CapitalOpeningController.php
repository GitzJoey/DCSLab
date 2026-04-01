<?php

namespace App\Http\Controllers;

use App\Actions\CapitalOpening\CapitalOpeningActions;
use App\DTOs\CapitalOpeningCreateDTO;
use App\DTOs\CapitalOpeningUpdateDTO;
use App\DTOs\ExecuteDTO;
use App\DTOs\ExecuteGetDTO;
use App\DTOs\ExecutePaginationDTO;
use App\Helpers\HashidsHelper;
use App\Http\Requests\CapitalOpening\CapitalOpeningStoreRequest;
use App\Http\Requests\CapitalOpening\CapitalOpeningUpdateRequest;
use App\Http\Resources\CapitalOpeningResource;
use App\Models\CapitalOpening;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCashAccount;
use App\Rules\IsValidCompany;
use App\Rules\IsValidInvestor;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CapitalOpeningController extends BaseController
{
    public function __construct(
        private CapitalOpeningActions $capitalOpeningActions
    ) {
        parent::__construct();
    }

    public function readAny(Request $request)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('viewAny', CapitalOpening::class);

        $request->merge([
            'company_id' => $request->filled('company_id') ? HashidsHelper::decodeId($request->company_id) : null,
            'branch_id' => $request->filled('branch_id') ? HashidsHelper::decodeId($request->branch_id) : null,
            'investor_id' => $request->filled('investor_id') ? HashidsHelper::decodeId($request->investor_id) : null,
            'cash_account_id' => $request->filled('cash_account_id') ? HashidsHelper::decodeId($request->cash_account_id) : null,
        ]);

        $validatedRequest = $request->validate([
            'refresh' => ['required', 'boolean'],
            'with_trashed' => ['required', 'boolean'],

            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'branch_id' => ['nullable', 'integer', 'bail', new IsValidBranch($request->company_id, true)],
            'search' => ['nullable', 'string'],
            'investor_id' => ['nullable', 'integer', 'bail', new IsValidInvestor($request->company_id)],
            'cash_account_id' => ['nullable', 'integer', 'bail', new IsValidCashAccount($request->branch_id)],

            'paginate' => ['nullable', 'array', 'required_without:get', 'prohibits:get'],
            'paginate.page' => ['required_with:paginate', 'integer', 'min:1'],
            'paginate.per_page' => ['required_with:paginate', 'integer', 'min:1'],
            'get' => ['nullable', 'array', 'required_without:paginate', 'prohibits:paginate'],
            'get.limit' => ['required_with:get', 'integer', 'min:1'],
        ]);

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->capitalOpeningActions->readAny(
                withTrashed: $validatedRequest['with_trashed'],
                companyId: $validatedRequest['company_id'],
                branchId: $validatedRequest['branch_id'] ?? null,
                search: $validatedRequest['search'] ?? null,

                investorId: $validatedRequest['investor_id'] ?? null,
                cashAccountId: $validatedRequest['cash_account_id'] ?? null,

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

        return CapitalOpeningResource::collection($result);
    }

    public function read(CapitalOpening $capitalOpening)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('view', $capitalOpening);

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->capitalOpeningActions->read($capitalOpening);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        }

        return new CapitalOpeningResource($result);
    }

    public function store(CapitalOpeningStoreRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            if ($validatedRequest['code'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUnique = $this->capitalOpeningActions->isUniqueCode(
                    $validatedRequest['company_id'],
                    $validatedRequest['code'],
                    null
                );
                if (! $isUnique) return response()->error(['code' => [trans('rules.unique_code')]], 422);
            }

            $result = $this->capitalOpeningActions->create(
                data: new CapitalOpeningCreateDTO(
                    companyId: $validatedRequest['company_id'],
                    branchId: $validatedRequest['branch_id'],
                    code: $validatedRequest['code'],
                    date: $validatedRequest['date'],
                    investorId: $validatedRequest['investor_id'],
                    cashAccountId: $validatedRequest['cash_account_id'],
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

    public function update(CapitalOpening $capitalOpening, CapitalOpeningUpdateRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            if ($validatedRequest['code'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUnique = $this->capitalOpeningActions->isUniqueCode(
                    $validatedRequest['company_id'],
                    $validatedRequest['code'],
                    $capitalOpening->id
                );
                if (! $isUnique) return response()->error(['code' => [trans('rules.unique_code')]], 422);
            }

            $result = $this->capitalOpeningActions->update(
                capitalOpening: $capitalOpening,
                data: new CapitalOpeningUpdateDTO(
                    companyId: $validatedRequest['company_id'],
                    branchId: $validatedRequest['branch_id'],
                    code: $validatedRequest['code'],
                    date: $validatedRequest['date'],
                    investorId: $validatedRequest['investor_id'],
                    cashAccountId: $validatedRequest['cash_account_id'],
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

    public function delete(CapitalOpening $capitalOpening)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('delete', $capitalOpening);

        $result = false;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            $result = $this->capitalOpeningActions->delete($capitalOpening);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
