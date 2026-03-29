<?php

namespace App\Http\Controllers;

use App\Actions\CapitalWithdrawal\CapitalWithdrawalActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\ExecuteGetDTO;
use App\DTOs\ExecutePaginationDTO;
use App\Helpers\HashidsHelper;
use App\Http\Requests\CapitalWithdrawal\CapitalWithdrawalStoreRequest;
use App\Http\Requests\CapitalWithdrawal\CapitalWithdrawalUpdateRequest;
use App\Http\Resources\CapitalWithdrawalResource;
use App\Models\CapitalWithdrawal;
use App\Rules\IsValidCompany;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CapitalWithdrawalController extends BaseController
{
    private $capitalWithdrawalActions;

    public function __construct(CapitalWithdrawalActions $capitalWithdrawalActions)
    {
        parent::__construct();

        $this->capitalWithdrawalActions = $capitalWithdrawalActions;
    }

    public function readAny(Request $request)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('viewAny', CapitalWithdrawal::class);

        $request->merge([
            'company_id' => $request->filled('company_id') ? HashidsHelper::decodeId($request->company_id) : null,
        ]);

        $validatedRequest = $request->validate([
            'with_trashed' => ['required', 'boolean'],
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'search' => ['nullable', 'string'],
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
            $result = $this->capitalWithdrawalActions->readAny(
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

        return CapitalWithdrawalResource::collection($result);
    }

    public function read(CapitalWithdrawal $capitalWithdrawal)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('view', $capitalWithdrawal);

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->capitalWithdrawalActions->read($capitalWithdrawal);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        }

        return new CapitalWithdrawalResource($result);
    }

    public function store(CapitalWithdrawalStoreRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            if ($validatedRequest['code'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUnique = $this->capitalWithdrawalActions->isUniqueCode(
                    $validatedRequest['company_id'],
                    $validatedRequest['code'],
                    null
                );
                if (! $isUnique) return response()->error(['code' => [trans('rules.unique_code')]], 422);
            }

            $result = $this->capitalWithdrawalActions->create($validatedRequest);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function update(CapitalWithdrawal $capitalWithdrawal, CapitalWithdrawalUpdateRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            if ($validatedRequest['code'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUnique = $this->capitalWithdrawalActions->isUniqueCode(
                    $validatedRequest['company_id'],
                    $validatedRequest['code'],
                    $capitalWithdrawal->id
                );
                if (! $isUnique) return response()->error(['code' => [trans('rules.unique_code')]], 422);
            }

            $result = $this->capitalWithdrawalActions->update(
                capitalWithdrawal: $capitalWithdrawal,
                data: $validatedRequest
            );

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(CapitalWithdrawal $capitalWithdrawal)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('delete', $capitalWithdrawal);

        $result = false;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            $result = $this->capitalWithdrawalActions->delete($capitalWithdrawal);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
