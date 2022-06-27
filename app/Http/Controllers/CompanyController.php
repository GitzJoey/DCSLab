<?php

namespace App\Http\Controllers;

use App\Http\Requests\CompanyRequest;
use App\Http\Resources\CompanyResource;
use App\Models\Company;
use App\Services\CompanyService;
use Exception;
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

    public function list(CompanyRequest $companyRequest)
    {
        $userId = Auth::id();
        $request = $companyRequest->validated();

        $search = $request['search'];
        $paginate = $request['paginate'];
        $page = array_key_exists('page', $request) ? abs($request['page']) : 1;
        $perPage = array_key_exists('perPage', $request) ? abs($request['perPage']) : 10;

        $result = $this->companyService->list(
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

    public function read(Company $company, CompanyRequest $companyRequest)
    {
        $request = $companyRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->companyService->read($company);
        } catch(Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }
        
        if (is_null($result)) {
            return response()->error($errorMsg);
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

        $companyArr = $request;
        $userId = Auth::id();

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

        $companyArr['user_id'] = $userId;
        $companyArr['code'] = $code;
        $companyArr['default'] = false;

        $result = null;
        $errorMsg = '';

        try {
            if (array_key_exists('default', $request) && $request['default']) {
                $this->companyService->resetDefaultCompany($userId);
                $companyArr['default'] = true;
            };

            $result = $this->companyService->create(
                $companyArr
            );
        
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function update(Company $company, CompanyRequest $companyRequest)
    {
        $request = $companyRequest->validated();

        $companyArr = $request;
        $userId = Auth::id();

        $code = $request['code'];
        if ($code == config('const.DEFAULT.KEYWORDS.AUTO')) {
            do {
                $code = $this->companyService->generateUniqueCode();
            } while (!$this->companyService->isUniqueCode($code, $userId, $company->id));
        } else {
            if (!$this->companyService->isUniqueCode($code, $userId, $company->id)) {
                return response()->error([
                    'code' => [trans('rules.unique_code')]
                ], 422);
            }
        }

        $companyArr['code'] = $code;
        $companyArr['default'] = false;

        $result = null;
        $errorMsg = '';

        try {
            if ($request['default'] == true) {
                $this->companyService->resetDefaultCompany($userId);
                $companyArr['default'] = true;
            };
    
            $result = $this->companyService->update(
                $company,
                $companyArr
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(Company $company)
    {
        $user = Auth::user();

        if ($this->companyService->isDefaultCompany($user->id)) 
            return response()->error(trans('rules.company.delete_default_company'));

        $result = false;
        $errorMsg = '';

        try {
            $result = $this->companyService->delete($company, $user);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return !$result ? response()->error($errorMsg) : response()->success();
    }
}
