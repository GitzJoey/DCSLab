<?php

namespace App\Http\Controllers;

use App\Actions\Warehouse\WarehouseActions;
use App\Http\Requests\WarehouseRequest;
use App\Http\Resources\WarehouseResource;
use App\Models\Warehouse;
use Exception;

class WarehouseController extends Controller
{
    private $warehouseActions;

    public function __construct(WarehouseActions $warehouseActions)
    {
        parent::__construct();

        $this->middleware('auth');
        $this->warehouseActions = $warehouseActions;
    }

    public function store(WarehouseRequest $warehouseRequest)
    {
        $request = $warehouseRequest->validated();

        $company_id = $request['company_id'];

        $code = $request['code'];
        if ($code == config('dcslab.KEYWORDS.AUTO')) {
            do {
                $code = $this->warehouseActions->generateUniqueCode();
            } while (! $this->warehouseActions->isUniqueCode($code, $company_id));
        } else {
            if (! $this->warehouseActions->isUniqueCode($code, $company_id)) {
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
            $result = $this->warehouseActions->create($warehouseArr);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function readAny(WarehouseRequest $warehouseRequest)
    {
        $request = $warehouseRequest->validated();

        $search = $request['search'];
        $paginate = $request['paginate'];
        $page = array_key_exists('page', $request) ? abs($request['page']) : 1;
        $perPage = array_key_exists('perPage', $request) ? abs($request['perPage']) : 10;
        $useCache = array_key_exists('refresh', $request) ? boolval($request['refresh']) : true;

        $companyId = $request['company_id'];

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->warehouseActions->readAny(
                companyId: $companyId,
                search: $search,
                paginate: $paginate,
                page: $page,
                perPage: $perPage,
                useCache: $useCache
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

    public function update(Warehouse $warehouse, WarehouseRequest $warehouseRequest)
    {
        $request = $warehouseRequest->validated();

        $company_id = $request['company_id'];

        $code = $request['code'];
        if ($code == config('dcslab.KEYWORDS.AUTO')) {
            do {
                $code = $this->warehouseActions->generateUniqueCode();
            } while (! $this->warehouseActions->isUniqueCode($code, $company_id, $warehouse->id));
        } else {
            if (! $this->warehouseActions->isUniqueCode($code, $company_id, $warehouse->id)) {
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
            $result = $this->warehouseActions->update(
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
            $result = $this->warehouseActions->delete($warehouse);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
