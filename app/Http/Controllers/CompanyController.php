<?php

namespace App\Http\Controllers;

use App\Http\Requests\CompanyRequest;
use App\Services\CompanyService;
use App\Services\ActivityLogService;

use Illuminate\Http\Request;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

class CompanyController extends BaseController
{
    private $companyService;

    public function __construct(CompanyService $companyService)
    {
        parent::__construct();

        $this->middleware('auth');
        $this->companyService = $companyService;
    }

    public function read()
    {
        $userId = Auth::id();
        return $this->companyService->read($userId);
    }

    public function getAllActiveCompany()
    {
        $userId = Auth::id();
        return $this->companyService->getAllActiveCompany($userId);
    }

    public function store(CompanyRequest $companyRequest)
    {
        $request = $companyRequest->validated();

        $default = $request['default'];
        if ($default == 'on') {
            $userId = Auth::user()->id;
            $this->companyService->resetDefaultCompany($userId);
        };

        $default = $request['default'];
        $default == 'on' ? $default = 1 : $default = 0;

        $userId = Auth::id();

        $result = $this->companyService->create(
            $request['code'],
            $request['name'],
            $default,
            $request['status'],
            $userId
        );

        return is_null($result) ? response()->error():response()->success();
    }

    public function update($id, CompanyRequest $companyRequest)
    {
        $request = $companyRequest->validated();

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

        return is_null($result) ? response()->error():response()->success();
    }

    public function delete($id)
    {
        $userId = Auth::id();

        if ($this->companyService->isDefaultCompany($id))
            return response()->error(trans('rules.company.default_company'));

        $result = $this->companyService->delete($userId, $id);

        if ($id == Hashids::decode(session(Config::get('const.DEFAULT.SESSIONS.SELECTED_COMPANY')))[0])
            session()->put(Config::get('const.DEFAULT.SESSIONS.SELECTED_COMPANY'), $this->companyService->getDefaultCompany($userId)->hId);

        return is_null($result) ? response()->error():response()->success();
    }

    public function switchCompany($id)
    {
        session()->put(Config::get('const.DEFAULT.SESSIONS.SELECTED_COMPANY'), Hashids::encode($id));

        return redirect()->back();
    }
}
