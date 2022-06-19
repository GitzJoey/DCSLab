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

    public function read(CompanyRequest $companyRequest)
    {
        $userId = Auth::id();
        $request = $companyRequest->validated();

        $search = $request['search'];
        $paginate = $request['paginate'];
        $page = array_key_exists('page', $request) ? abs($request['page']) : 1;
        $perPage = array_key_exists('perPage', $request) ? abs($request['perPage']) : 10;

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

    public function getAllActiveCompany(Request $request)
    {
        $userId = Auth::id();

        $with = $request->has('with') ? explode(',', $request['with']) : []; 

        $result = $this->companyService->getAllActiveCompany($userId, $with);
    
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
            } while (!$this->companyService->isUniqueCode($code, $userId));
        } else {
            if (!$this->companyService->isUniqueCode($code, $userId)) {
                return response()->error([
                    'code' => [trans('rules.unique_code')]
                ], 422);
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
            } while (!$this->companyService->isUniqueCode($code, $userId, $id));
        } else {
            if (!$this->companyService->isUniqueCode($code, $userId, $id)) {
                return response()->error([
                    'code' => [trans('rules.unique_code')]
                ], 422);
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
