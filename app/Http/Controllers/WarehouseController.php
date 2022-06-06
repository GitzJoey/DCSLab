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

    public function read(WarehouseRequest $warehouseRequest)
    {
        $request = $warehouseRequest->validated();
        
        $search = $request['search'];
        $paginate = $request['paginate'];
        $page = array_key_exists('page', $request) ? abs($request['page']) : 1;
        $perPage = array_key_exists('perPage', $request) ? abs($request['perPage']) : 10;

        $companyId = $request['company_id'];

        $result = $this->warehouseService->read(
            companyId: $companyId,
            search: $search,
            paginate: $paginate,
            page: $page,
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
        
        $company_id = $request['company_id'];
        $branch_id = $request['branch_id'];

        $code = $request['code'];
        if ($code == config('const.DEFAULT.KEYWORDS.AUTO')) {
            do {
                $code = $this->warehouseService->generateUniqueCode($company_id);
            } while (!$this->warehouseService->isUniqueCode($code, $company_id));
        } else {
            if (!$this->warehouseService->isUniqueCode($code, $company_id)) {
                return response()->error([
                    'code' => [trans('rules.unique_code')]
                ], 422);
            }
        }

        $name = $request['name'];
        $address = $request['address'];
        $city = $request['city'];
        $contact = $request['contact'];
        $remarks = $request['remarks'];
        $status = $request['status'];

        $result = $this->warehouseService->create(
            $company_id,
            $branch_id,
            $code, 
            $name,
            $address,
            $city,
            $contact,
            $remarks,
            $status,
        );

        return is_null($result) ? response()->error() : response()->success();
    }

    public function update($id, WarehouseRequest $warehouseRequest)
    {
        $request = $warehouseRequest->validated();

        $company_id = $request['company_id'];
        $branch_id = $request['branch_id'];

        $code = $request['code'];
        if ($code == config('const.DEFAULT.KEYWORDS.AUTO')) {
            do {
                $code = $this->warehouseService->generateUniqueCode($company_id);
            } while (!$this->warehouseService->isUniqueCode($code, $company_id, $id));
        } else {
            if (!$this->warehouseService->isUniqueCode($code, $company_id, $id)) {
                return response()->error([
                    'code' => [trans('rules.unique_code')]
                ], 422);
            }
        }

        $name = $request['name'];
        $address = $request['address'];
        $city = $request['city'];
        $contact = $request['contact'];
        $remarks = $request['remarks'];
        $status = $request['status'];

        $warehouse = $this->warehouseService->update(
            $id,
            $company_id,
            $branch_id,
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

        return !$result ? response()->error() : response()->success();
    }
}
