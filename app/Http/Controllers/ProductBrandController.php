<?php

namespace App\Http\Controllers;

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

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|max:255',
            'name' => 'required|max:255'
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

        $result = $this->productBrandService->update(
            $id,
            $request['code'],
            $request['name'],
            $inputtedRolePermissions
        );

        return response()->json();
    }

    public function delete($id)
    {
        $this->productBrandService->delete($id);

        return response()->json();
    }
}
