<?php

namespace App\Http\Controllers;

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

    public function index()
    {

        return view('product.units.index');
    }

    public function read()
    {
        return $this->productUnitService->read();
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|max:255',
            'name' => 'required|max:255'
        ]);

        $rolePermissions = [];
        for($i = 0; $i < count($request['permissions']); $i++) {
            array_push($rolePermissions, array (
                'id' => Hashids::decode($request['permissions'][$i])[0]
            ));
        }

        $result = $this->productUnitService->create(
            $request['code'],
            $request['name'],
            $rolePermissions
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

        $result = $this->productUnitService->update(
            $id,
            $request['code'],
            $request['name'],
            $inputtedRolePermissions
        );

        return response()->json();
    }

    public function delete($id)
    {
        $this->productUnitService->delete($id);

        return response()->json();
    }
}
