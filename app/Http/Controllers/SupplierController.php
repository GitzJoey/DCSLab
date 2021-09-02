<?php

namespace App\Http\Controllers;

use App\Rules\uniqueCode;
use App\Services\ActivityLogService;
use App\Services\SupplierService;
use Illuminate\Http\Request;

use Vinkla\Hashids\Facades\Hashids;

class SupplierController extends Controller
{
    private $SupplierService;
    private $activityLogService;

    public function __construct(SupplierService $SupplierService, ActivityLogService $activityLogService)
    {
        $this->middleware('auth');
        $this->SupplierService = $SupplierService;
        $this->activityLogService = $activityLogService;

    }

    public function index(Request $request)
    {
        $this->activityLogService->RoutingActivity($request->route()->getName(), $request->all());
        
        return view('purchase.suppliers.index');
    }

    public function read()
    {
        return $this->SupplierService->read();
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|max:255',
            'code' => new uniqueCode('create', '', 'suppliers'),
            'name' => 'required|max:255',
            'status' => 'required'
        ]);
        
        $is_tax = $request['is_tax'];
        $is_tax == 'on' ? $is_tax = 1 : $is_tax = 0;

        $result = $this->SupplierService->create(
            $request['code'],
            $request['name'], 
            $request['term'], 
            $request['contact'], 
            $request['address'], 
            $request['city'],
            $is_tax, 
            $request['tax_number'], 
            $request['remarks'], 
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
            'code' => new uniqueCode('update', $id, 'suppliers'),
            'name' => 'required|max:255',
            'status' => 'required'
        ]);

        $is_tax = $request['is_tax'];
        $is_tax == 'on' ? $is_tax = 1 : $is_tax = 0;

        $result = $this->SupplierService->update(
            $id,
            $request['code'],
            $request['name'],
            $request['term'],
            $request['contact'],
            $request['address'],
            $request['city'],
            $is_tax,
            $request['tax_number'],
            $request['remarks'],
            $request['status'],
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
        $result = $this->SupplierService->delete($id);

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
