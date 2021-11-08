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

        if ($request->product_type != '4') {
            $company_id = Hashids::decode($request['company_id'])[0];
            $code = $request->code;
            $group_id = Hashids::decode($request->group_id)[0];
            $brand_id = Hashids::decode($request->brand_id)[0];
            $name = $request->name;
            $product_unit = array (
                'code' => $request['code'],
                'is_base' => $request['is_base'],
                'unit_id' => $request['unit_id'],
                'is_primary_unit' => $request['is_primary_unit'],
            );
            $tax_status = $request->tax_status;
            $supplier = Hashids::decode($request->supplier)[0];
            $remarks = $request->remarks;
            $point = $request->point;
            $is_use_serial = $request->is_use_serial;
            $product_type = $request->product_type;
            $status = $request->status;
        }

        $result = $this->productService->create(
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
