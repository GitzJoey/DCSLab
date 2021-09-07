<?php

namespace App\Http\Controllers;

use App\Rules\uniqueCode;
use Illuminate\Http\Request;
use App\Services\ProductService;

use Illuminate\Support\Facades\Auth;
use Vinkla\Hashids\Facades\Hashids;
use App\Services\ActivityLogService;

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
        $userId = Auth::user()->id;
        return $this->productService->read($userId);
    }

    public function store(Request $request)
    {   
        $request->validate([
            'code' => 'required|max:255',
            'code' => new uniqueCode('create', '', 'products'),
            'name' => 'required|max:255',
            'price' => 'required|max:255',
            'status' => 'required',
        ]);

        $is_use_serial = $request['is_use_serial'];
        $is_use_serial == 'on' ? $is_use_serial = 1 : $is_use_serial = 0;

        $result = $this->productService->create(
            $request['code'], 
            Hashids::decode($request['group_id'])[0], 
            Hashids::decode($request['brand_id'])[0], 
            $request['name'], 
            Hashids::decode($request['unit_id'])[0],
            $request['price'], 
            $request['tax_status'], 
            $request['remarks'], 
            $request['estimated_capital_price'], 
            $request['point'],
            $is_use_serial, 
            $request['product_type'],
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
        
        $request->validate([
            'code' => new uniqueCode('update', $id, 'products'),
            'name' => 'required|max:255',
            'price' => 'required|max:255',
            'status' => 'required',
        ]);

        $is_use_serial = $request['is_use_serial'];
        $is_use_serial == 'on' ? $is_use_serial = 1 : $is_use_serial = 0;

        $result = $this->productService->update(
            $id,
            $request['code'], 
            Hashids::decode($request['group_id'])[0], 
            Hashids::decode($request['brand_id'])[0], 
            $request['name'], 
            Hashids::decode($request['unit_id'])[0],
            $request['price'], 
            $request['tax_status'], 
            $request['remarks'], 
            $request['estimated_capital_price'], 
            $request['point'],
            $is_use_serial, 
            $request['product_type'],
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
