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
            'name' => 'required|max:255',
            'price' => 'required|max:255',
            'information' => 'required|max:255',
            'estimated_capital_price' => 'required|max:255',
            'status' => 'required'
        ]);
       
        $result = 1;

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
            $request['is_use_serial'],
            $request['is_buy'],
            $request['is_production_material'],
            $request['is_production_result'],
            $request['is_sell'],
            $request['status'],
            $inputtedRolePermissions
        );

        return response()->json();
    }

    public function delete($id)
    {
        $this->productService->delete($id);

        return response()->json();
    }
}
