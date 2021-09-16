<?php

namespace App\Http\Controllers;

use App\Rules\uniqueCode;
use App\Services\ActivityLogService;
use App\Services\ProductBrandService;
use Illuminate\Http\Request;

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
            'code' => 'required|max:255',
            'code' => new uniqueCode('create', '', 'productbrands'),
            'name' => 'required|max:255'
        ]);

        $result = $this->productBrandService->create($request['code'], $request['name']);
        
        return $result == 0 ? response()->error():response()->success();
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
        return $result == 0 ? response()->error():response()->success();
    }

    public function delete($id)
    {
        $result = $this->productBrandService->delete($id);

        return $result == 0 ? response()->error():response()->success();
    }
}
