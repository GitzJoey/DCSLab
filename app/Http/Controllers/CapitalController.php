<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Vinkla\Hashids\Facades\Hashids;

use App\Services\ActivityLogService;
use App\Services\CapitalService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

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
        if (!parent::hasSelectedCompanyOrCompany())
        return response()->error(trans('error_messages.unable_to_find_selected_company'));

        $userId = Auth::user()->id;
        return $this->capitalService->read($userId);
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

        $company_id = session(Config::get('const.DEFAULT.SESSIONS.SELECTED_COMPANY'));
        $company_id = Hashids::decode($company_id)[0];

        $result = $this->capitalService->create(
            $company_id,
            $request['ref_number'],
            Hashids::decode($request['investor_id'])[0], 
            Hashids::decode($request['group_id'])[0], 
            Hashids::decode($request['cash_id'])[0],
            $date, 
            $request['capital_status'], 
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

        $date = '2021-10-01';
        // $date = [
        //     'PREFS.DATE_FORMAT' => $request['dateFormat'],
        //     'PREFS.TIME_FORMAT' => $request['timeFormat'],
        // ];

        $company_id = session(Config::get('const.DEFAULT.SESSIONS.SELECTED_COMPANY'));
        $company_id = Hashids::decode($company_id)[0];

        $result = $this->capitalService->update(
            $id,
            $company_id,
            $request['ref_number'], 
            Hashids::decode($request['investor_id'])[0], 
            Hashids::decode($request['group_id'])[0], 
            Hashids::decode($request['cash_id'])[0],
            $date, 
            $request['capital_status'],
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
