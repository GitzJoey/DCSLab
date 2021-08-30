<?php

namespace App\Http\Controllers;

use App\Rules\uniqueCode;
use App\Services\ActivityLogService;
use App\Services\ProductBrandService;
use Illuminate\Http\Request;

use Vinkla\Hashids\Facades\Hashids;

class ProductBrandController extends Controller
{
    private $productBrandService;
    private $activityLogService;

    public function __construct(ProductBrandService $productBrandService, ActivityLogService $activityLogService)
    {
        $this->middleware('auth');
        $this->productBrandService = $productBrandService;
        $this->activityLogService = $activityLogService;

    }

    public function index(Request $request)
    {
        $this->activityLogService->RoutingActivity($request->route()->getName(), $request->all());

        return view('product.brands.index');
    }

    public function read()
    {
        return $this->productBrandService->read();
    }

    public function getAllActiveProductBrand()
    {
        return $this->productBrandService->getAllActiveProductBrand();
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => new uniqueCode('create', '', 'productbrands'),
            'code' => 'required|max:255',
            'name' => 'required|max:255'
        ]);

        $result = $this->productBrandService->create($request['code'], $request['name']);
        
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
            'code' => new uniqueCode('update', $id, 'productbrands'),
            'name' => 'required|max:255',
        ]);

        $result = $this->productBrandService->update(
            $id,
            $request['code'],
            $request['name'],
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
        $result = $this->productBrandService->delete($id);

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
