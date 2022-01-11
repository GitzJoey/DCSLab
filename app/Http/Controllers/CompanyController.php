<?php

namespace App\Http\Controllers;

use App\Http\Requests\CompanyRequest;
use App\Services\CompanyService;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyController extends BaseController
{
    private $companyService;

    public function __construct(CompanyService $companyService)
    {
        parent::__construct();

        $this->middleware('auth');
        $this->companyService = $companyService;
    }

    public function read(Request $request)
    {
        $userId = Auth::id();
        $search = $request->has('search') && !is_null($request['search']) ? $request['search']:'';
        $paginate = true;
        $perPage = $request->has('perPage') ? $request['perPage']:null;

        return $this->companyService->read($userId, $search, $paginate, $perPage);
    }

    public function getAllActiveCompany()
    {
        $userId = Auth::id();
        return $this->companyService->getAllActiveCompany($userId);
    }

    public function getDefaultCompany()
    {
        $userId = Auth::id();
        $defaultCompany = $this->companyService->getDefaultCompany($userId);

        return $defaultCompany->hId;
    }

    public function store(CompanyRequest $companyRequest)
    {
        $request = $companyRequest->validated();

        $userId = Auth::id();
        $default = 0;

        if (array_key_exists('default', $request)) {
            $this->companyService->resetDefaultCompany($userId);
            $default = 1;
        };

        $code = $request['code'];

        $result = $this->companyService->create(
            $code,
            $request['name'],
            $request['address'],
            $default,
            $request['status'],
            $userId
        );

        return is_null($result) ? response()->error():response()->success();
    }

    public function update($id, CompanyRequest $companyRequest)
    {
        $request = $companyRequest->validated();

        $userId = Auth::id();
        $default = 0;

        if (array_key_exists('default', $request)) {
            $this->companyService->resetDefaultCompany($userId);
            $default = 1;
        };

        $code = $request['code'];

        $result = $this->companyService->update(
            $id,
            $code,
            $request['name'],
            $request['address'],
            $default,
            $request['status']
        );

        return is_null($result) ? response()->error():response()->success();
    }

    public function delete($id)
    {
        $userId = Auth::id();

        if ($this->companyService->isDefaultCompany($id))
            return response()->error(trans('rules.company.delete_default_company'));

        $result = $this->companyService->delete($userId, $id);

        return is_null($result) ? response()->error():response()->success();
    }
}
