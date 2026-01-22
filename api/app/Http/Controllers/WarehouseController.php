<?php

namespace App\Http\Controllers;

use App\Actions\Warehouse\WarehouseActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\ExecuteGetDTO;
use App\DTOs\ExecutePaginationDTO;
use App\Enums\RecordStatusEnum;
use App\Helpers\HashidsHelper;
use App\Http\Requests\Warehouse\WarehouseStoreRequest;
use App\Http\Requests\Warehouse\WarehouseUpdateRequest;
use App\Http\Resources\WarehouseResource;
use App\Models\Warehouse;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCompany;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Enum;

class WarehouseController extends BaseController
{
    private $warehouseActions;

    public function __construct(WarehouseActions $warehouseActions)
    {
        parent::__construct();

        $this->warehouseActions = $warehouseActions;
    }

    public function store(WarehouseStoreRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            if ($validatedRequest['code'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUnique = $this->warehouseActions->isUniqueCode(
                    $validatedRequest['company_id'], $validatedRequest['code'], null,
                );
                if (! $isUnique) return response()->error(['code' => [trans('rules.unique_code')]], 422);
            }

            $isUniqueName = $this->warehouseActions->isUniqueName(
                $validatedRequest['company_id'], $validatedRequest['name'], null,
            );
            if (! $isUniqueName) return response()->error(['name' => [trans('rules.unique_name')]], 422);

            $validatedRequest['address'] = $validatedRequest['address'] ?? null;
            $validatedRequest['city'] = $validatedRequest['city'] ?? null;
            $validatedRequest['contact'] = $validatedRequest['contact'] ?? null;
            $validatedRequest['remarks'] = $validatedRequest['remarks'] ?? null;

            $result = $this->warehouseActions->create($validatedRequest);

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
        $this->authorize('viewAny', Warehouse::class);

        if ($request->filled('company_id')) $request->merge(['company_id' => HashidsHelper::decodeId($request->company_id)]);

        if ($request->filled('branch_id')) $request->merge(['branch_id' => HashidsHelper::decodeId($request->branch_id)]);

        if ($request->filled('include_id')) $request->merge(['include_id' => HashidsHelper::decodeId($request->include_id)]);

        if ($request->filled('status')) {
            $request->merge(['status' => RecordStatusEnum::isValid($request->status) ? RecordStatusEnum::resolveToEnum($request->status)->value : -1]);
        }

        $validatedRequest = $request->validate([
            'with_trashed' => ['required', 'boolean'],

            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'branch_id' => ['nullable', 'integer', 'bail', new IsValidBranch($request->company_id, false)],
            'search' => ['nullable', 'string'],
            'status' => ['nullable', new Enum(RecordStatusEnum::class)],
            'include_id' => ['nullable', 'integer', 'exists:warehouses,id'],

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
            $result = $this->warehouseActions->readAny(
                withTrashed: $validatedRequest['with_trashed'],

                companyId: $validatedRequest['company_id'],
                search: $validatedRequest['search'] ?? null,
                branchId: $validatedRequest['branch_id'] ?? null,
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
            return WarehouseResource::collection($result);
        }
    }

    public function read(Warehouse $warehouse)
    {
        if (! Auth::check())  return response()->error(trans('rules.auth.unauthorized'), 401);
        $this->authorize('view', $warehouse);

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->warehouseActions->read($warehouse);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            $response = new WarehouseResource($result);

            return $response;
        }
    }

    public function update(Warehouse $warehouse, WarehouseUpdateRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            if ($validatedRequest['code'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUnique = $this->warehouseActions->isUniqueCode(
                    $validatedRequest['company_id'], $validatedRequest['code'], $warehouse->id,
                );
                if (! $isUnique) return response()->error(['code' => [trans('rules.unique_code')]], 422);
            }

            $isUniqueName = $this->warehouseActions->isUniqueName(
                $validatedRequest['company_id'], $validatedRequest['name'], $warehouse->id,
            );
            if (! $isUniqueName) return response()->error(['name' => [trans('rules.unique_name')]], 422);

            $validatedRequest['address'] = $validatedRequest['address'] ?? null;
            $validatedRequest['city'] = $validatedRequest['city'] ?? null;
            $validatedRequest['contact'] = $validatedRequest['contact'] ?? null;
            $validatedRequest['remarks'] = $validatedRequest['remarks'] ?? null;

            $result = $this->warehouseActions->update(
                warehouse: $warehouse,
                data: $validatedRequest
            );

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(Warehouse $warehouse)
    {
        if (! Auth::check())  return response()->error(trans('rules.auth.unauthorized'), 401);
        $this->authorize('delete', $warehouse);

        $result = false;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            $result = $this->warehouseActions->delete($warehouse);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
