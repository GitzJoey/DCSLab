<?php

namespace App\Http\Controllers;

use App\Actions\RandomGenerator;
use App\Rules\uniqueCode;
use App\Services\CompanyService;
use App\Services\ActivityLogService;

use Illuminate\Http\Request;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

class CompanyController extends BaseController
{
    private $companyService;
    private $activityLogService;

    public function __construct(CompanyService $companyService, ActivityLogService $activityLogService)
    {
        parent::__construct();

        $this->middleware('auth');
        $this->companyService = $companyService;
        $this->activityLogService = $activityLogService;
    }

    public function index(Request $request)
    {
        $this->activityLogService->RoutingActivity($request->route()->getName(), $request->all());

        return view('company.companies.index');
    }

    public function read()
    {
        $userId = Auth::user()->id;
        return $this->companyService->read($userId);
    }

    public function getAllActiveCompany()
    {
        $userId = Auth::user()->id;
        return $this->companyService->getAllActiveCompany($userId);
    }

    public function store(Request $request)
    {
        if ($request['code'] == 'AUTO') {
            $randomGenerator = new randomGenerator();
            $request['code'] = $randomGenerator->generateOne(99999999);
            // $request->code = (new RandomGenerator())->generateOne(99999999);
        };

        $request->validate([
            'code' => ['required', 'max:255', new uniqueCode('create', '', 'companies')],
            'name' => 'required|max:255',
            'status' => 'required'
        ]);

        $default = $request['default'];
        if ($default == 'on') {
            $userId = Auth::user()->id;
            $this->companyService->resetDefaultCompany($userId);
        };

        $default = $request['default'];
        $default == 'on' ? $default = 1 : $default = 0;

        $userId = Auth::user()->id;

        $result = $this->companyService->create(
            $request['code'],
            $request['name'],
            $default,
            $request['status'],
            $userId
        );

        return $result == 0 ? response()->error():response()->success();
    }

    public function update($id, Request $request)
    {
        $request->validate([
            'code' => new uniqueCode('update', $id, 'companies'),
            'name' => 'required|max:255',
            'status' => 'required'
        ]);

        $default = $request['default'];
        if ($default == "on") {
            $userId = Auth::user()->id;
            $this->companyService->resetDefaultCompany($userId);
        };

        $default = $request['default'];
        $default == 'on' ? $default = 1 : $default = 0;

        $result = $this->companyService->update(
            $id,
            $request['code'],
            $request['name'],
            $default,
            $request['status']
        );

        return $result == 0 ? response()->error():response()->success();
    }

    public function delete($id)
    {
        $userId = Auth::user()->id;

        if ($this->companyService->isDefaultCompany($id))
            return response()->error(trans('rules.company.default_company'));

        $result = $this->companyService->delete($userId, $id);

        if ($id == Hashids::decode(session(Config::get('const.DEFAULT.SESSIONS.SELECTED_COMPANY')))[0])
            session()->put(Config::get('const.DEFAULT.SESSIONS.SELECTED_COMPANY'), $this->companyService->getDefaultCompany($userId)->hId);

        return $result == 0 ? response()->error():response()->success();
    }

    public function switchCompany($id)
    {
        session()->put(Config::get('const.DEFAULT.SESSIONS.SELECTED_COMPANY'), Hashids::encode($id));

        return redirect()->back();
    }
}