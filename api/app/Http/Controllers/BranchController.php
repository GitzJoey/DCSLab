<?php

namespace App\Http\Controllers;

use App\Actions\Branch\BranchActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\ExecuteGetDTO;
use App\DTOs\ExecutePaginationDTO;
use App\Enums\RecordStatusEnum;
use App\Helpers\HashidsHelper;
use App\Http\Requests\Branch\BranchStoreRequest;
use App\Http\Requests\Branch\BranchUpdateRequest;
use App\Http\Resources\BranchResource;
use App\Models\Branch;
use App\Rules\IsValidCompany;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Enum;

class BranchController extends BaseController
{
    private $branchActions;

    public function __construct(BranchActions $branchActions)
    {
        parent::__construct();

        $this->branchActions = $branchActions;
    }

    public function store(BranchStoreRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            if ($validatedRequest['code'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUnique = $this->branchActions->isUniqueCode(
                    $validatedRequest['company_id'], $validatedRequest['code'], null,
                );
                if (! $isUnique) return response()->error(['code' => [trans('rules.unique_code')]], 422);
            }

            $isUniqueName = $this->branchActions->isUniqueName(
                $validatedRequest['company_id'], $validatedRequest['name'], null,
            );
            if (! $isUniqueName) return response()->error(['name' => [trans('rules.unique_name')]], 422);

            $validatedRequest['address'] = $validatedRequest['address'] ?? null;
            $validatedRequest['city'] = $validatedRequest['city'] ?? null;
            $validatedRequest['contact'] = $validatedRequest['contact'] ?? null;

            if ($validatedRequest['is_main']) {
                $this->branchActions->resetMainByCompany($validatedRequest['company_id']);
            }

            $validatedRequest['remarks'] = $validatedRequest['remarks'] ?? null;

            $result = $this->branchActions->create($validatedRequest);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function readAny(Request $request)
    {
        if (! Auth::check())  return response()->error(trans('rules.auth.unauthorized'), 401);
        $this->authorize('viewAny', Branch::class);

        if ($request->filled('company_id')) $request->merge(['company_id' => HashidsHelper::decodeId($request->company_id)]);

        if ($request->filled('include_id')) $request->merge(['include_id' => HashidsHelper::decodeId($request->include_id)]);

        if ($request->filled('status')) {
            $request->merge(['status' => RecordStatusEnum::isValid($request->status) ? RecordStatusEnum::resolveToEnum($request->status)->value : -1]);
        }

        $validatedRequest = $request->validate([
            'with_trashed' => ['required', 'boolean'],

            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'search' => ['nullable', 'string'],
            'is_main' => ['nullable', 'boolean'],
            'status' => ['nullable', new Enum(RecordStatusEnum::class)],
            'include_id' => ['nullable', 'integer', 'exists:branches,id'],

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
            $result = $this->branchActions->readAny(
                withTrashed: $validatedRequest['with_trashed'],

                companyId: $validatedRequest['company_id'],
                search: $validatedRequest['search'] ?? null,
                isMain: $validatedRequest['is_main'] ?? null,
                status: $validatedRequest['status'] ?? null,
                includeId: $validatedRequest['include_id'] ?? null,

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
                    })()
                )
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            return BranchResource::collection($result);
        }
    }

    public function read(Branch $branch)
    {
        if (! Auth::check())  return response()->error(trans('rules.auth.unauthorized'), 401);
        $this->authorize('view', $branch);

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->branchActions->read($branch);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            $response = new BranchResource($result);

            return $response;
        }
    }

    public function update(Branch $branch, BranchUpdateRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            if ($validatedRequest['code'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUnique = $this->branchActions->isUniqueCode(
                    $validatedRequest['company_id'], $validatedRequest['code'], $branch->id,
                );
                if (! $isUnique) return response()->error(['code' => [trans('rules.unique_code')]], 422);
            }

            $isUniqueName = $this->branchActions->isUniqueName(
                $validatedRequest['company_id'], $validatedRequest['name'], $branch->id,
            );
            if (! $isUniqueName) return response()->error(['name' => [trans('rules.unique_name')]], 422);

            $validatedRequest['address'] = $validatedRequest['address'] ?? null;
            $validatedRequest['city'] = $validatedRequest['city'] ?? null;
            $validatedRequest['contact'] = $validatedRequest['contact'] ?? null;

            if ($validatedRequest['is_main']) {
                $this->branchActions->resetMainByCompany($validatedRequest['company_id']);
                $branch->refresh();
            }

            $validatedRequest['remarks'] = $validatedRequest['remarks'] ?? null;

            $result = $this->branchActions->update(
                branch: $branch,
                data: $validatedRequest
            );

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(Branch $branch)
    {
        if (! Auth::check())  return response()->error(trans('rules.auth.unauthorized'), 401);
        $this->authorize('delete', $branch);

        $result = false;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            if ($this->branchActions->isMain($branch)) {
                return response()->error(trans('rules.branch.delete_main_branch'), 422);
            }

            $result = $this->branchActions->delete($branch);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
