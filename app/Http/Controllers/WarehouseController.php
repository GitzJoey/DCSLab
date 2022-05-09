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
        $search = $request->has('search') && !is_null($request['search']) ? $request['search']:'';
        $search = !is_null($search) ? $search : '';

        $paginate = $request->has('paginate') ? $request['paginate']:true;
        $paginate = !is_null($paginate) ? $paginate : true;
        $paginate = is_numeric($paginate) ? abs($paginate) : true;

        $page = $request->has('page') ? $request['page']:1;
        $page = !is_null($page) ? $page : 1;
        $page = is_numeric($page) ? abs($page) : 1; 

        $perPage = $request->has('perPage') ? $request['perPage']:10;
        $perPage = !is_null($perPage) ? $perPage : 10;
        $perPage = is_numeric($perPage) ? abs($perPage) : 10; 

        $companyId = Hashids::decode($request['companyId'])[0];

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
        
        $company_id = Hashids::decode($request['company_id'])[0];
        $branch_id = Hashids::decode($request['branch_id'])[0];

        $code = $request['code'] == config('const.DEFAULT.KEYWORDS.AUTO') ? $code = $this->warehouseService->generateUniqueCode($company_id) : $request['code'];
        if (!$this->warehouseService->isUniqueCode($code, $company_id)) {
            return response()->error([
                'code' => trans('rules.unique_code')
            ]);
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

        $company_id = Hashids::decode($request['company_id'])[0];
        $branch_id = Hashids::decode($request['branch_id'])[0];

        $code = $request['code'] == config('const.DEFAULT.KEYWORDS.AUTO') ? $code = $this->warehouseService->generateUniqueCode($company_id) : $request['code'];
        if (!$this->warehouseService->isUniqueCode($code, $company_id, $id)) {
            return response()->error([
                'code' => trans('rules.unique_code')
            ]);
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
