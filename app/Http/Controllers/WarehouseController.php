<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\WarehouseService;
use Vinkla\Hashids\Facades\Hashids;
use App\Http\Requests\WarehouseRequest;
use App\Http\Resources\WarehouseResource;

class WarehouseController extends BaseController
{
    private $warehouseService;
    
    public function __construct(WarehouseService $warehouseService)
    {
        parent::__construct();

        $this->middleware('auth');
        $this->warehouseService = $warehouseService;
    }

    public function read(Request $request)
    {
        $companyId = Hashids::decode($request['companyId'])[0];
        $search = $request->has('search') && !is_null($request['search']) ? $request['search']:'';
        $paginate = $request->has('paginate') ? $request['paginate']:true;
        $perPage = $request->has('perPage') ? $request['perPage']:null;

        $result = $this->warehouseService->read(
            companyId: $companyId,
            search: $search,
            paginate: $paginate,
            perPage: $perPage
        );

        if (is_null($result)) {
            return response()->error();
        } else {
            $response = WarehouseResource::collection($result);

            return $response;
        }
    }

    public function store(WarehouseRequest $warehouseRequest)
    {   
        $request = $warehouseRequest->validated();
        
        $company_id = Hashids::decode($request['company_id'])[0];
        $code = $request['code'];
        $name = $request['name'];
        $address = $request['address'];
        $city = $request['city'];
        $contact = $request['contact'];
        $remarks = $request['remarks'];
        $status = $request['status'];

        $result = $this->warehouseService->create(
            $company_id,
            $code, 
            $name,
            $address,
            $city,
            $contact,
            $remarks,
            $status,
        );

        return is_null($result) ? response()->error():response()->success();
    }

    public function update($id, WarehouseRequest $warehouseRequest)
    {
        $request = $warehouseRequest->validated();

        $company_id = Hashids::decode($request['company_id'])[0];
        $code = $request['code'];
        $name = $request['name'];
        $address = $request['address'];
        $city = $request['city'];
        $contact = $request['contact'];
        $remarks = $request['remarks'];
        $status = $request['status'];

        $warehouse = $this->warehouseService->update(
            $id,
            $company_id,
            $code, 
            $name,
            $address,
            $city,
            $contact,
            $remarks,
            $status,
        );

        return is_null($warehouse) ? response()->error() : response()->success();
    }

    public function delete($id)
    {
        $result = $this->warehouseService->delete($id);

        return is_null($result) ? response()->error():response()->success();
    }
}
