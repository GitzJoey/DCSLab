<?php

namespace App\Http\Controllers;

use App\Rules\uniqueCode;
use App\Services\ActivityLogService;
use App\Services\UnitService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Support\Facades\Config;
use App\Actions\RandomGenerator;

class UnitController extends BaseController
{
    private $UnitService;
    private $activityLogService;

    public function __construct(UnitService $UnitService, ActivityLogService $activityLogService)
    {
        parent::__construct();

        $this->middleware('auth');
        $this->UnitService = $UnitService;
        $this->activityLogService = $activityLogService;
    }

    public function index(Request $request)
    {
        $this->activityLogService->RoutingActivity($request->route()->getName(), $request->all());
        
        return view('product.units.index');
    }

    public function read()
    {
        if (!parent::hasSelectedCompanyOrCompany())
        return response()->error(trans('error_messages.unable_to_find_selected_company'));

        $userId = Auth::user()->id;
        return $this->UnitService->read($userId);
    }

    public function getAllUnit_Product()
    {
        return $this->UnitService->getAllUnit_Product();
    }

    public function getAllUnit_Service()
    {
        return $this->UnitService->getAllUnit_Service();
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => ['required', 'min:1', 'max:255', new uniqueCode('create', '', 'units')],
            'name' => 'required|min:3|max:255',
            'category' => 'required'
        ]);

        if ($request['code'] == 'AUTO') {
            $randomGenerator = new randomGenerator();
            $request['code'] = $randomGenerator->generateOne(99999999);
        };

        $company_id = session(Config::get('const.DEFAULT.SESSIONS.SELECTED_COMPANY'));
        $company_id = Hashids::decode($company_id)[0];

        $result = $this->UnitService->create(
            $company_id,
            $request['code'],
            $request['name'],
            $request['category']
        );
        return $result == 0 ? response()->error():response()->success();
    }

    public function update($id, Request $request)
    {
        $request->validate([
            'code' => new uniqueCode('update', $id, 'units'),
            'name' => 'required|min:3|max:255|alpha_num|alpha_dash',
        ]);

        $company_id = session(Config::get('const.DEFAULT.SESSIONS.SELECTED_COMPANY'));
        $company_id = Hashids::decode($company_id)[0];

        $result = $this->UnitService->update(
            $id,
            $company_id,
            $request['code'],
            $request['name'],
            $request['category']
        );
        return $result == 0 ? response()->error():response()->success();
    }

    public function delete($id)
    {
        $result = $this->UnitService->delete($id);

        return $result == 0 ? response()->error():response()->success();
    }
}