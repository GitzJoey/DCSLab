<?php

namespace App\Http\Controllers;

use App\Actions\CashAccount\CashAccountActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\ExecuteGetDTO;
use App\DTOs\ExecutePaginationDTO;
use App\Helpers\HashidsHelper;
use App\Http\Requests\CashAccount\CashAccountStoreRequest;
use App\Http\Requests\CashAccount\CashAccountUpdateRequest;
use App\Http\Resources\CashAccountResource;
use App\Models\CashAccount;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCompany;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CashAccountController extends BaseController
{
    private $cashAccountActions;

    public function __construct(CashAccountActions $cashAccountActions)
    {
        parent::__construct();

        $this->cashAccountActions = $cashAccountActions;
    }

    public function store(CashAccountStoreRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            if ($validatedRequest['code'] != config('dcslab.KEYWORDS.AUTO')) {
                $isUnique = $this->cashAccountActions->isUniqueCode(
                    $validatedRequest['company_id'], $validatedRequest['code'], null,
                );
                if (! $isUnique) return response()->error(['code' => [trans('rules.unique_code')]], 422);
            }

            $isUniqueName = $this->cashAccountActions->isUniqueName(
                $validatedRequest['company_id'], $validatedRequest['name'], null,
            );
            if (! $isUniqueName) return response()->error(['name' => [trans('rules.unique_name')]], 422);

            $result = $this->cashAccountActions->create($validatedRequest);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function readAny(Request $request)
    {
        if (! Auth::check()) return response()->error(trans('rules.auth.unauthorized'), 401);
        $this->authorize('viewAny', CashAccount::class);

        if ($request->filled('company_id')) {
            $request->merge(['company_id' => HashidsHelper::decodeId($request->company_id)]);
        }
        if ($request->filled('branch_id')) {
            $request->merge(['branch_id' => HashidsHelper::decodeId($request->branch_id)]);
        }
        if ($request->filled('include_id')) {
            $request->merge(['include_id' => HashidsHelper::decodeId($request->include_id)]);
        }

        $validatedRequest = $request->validate([
            'with_trashed' => ['required', 'boolean'],

            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'branch_id' => ['nullable', 'integer', 'bail', new IsValidBranch($request->company_id, true)],
            'search' => ['nullable', 'string'],
            'include_id' => ['nullable', 'integer', 'exists:cash_accounts,id'],

            'refresh' => ['required', 'boolean'],
            'paginate' => ['nullable', 'array', 'required_without:get', 'prohibits:get'],
            'paginate.page' => ['required_with:paginate', 'integer', 'min:1'],
            'paginate.per_page' => ['required_with:paginate', 'integer', 'min:10'],
            'get' => ['nullable', 'array', 'required_without:paginate', 'prohibits:paginate'],
            'get.limit' => ['required_with:get', 'integer', 'min:10'],
        ]);

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->cashAccountActions->readAny(
                withTrashed: $validatedRequest['with_trashed'],

                companyId: $validatedRequest['company_id'],
                branchId: $validatedRequest['branch_id'] ?? null,
                search: $validatedRequest['search'] ?? null,
                includeId: $validatedRequest['include_id'] ?? null,

                execute: new ExecuteDTO(
                    useCache: ! $validatedRequest['refresh'],
                    pagination: isset($validatedRequest['paginate']) ? new ExecutePaginationDTO(
                        page: $validatedRequest['paginate']['page'],
                        perPage: $validatedRequest['paginate']['per_page'],
                    ) : null,
                    get: isset($validatedRequest['get']) ? new ExecuteGetDTO(
                        limit: $validatedRequest['get']['limit'],
                    ) : null,
                )
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            return CashAccountResource::collection($result);
        }
    }

    public function read(CashAccount $cashAccount)
    {
        if (! Auth::check()) return response()->error(trans('rules.auth.unauthorized'), 401);
        $this->authorize('view', $cashAccount);

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->cashAccountActions->read($cashAccount);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            return new CashAccountResource($result);
        }
    }

    public function update(CashAccountUpdateRequest $request, CashAccount $cashAccount)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            if ($validatedRequest['code'] != config('dcslab.KEYWORDS.AUTO')) {
                $isUnique = $this->cashAccountActions->isUniqueCode(
                    $validatedRequest['company_id'], $validatedRequest['code'], $cashAccount->id,
                );
                if (! $isUnique) return response()->error(['code' => [trans('rules.unique_code')]], 422);
            }

            $isUniqueName = $this->cashAccountActions->isUniqueName(
                $validatedRequest['company_id'], $validatedRequest['name'], $cashAccount->id,
            );
            if (! $isUniqueName) return response()->error(['name' => [trans('rules.unique_name')]], 422);

            $result = $this->cashAccountActions->update($cashAccount, $validatedRequest);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(CashAccount $cashAccount)
    {
        if (! Auth::check()) return response()->error(trans('rules.auth.unauthorized'), 401);
        $this->authorize('delete', $cashAccount);

        $result = false;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            $result = $this->cashAccountActions->delete($cashAccount);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
