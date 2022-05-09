<?php

namespace App\Http\Controllers;

use App\Http\Requests\CompanyRequest;
use App\Http\Resources\CompanyResource;
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
        $search = !is_null($search) ? $search : '';

        $paginate = $request->has('paginate') ? $request['paginate']:true;
        $paginate = !is_null($paginate) ? $paginate : true;
        $paginate = is_numeric($paginate) ? abs($paginate) : true;

        $page = $request->has('page') ? $request['page']:1;
        $page = !is_null($page) ? $page : 1;
        $page = is_numeric($page) ? abs($page) : 1; 

        $perPage = $request->has('perPage') ? $request['perPage']:10;
        $perPage = !is_null($perPage) ? $perPage : 10;
        $perPage = is_numeric($perPage) ? abs($perPage) : 10;  

        $result = $this->companyService->read(
            userId: $userId, 
            search: $search,
            paginate: $paginate,
            page: $page,
            perPage: $perPage
        );
        
        if (is_null($result)) {
            return response()->error();
        } else {
            $response = CompanyResource::collection($result);

            return $response;
        }
    }

    public function getAllActiveCompany()
    {
        $userId = Auth::id();
        $result = $this->companyService->getAllActiveCompany($userId);
    
        if (is_null($result)) {
            return response()->error();
        } else {
            $response = CompanyResource::collection($result);

            return $response;
        }
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
        $default = false;

        if (array_key_exists('default', $request) && $request['default']) {
            $this->companyService->resetDefaultCompany($userId);
            $default = true;
        };

        $code = $request['code'];
        if ($code == config('const.DEFAULT.KEYWORDS.AUTO')) {
            do {
                $code = $this->companyService->generateUniqueCode();
                $isUniqueCode = $this->companyService->isUniqueCode($code, $userId);
            } while ($isUniqueCode == false);
        } else {
            $isUniqueCode = $this->companyService->isUniqueCode($code, $userId);
            if ($isUniqueCode == false) {
                return response()->error([
                    'code' => trans('rules.unique_code')
                ]);
            }
        }
        
        $result = $this->companyService->create(
            $code,
            $request['name'],
            $request['address'],
            $default,
            $request['status'],
            $userId
        );

        return is_null($result) ? response()->error() : response()->success();
    }

    public function update($id, CompanyRequest $companyRequest)
    {
        $request = $companyRequest->validated();

        $userId = Auth::id();
        $default = false;

        if ($request['default'] == true) {
            $this->companyService->resetDefaultCompany($userId);
            $default = true;
        };

        $code = $request['code'];
        if ($code == config('const.DEFAULT.KEYWORDS.AUTO')) {
            do {
                $code = $this->companyService->generateUniqueCode();
                $isUniqueCode = $this->companyService->isUniqueCode($code, $userId, $id);
            } while ($isUniqueCode == false);
        } else {
            $isUniqueCode = $this->companyService->isUniqueCode($code, $userId, $id);
            if ($isUniqueCode == false) {
                return response()->error([
                    'code' => trans('rules.unique_code')
                ]);
            }
        }

        $result = $this->companyService->update(
            $id,
            $code,
            $request['name'],
            $request['address'],
            $default,
            $request['status']
        );

        return is_null($result) ? response()->error() : response()->success();
    }

    public function delete($id)
    {
        $userId = Auth::id();

        if ($this->companyService->isDefaultCompany($id))
            return response()->error(trans('rules.company.delete_default_company'));

        $result = $this->companyService->delete($userId, $id);

        return !$result ? response()->error() : response()->success();
    }
}
