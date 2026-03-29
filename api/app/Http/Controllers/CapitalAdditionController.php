<?php

namespace App\Http\Controllers;

use App\Actions\CapitalAddition\CapitalAdditionActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\ExecuteGetDTO;
use App\DTOs\ExecutePaginationDTO;
use App\Helpers\HashidsHelper;
use App\Http\Requests\CapitalAddition\CapitalAdditionStoreRequest;
use App\Http\Requests\CapitalAddition\CapitalAdditionUpdateRequest;
use App\Http\Resources\CapitalAdditionResource;
use App\Models\CapitalAddition;
use App\Rules\IsValidCompany;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CapitalAdditionController extends BaseController
{
    private $capitalAdditionActions;

    public function __construct(CapitalAdditionActions $capitalAdditionActions)
    {
        parent::__construct();

        $this->capitalAdditionActions = $capitalAdditionActions;
    }

    public function readAny(Request $request)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('viewAny', CapitalAddition::class);

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
            $result = $this->capitalAdditionActions->readAny(
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

        return CapitalAdditionResource::collection($result);
    }

    public function read(CapitalAddition $capitalAddition)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('view', $capitalAddition);

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->capitalAdditionActions->read($capitalAddition);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        }

        return new CapitalAdditionResource($result);
    }

    public function store(CapitalAdditionStoreRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            if ($validatedRequest['code'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUnique = $this->capitalAdditionActions->isUniqueCode(
                    $validatedRequest['company_id'],
                    $validatedRequest['code'],
                    null
                );
                if (! $isUnique) return response()->error(['code' => [trans('rules.unique_code')]], 422);
            }

            $result = $this->capitalAdditionActions->create($validatedRequest);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function update(CapitalAddition $capitalAddition, CapitalAdditionUpdateRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            if ($validatedRequest['code'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUnique = $this->capitalAdditionActions->isUniqueCode(
                    $validatedRequest['company_id'],
                    $validatedRequest['code'],
                    $capitalAddition->id
                );
                if (! $isUnique) return response()->error(['code' => [trans('rules.unique_code')]], 422);
            }

            $result = $this->capitalAdditionActions->update(
                $capitalAddition,
                $validatedRequest
            );

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(CapitalAddition $capitalAddition)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('delete', $capitalAddition);

        $result = false;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            $result = $this->capitalAdditionActions->delete($capitalAddition);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
