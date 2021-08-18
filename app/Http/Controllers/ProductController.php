<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogService;
use App\Services\ProductService;
use Illuminate\Http\Request;

use Vinkla\Hashids\Facades\Hashids;

class ProductController extends Controller
{
    private $productService;
    private $activityLogService;

    public function __construct(ProductService $productService, ActivityLogService $activityLogService)
    {
        $this->middleware('auth');
        $this->productService = $productService;
        $this->activityLogService = $activityLogService;

    }

    public function index(Request $request)
    {
        $this->activityLogService->RoutingActivity($request->route()->getName(), $request->all());

        return view('product.products.index');
    }

    public function read()
    {
        return $this->productService->read();
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|max:255',
            'group_id' => 'required',
            'brand_id' => 'required',
            'name' => 'required|max:255',
            'unit_id' => 'required',
            'price' => 'required|max:255',
            'tax_status' => 'required',
            'estimated_capital_price' => 'required|max:255',
            'is_use_serial' => 'required',
            'is_buy' => 'required',
            'is_production_material' => 'required',
            'is_production_result' => 'required',
            'is_sell' => 'required',
            'status' => 'required'
        ]);

        $group_id = Hashids::decode($request['group_id'])[0];
        $brand_id = Hashids::decode($request['brand_id'])[0];
        $unit_id = Hashids::decode($request['unit_id'])[0];

        $is_use_serial = $request['is_use_serial'];
        $is_use_serial == 'on' ? $is_use_serial = 1 : $is_use_serial = 0;

        $is_buy = $request['is_buy'];
        $is_buy == 'on' ? $is_buy = 1 : $is_buy = 0;
        
        $is_production_material = $request['is_production_material'];
        $is_production_material == 'on' ? $is_production_material = 1 : $is_production_material = 0;

        $is_production_result = $request['is_production_result'];
        $is_production_result == 'on' ? $is_production_result = 1 : $is_production_result = 0;

        $is_sell = $request['is_sell'];
        $is_sell == 'on' ? $is_sell = 1 : $is_sell = 0;
        
        $result = $this->productService->create(
            $request['code'], 
            $group_id, 
            $brand_id, 
            $request['name'], 
            $unit_id,
            $request['price'], 
            $request['tax'], 
            $request['information'], 
            $request['estimated_capital_price'], 
            $is_use_serial, 
            $is_buy, 
            $is_production_material, 
            $is_production_result, 
            $is_sell, 
            $request['status']
        );

        if ($result == 0) {
            return response()->json([
                'message' => ''
            ],500);
        } else {
            return response()->json([
                'message' => ''
            ],200);
        }
    }

    public function update($id, Request $request)
    {
        $inputtedRolePermissions = [];
        for ($i = 0; $i < count($request['permissions']); $i++) {
            array_push($inputtedRolePermissions, array(
                'id' => Hashids::decode($request['permissions'][$i])[0]
            ));
        }

        $is_use_serial = $request['is_use_serial'];
        $is_use_serial == 'on' ? $is_use_serial = 1 : $is_use_serial = 0;

        $is_buy = $request['is_buy'];
        $is_buy == 'on' ? $is_buy = 1 : $is_buy = 0;
        
        $is_production_material = $request['is_production_material'];
        $is_production_material == 'on' ? $is_production_material = 1 : $is_production_material = 0;

        $is_production_result = $request['is_production_result'];
        $is_production_result == 'on' ? $is_production_result = 1 : $is_production_result = 0;

        $is_sell = $request['is_sell'];
        $is_sell == 'on' ? $is_sell = 1 : $is_sell = 0;

        $result = $this->productService->update(
            $id,
            $request['code'],
            $request['group_id'],
            $request['brand_id'],
            $request['name'],
            $request['unit_id'],
            $request['price'],
            $request['tax_status'],
            $request['information'],
            $request['estimated_capital_price'],
            $is_use_serial,
            $is_buy,
            $is_production_material,
            $is_production_result,
            $is_sell,
            $request['status'],
            $inputtedRolePermissions
        );

        return response()->json();
    }

    public function delete($id)
    {
        $result = $this->productService->delete($id);

        if ($result == false) {
            return response()->json([
                'message' => ''
            ],500);
        } else {
            return response()->json([
                'message' => ''
            ],200);
        }
    }
}
