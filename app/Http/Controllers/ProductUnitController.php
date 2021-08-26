<?php

namespace App\Http\Controllers;

use App\Rules\uniqueCode;
use App\Services\ActivityLogService;
use App\Services\ProductUnitService;
use Illuminate\Http\Request;

use Vinkla\Hashids\Facades\Hashids;

class ProductUnitController extends Controller
{
    private $productUnitService;
    private $activityLogService;

    public function __construct(ProductUnitService $productUnitService, ActivityLogService $activityLogService)
    {
        $this->middleware('auth');
        $this->productUnitService = $productUnitService;
        $this->activityLogService = $activityLogService;

    }

    public function index(Request $request)
    {
        $this->activityLogService->RoutingActivity($request->route()->getName(), $request->all());
        
        return view('product.units.index');
    }

    public function read()
    {
        return $this->productUnitService->read();
    }

    public function getAllProductUnit()
    {
        return $this->productUnitService->getAllProductUnit();
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|max:255|unique:product_units',
            'name' => 'required|max:255'
        ]);

        $result = $this->productUnitService->create($request['code'], $request['name']);

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
            'code' => new uniqueCode($id, 'product_units'),
            'name' => 'required|max:255',
        ]);

        $result = $this->productUnitService->update(
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
        $result = $this->productUnitService->delete($id);

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
