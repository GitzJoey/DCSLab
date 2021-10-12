<?php

namespace App\Http\Controllers;

use App\Rules\uniqueCode;
use App\Services\ActivityLogService;
use App\Services\InvestorService;
use Illuminate\Http\Request;

class InvestorController extends BaseController
{
    private $investorService;
    private $activityLogService;

    public function __construct(InvestorService $investorService, ActivityLogService $activityLogService)
    {
        parent::__construct();

        $this->middleware('auth');
        $this->investorService = $investorService;
        $this->activityLogService = $activityLogService;
    }

    public function index(Request $request)
    {
        $this->activityLogService->RoutingActivity($request->route()->getName(), $request->all());
        
        return view('finance.investors.index');
    }

    public function read()
    {
        return $this->investorService->read();
    }

    public function getAllActiveInvestor()
    {
        return $this->investorService->getAllActiveInvestor();
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => ['required', 'max:255', new uniqueCode('create', '', 'investors')],
            'name' => 'required|max:255',
            'status' => 'required'
        ]);

        $result = $this->investorService->create(
            $request['code'],
            $request['name'], 
            $request['contact'], 
            $request['address'], 
            $request['city'],
            $request['tax_number'], 
            $request['remarks'], 
            $request['status']
        );
        
        return $result == 0 ? response()->error():response()->success();
    }

    public function update($id, Request $request)
    {
        $request->validate([
            'code' => new uniqueCode('update', $id, 'investors'),
            'name' => 'required|max:255',
            'status' => 'required'
        ]);

        $result = $this->investorService->update(
            $id,
            $request['code'],
            $request['name'],
            $request['contact'],
            $request['address'],
            $request['city'],
            $request['tax_number'],
            $request['remarks'],
            $request['status'],
        );

        return $result == 0 ? response()->error():response()->success();
    }

    public function delete($id)
    {
        $result = $this->investorService->delete($id);

        return $result == 0 ? response()->error():response()->success();
    }
}
