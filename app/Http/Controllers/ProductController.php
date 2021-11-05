<?php

namespace App\Http\Controllers;

use App\Rules\uniqueCode;
use Illuminate\Http\Request;
use App\Services\ProductService;

use Vinkla\Hashids\Facades\Hashids;
use App\Services\ActivityLogService;

class ProductController extends BaseController
{
    private $productService;
    private $activityLogService;

    public function __construct(ProductService $productService, ActivityLogService $activityLogService)
    {
        parent::__construct();

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

    public function read_product()
    {
        return $this->productService->read_product();
    }

    public function read_service()
    {
        return $this->productService->read_service();
    }

    public function store(Request $request)
    {   
        $request->validate([
            'code' => ['required', 'max:255', new uniqueCode('create', '', 'products')],
            'name' => 'required|max:255',
            'status' => 'required',
        ]);

        $productservice = array (
            'code' => $request['code'],
            'group_id' => $request['group_id'],
            'name' => $request['name'],
            'unit_id' => $request['unit_id'],
            'tax_status' => $request['tax_status'],
            'remarks' => $request['remarks'],
            'point' => $request['point'],
            'product_type' => $request['product_type'],
            'status' => $request['status'],
        );

        $is_use_serial = $request['is_use_serial'];
        $is_use_serial == 'on' ? $is_use_serial = 1 : $is_use_serial = 0;
        
        // $product_unit = [];
        // array_push(
        //     $product_unit,         
        //     $product_unit_code, 
        //     $conv_value, 
        //     $unit_id, 
        //     $is_primary_unit, 
        //     $product_unit_remarks
        // );

        // $product_unit_code = [];
        // for($i = 0; $i < count($request['product_unit_code']); $i++) {
        //     array_push($product_unit_code, array (
        //         'id' => $request['product_unit_code'][$i]
        //     ));
        // }

        $product_unit_code = $request['product_unit_code'];
        $conv_value = $request['conv_value'];
        $unit_id = $request['unit_id'];
        $is_primary_unit = $request['is_primary_unit'];
        $product_unit_remarks = $request['product_unit_remarks'];

        $product_unit = [];
        array_push(
            $product_unit,         
            $product_unit_code, 
            $conv_value, 
            $unit_id, 
            $is_primary_unit, 
            $product_unit_remarks
        );

        $result = $this->productService->create(
            Hashids::decode($request['company_id'])[0],
            $request['code'], 
            Hashids::decode($request['group_id'])[0], 
            Hashids::decode($request['brand_id'])[0], 
            $request['name'],          
            $request['tax_status'], 
            $request['remarks'], 
            $request['estimated_capital_price'], 
            $request['point'],
            $is_use_serial, 
            $request['product_type'],
            $request['status'],
            $productservice
        );
        return $result == 0 ? response()->error():response()->success();
    }

    public function update($id, Request $request)
    {
        $request->validate([
            'code' => new uniqueCode('update', $id, 'products'),
            'name' => 'required|max:255',
            'status' => 'required',
        ]);

        $productservice = array (
            'code' => $request['code'],
            'group_id' => $request['group_id'],
            'name' => $request['name'],
            'unit_id' => $request['unit_id'],
            'tax_status' => $request['tax_status'],
            'remarks' => $request['remarks'],
            'point' => $request['point'],
            'product_type' => $request['product_type'],
            'status' => $request['status'],
        );

        $is_use_serial = $request['is_use_serial'];
        $is_use_serial == 'on' ? $is_use_serial = 1 : $is_use_serial = 0;

        $result = $this->productService->update(
            $id,
            Hashids::decode($request['company_id'])[0],
            $request['code'], 
            Hashids::decode($request['group_id'])[0], 
            Hashids::decode($request['brand_id'])[0], 
            $request['name'], 
            $request['tax_status'], 
            $request['remarks'], 
            $request['estimated_capital_price'], 
            $request['point'],
            $is_use_serial, 
            $request['product_type'],
            $request['status'],
            $productservice
        );
        return $result == 0 ? response()->error():response()->success();
    }

    public function delete($id)
    {
        $result = $this->productService->delete($id);

        return $result == 0 ? response()->error():response()->success();
    }
}
