<?php

namespace App\Http\Controllers;

use App\Http\Requests\WarehouseRequest;
use App\Http\Resources\WarehouseResource;
use App\Models\Warehouse;
use App\Services\WarehouseService;
use Exception;

class WarehouseController extends BaseController
{
    private $warehouseService;

    public function __construct(WarehouseService $warehouseService)
    {
        parent::__construct();

        $this->middleware('auth');
        $this->warehouseService = $warehouseService;
    }

    public function store(WarehouseRequest $warehouseRequest)
    {
        $request = $warehouseRequest->validated();

        $company_id = $request['company_id'];

        $code = $request['code'];
        if ($code == config('dcslab.KEYWORDS.AUTO')) {
            do {
                $code = $this->warehouseService->generateUniqueCode();
            } while (!$this->warehouseService->isUniqueCode($code, $company_id));
        } else {
            if (!$this->warehouseService->isUniqueCode($code, $company_id)) {
                return response()->error([
                    'code' => [trans('rules.unique_code')],
                ], 422);
            }
        }

        $warehouseArr = [
            'company_id' => $company_id,
            'code' => $code,
            'company_id' => $request['company_id'],
            'branch_id' => $request['branch_id'],
            'name' => $request['name'],
            'address' => $request['address'],
            'city' => $request['city'],
            'contact' => $request['contact'],
            'remarks' => $request['remarks'],
            'status' => $request['status'],
        ];

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->warehouseService->create($warehouseArr);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }
    
    public function list(WarehouseRequest $warehouseRequest)
    {
        $request = $warehouseRequest->validated();

        $search = $request['search'];
        $paginate = $request['paginate'];
        $page = array_key_exists('page', $request) ? abs($request['page']) : 1;
        $perPage = array_key_exists('perPage', $request) ? abs($request['perPage']) : 10;

        $companyId = $request['company_id'];

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->warehouseService->list(
                companyId: $companyId,
                search: $search,
                paginate: $paginate,
                page: $page,
                perPage: $perPage
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            $response = WarehouseResource::collection($result);

            return $response;
        }
    }

    public function read(Warehouse $warehouse, WarehouseRequest $warehouseRequest)
    {
        $request = $warehouseRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->warehouseService->read($warehouse);
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

    public function update(Warehouse $warehouse, WarehouseRequest $warehouseRequest)
    {
        $request = $warehouseRequest->validated();

        $company_id = $request['company_id'];

        $code = $request['code'];
        if ($code == config('dcslab.KEYWORDS.AUTO')) {
            do {
                $code = $this->warehouseService->generateUniqueCode();
            } while (!$this->warehouseService->isUniqueCode($code, $company_id, $warehouse->id));
        } else {
            if (!$this->warehouseService->isUniqueCode($code, $company_id, $warehouse->id)) {
                return response()->error([
                    'code' => [trans('rules.unique_code')],
                ], 422);
            }
        }

        $warehouseArr = [
            'code' => $code,
            'name' => $request['name'],
            'address' => $request['address'],
            'city' => $request['city'],
            'contact' => $request['contact'],
            'remarks' => $request['remarks'],
            'status' => $request['status'],
        ];

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->warehouseService->update(
                $warehouse,
                $warehouseArr
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(Warehouse $warehouse)
    {
        $result = false;
        $errorMsg = '';

        try {
            $result = $this->warehouseService->delete($warehouse);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return !$result ? response()->error($errorMsg) : response()->success();
    }
}
