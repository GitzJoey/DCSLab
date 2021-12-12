<?php

namespace App\Http\Controllers;

use App\Rules\uniqueCode;
use App\Services\ActivityLogService;
use App\Services\CashService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Support\Facades\Config;
use App\Actions\RandomGenerator;
use App\Models\Cash;

class CashController extends BaseController
{
    private $CashService;
    private $activityLogService;

    public function __construct(CashService $CashService, ActivityLogService $activityLogService)
    {
        parent::__construct();

        $this->middleware('auth');
        $this->CashService = $CashService;
        $this->activityLogService = $activityLogService;
    }

    public function index(Request $request)
    {
        $this->activityLogService->RoutingActivity($request->route()->getName(), $request->all());
        
        return view('finance.cashes.index');
    }

    public function read()
    {
        if (!parent::hasSelectedCompanyOrCompany())
        return response()->error(trans('error_messages.unable_to_find_selected_company'));

        $userId = Auth::user()->id;
        return $this->CashService->read($userId);
    }

    public function getAllActiveCash()
    {
        return $this->CashService->getAllActiveCash();
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => ['required', 'min:1', 'max:255', new uniqueCode('create', '', 'cashes')],
            'name' => 'required|min:2|max:255|alpha|alpha_dash',
            'status' => 'required'
        ]);

        $randomGenerator = new randomGenerator();
        $code = $request['code'];
        if ($code == 'AUTO') {
            $code_count = 1;
            do {
                $code = $randomGenerator->generateOne(99999999);
                $code_count = Cash::where('code', $code)->count();
            }
            while ($code_count != 0);
        };

        $company_id = session(Config::get('const.DEFAULT.SESSIONS.SELECTED_COMPANY'));
        $company_id = Hashids::decode($company_id)[0];

        $is_bank = $request['is_bank'] == 'on' ? 1 : $request['is_bank'];
        $is_bank = is_null($is_bank) ? 0 : $is_bank;
        $is_bank = is_numeric($is_bank) ? $is_bank : 0;

        $result = $this->CashService->create(
            $company_id,
            $code,
            $request['name'], 
            $is_bank, 
            $request['status']
        );
        return $result == 0 ? response()->error():response()->success();
    }

    public function update($id, Request $request)
    {
        $request->validate([
            'code' => new uniqueCode('update', $id, 'cashes'),
            'name' => 'required|min:2|max:255|alpha|alpha_dash',
            'status' => 'required'
        ]);

        $company_id = session(Config::get('const.DEFAULT.SESSIONS.SELECTED_COMPANY'));
        $company_id = Hashids::decode($company_id)[0];

        $is_bank = $request['is_bank'] == 'on' ? 1 : $request['is_bank'];
        $is_bank = is_null($is_bank) ? 0 : $is_bank;
        $is_bank = is_numeric($is_bank) ? $is_bank : 0;

        $result = $this->CashService->update(
            $id,
            $company_id,
            $request['code'],
            $request['name'],
            $is_bank,
            $request['status'],
        );
        return $result == 0 ? response()->error():response()->success();
    }

    public function delete($id)
    {
        $result = $this->CashService->delete($id);

        return $result == 0 ? response()->error():response()->success();
    }
}