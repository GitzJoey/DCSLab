<?php

namespace App\Http\Controllers;

use App\Services\WarehouseService;
use Illuminate\Http\Request;

use Vinkla\Hashids\Facades\Hashids;

class WarehouseController extends Controller
{
    private $warehouseService;

    public function __construct(WarehouseService $warehouseService)
    {
        $this->middleware('auth');
        $this->warehouseService = $warehouseService;
    }

    public function index()
    {
        $test = $this->read();

        return view('company.warehouses.index', compact('test'));
    }

    public function read()
    {
        return $this->warehouseService->read();
    }

    public function store(Request $request)
    {
        $request->validate([
            'company_id' => 'required|max:255',
            'code' => 'required|max:255',
            'name' => 'required|max:255',
            'address' => 'required|max:255',
            'city' => 'required|max:255',
            'contact' => 'required|max:255',
            'remarks' => 'required|max:255',
            'is_active' => 'required'
        ]);

        $rolePermissions = [];
        for($i = 0; $i < count($request['permissions']); $i++) {
            array_push($rolePermissions, array (
                'id' => Hashids::decode($request['permissions'][$i])[0]
            ));
        }

        $result = $this->warehouseService->create(
            $request['company_id'],
            $request['code'],
            $request['name'],
            $request['address'],
            $request['city'],
            $request['contact'],
            $request['remarks'],
            $request['is_active'],
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

        $result = $this->warehouseService->update(
            $id,
            $request['company_id'],
            $request['code'],
            $request['name'],
            $request['address'],
            $request['city'],
            $request['contact'],
            $request['remarks'],
            $request['is_active'],
            $inputtedRolePermissions
        );

        return response()->json();
    }

    public function delete($id)
    {
        $this->warehouseService->delete($id);

        return response()->json();
    }
}