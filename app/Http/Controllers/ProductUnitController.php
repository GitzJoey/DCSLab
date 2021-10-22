<?php

namespace App\Http\Controllers;

use App\Rules\uniqueCode;
use App\Services\ActivityLogService;
use App\Services\ProductUnitService;
use Illuminate\Http\Request;
use Vinkla\Hashids\Facades\Hashids;

class ProductUnitController extends BaseController
{
    private $productUnitService;
    private $activityLogService;

    public function __construct(ProductUnitService $productUnitService, ActivityLogService $activityLogService)
    {
        parent::__construct();

        $this->middleware('auth');
        $this->productUnitService = $productUnitService;
        $this->activityLogService = $activityLogService;
    }

    public function index(Request $request)
    {
        $this->activityLogService->RoutingActivity($request->route()->getName(), $request->all());

        return view('product.productunits.index');
    }

    public function read()
    {
        return $this->productUnitService->read();
    }

    public function getAllActiveProductUnit()
    {
        return $this->productUnitService->getAllActiveProductUnit();
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => ['required', 'max:255', new uniqueCode('create', '', 'productunits')],
            'name' => 'required|max:255'
        ]);

        $result = $this->productUnitService->create(
            $request['code'],
            Hashids::decode($request['company_id'])[0], 
            Hashids::decode($request['product_id'])[0], 
            Hashids::decode($request['unit_id'])[0], 
            $request['is_base'],
            $request['conversion_value'],
            $request['remarks'],
        );

        return $result == 0 ? response()->error():response()->success();
    }

    public function update($id, Request $request)
    {
        $request->validate([
            'code' => new uniqueCode('update', $id, 'productunits'),
            'name' => 'required|max:255',
        ]);
        
        $result = $this->productUnitService->update(
            $id,
            $request['code'],
            Hashids::decode($request['company_id'])[0], 
            Hashids::decode($request['product_id'])[0], 
            Hashids::decode($request['unit_id'])[0], 
            $request['is_base'],
            $request['conversion_value'],
            $request['remarks'],
        );
        return $result == 0 ? response()->error():response()->success();
    }

    public function delete($id)
    {
        $result = $this->productUnitService->delete($id);

        return $result == 0 ? response()->error():response()->success();
    }
}