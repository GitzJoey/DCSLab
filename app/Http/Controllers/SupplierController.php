<?php

namespace App\Http\Controllers;

use App\Rules\uniqueCode;
use App\Services\ActivityLogService;
use App\Services\SupplierService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Vinkla\Hashids\Facades\Hashids;

class SupplierController extends BaseController
{
    private $SupplierService;
    private $activityLogService;

    public function __construct(SupplierService $SupplierService, ActivityLogService $activityLogService)
    {
        parent::__construct();

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
        $userId = Auth::user()->id;
        return $this->SupplierService->read($userId);
    }

    public function getAllSupplier()
    {
        return $this->SupplierService->getAllSupplier();
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
            // Hashids::decode($request['company_id'])[0],
            null,
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
        
        return $result == 0 ? response()->error():response()->success();
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
            Hashids::decode($request['company_id'])[0],
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

        return $result == 0 ? response()->error():response()->success();
    }

    public function delete($id)
    {
        $result = $this->SupplierService->delete($id);

        return $result == 0 ? response()->error():response()->success();
    }
}
