<?php

namespace App\Http\Controllers;

use App\Rules\uniqueCode;
use Illuminate\Http\Request;
use App\Services\ProductService;
use App\Services\ProductUnitService;

use Vinkla\Hashids\Facades\Hashids;
use App\Services\ActivityLogService;

class ProductController extends BaseController
{
    private $productService;

    public function __construct(ProductService $productService)
    {
        parent::__construct();

        $this->middleware('auth');
        $this->productService = $productService;
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

        if ($request->product_type !== '4') {
            $request->company_id != null ? $company_id = Hashids::decode($request->company_id)[0]:$company_id = null;
 
            $brand_id = Hashids::decode($request->brand_id)[0];
            $name = $request->name;

            // $product_units = [];
            // foreach ($request['product_unit_code'] as $product_unit) {
            //     array_push($product_units, 
            
            // );
            // }

            $product_units = [];
            $count_unit = count($request['unit_id']);
            for ($i = 0; $i < $count_unit; $i++) {
                array_push($product_units, array (
                    'company_id' => $request->company_id,
                    'code' => array_key_exists($i, $request['product_unit_code']) == true ? $request['product_unit_code'][$i]:null,
                    'is_base' => array_key_exists($i, $request['is_base']) == true ? $request['is_base'][$i]:null,
                    'conv_value' => array_key_exists($i, $request['conv_value']) == true ? $request['conv_value'][$i]:null,
                    'unit_id' => array_key_exists($i, $request['unit_id']) == true ? $request['unit_id'][$i]:null,
                    'is_primary_unit' => array_key_exists($i, $request['is_primary_unit']) == true ? $request['is_primary_unit'][$i]:null
                ));
            }

            // $product_units = array (
            //     #'company_id' => Hashids::decode($request['company_id'])[0],
            //     'code' => $request['product_unit_code'],
            //     'is_base' => $request['is_base'],
            //     'unit_id' => $request['unit_id'],
            //     'is_primary_unit' => $request['is_primary_unit'],
            // );

            $tax_status = $request->tax_status;
            $request['company_id'] != null ? $company_id = Hashids::decode($request['company_id'])[0]:$company_id = null;
            $supplier_id = Hashids::decode($request->supplier_id)[0];
            $remarks = $request->remarks;
            $point = $request->point;
            $is_use_serial = $request->is_use_serial;
            $product_type = $request->product_type;
            $status = $request->status;
        }

        $product = $this->productService->create(
            $company_id,
            $code, 
            $group_id,
            $brand_id,
            $name,
            $tax_status,
            $supplier_id,
            $remarks,
            $point,
            $is_use_serial,
            $product_type,
            $status,
        );


        
        // return $result == 0 ? response()->error():response()->success();
    }

    public function update($id, Request $request)
    {
        $request->validate([
            'code' => new uniqueCode('update', $id, 'products'),
            'name' => 'required|max:255',
            'status' => 'required',
        ]);

        $is_use_serial = $request['is_use_serial'];
        $is_use_serial == 'on' ? $is_use_serial = 1 : $is_use_serial = 0;

        $result = $this->productService->update(
            $id,
            Hashids::decode($request['company_id'])[0],
            $request['code'], 
            Hashids::decode($request['group_id'])[0],
            Hashids::decode($request['brand_id'])[0],
            $request['name'],
            $request['product_unit'],
            Hashids::decode($request['unit_id'])[0],
            $request['tax_status'],
            Hashids::decode($request['supplier_id'])[0],
            $request['remarks'],
            $request['point'],
            $is_use_serial,
            $request['product_type'],
            $request['status'],
        );
        return $result == 0 ? response()->error():response()->success();
    }

    public function delete($id)
    {
        $result = $this->productService->delete($id);

        return $result == 0 ? response()->error():response()->success();
    }
}
