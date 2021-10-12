<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Vinkla\Hashids\Facades\Hashids;

use App\Services\ActivityLogService;
use App\Services\CapitalService;

class CapitalController extends BaseController
{
    private $capitalService;
    private $activityLogService;

    public function __construct(CapitalService $capitalService, ActivityLogService $activityLogService)
    {
        parent::__construct();

        $this->middleware('auth');
        $this->capitalService = $capitalService;
        $this->activityLogService = $activityLogService;
    }

    public function index(Request $request)
    {
        $this->activityLogService->RoutingActivity($request->route()->getName(), $request->all());

        return view('finance.capitals.index');
    }

    public function read()
    {
        return $this->capitalService->read();
    }

    public function store(Request $request)
    {   
        $request->validate([
            'ref_number' => 'required|max:255',
        ]);
        
        $date = '2021-10-01';
        // $date = [
        //     'PREFS.DATE_FORMAT' => $request['dateFormat'],
        //     'PREFS.TIME_FORMAT' => $request['timeFormat'],
        // ];

        $result = $this->capitalService->create(
            $request['ref_number'],
            Hashids::decode($request['investor_id'])[0], 
            Hashids::decode($request['group_id'])[0], 
            Hashids::decode($request['cash_id'])[0],
            $date, 
            $request['amount'], 
            $request['remarks'],
        );
        return $result == 0 ? response()->error():response()->success();
    }

    public function update($id, Request $request)
    {
        $request->validate([
            'ref_number' => 'required|max:255',
        ]);

        $date = [
            'PREFS.DATE_FORMAT' => $request['dateFormat'],
            'PREFS.TIME_FORMAT' => $request['timeFormat'],
        ];

        $result = $this->capitalService->update(
            $id,
            Hashids::decode($request['investor'])[0], 
            Hashids::decode($request['capital_group'])[0], 
            Hashids::decode($request['cash_id'])[0],
            $request['ref_number'], 
            $date, 
            $request['amount'], 
            $request['remarks'],
        );
        return $result == 0 ? response()->error():response()->success();
    }

    public function delete($id)
    {
        $result = $this->capitalService->delete($id);

        return $result == 0 ? response()->error():response()->success();
    }
}
