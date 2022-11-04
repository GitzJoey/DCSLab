<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use App\Models\Company;
use Illuminate\Http\Request;
use App\Services\CompanyService;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\CompanyRequest;
use App\Http\Resources\CompanyResource;

class CompanyController extends BaseController
{
    private $companyService;

    public function __construct(CompanyService $companyService)
    {
        parent::__construct();

        $this->middleware('auth');
        $this->companyService = $companyService;
    }

    public function store(CompanyRequest $companyRequest)
    {
        $request = $companyRequest->validated();

        $user = Auth::user();

        $code = $request['code'];
        if ($code == config('dcslab.KEYWORDS.AUTO')) {
            do {
                $code = $this->companyService->generateUniqueCode();
            } while (! $this->companyService->isUniqueCode($code, $user->id));
        } else {
            if (! $this->companyService->isUniqueCode($code, $user->id)) {
                return response()->error([
                    'code' => [trans('rules.unique_code')],
                ], 422);
            }
        }

        $companyArr = [
            'user_id' => $user->id,
            'code' => $code,
            'name' => $request['name'],
            'address' => $request['address'],
            'default' => $request['default'],
            'status' => $request['status'],

        ];

        $result = null;
        $errorMsg = '';

        try {
            if ($companyArr['default']) {
                $this->companyService->resetDefaultCompany($user);
            }

            $result = $this->companyService->create($companyArr);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function list(CompanyRequest $companyRequest)
    {
        $userId = Auth::id();
        $request = $companyRequest->validated();

        $search = $request['search'];
        $paginate = $request['paginate'];
        $page = array_key_exists('page', $request) ? abs($request['page']) : 1;
        $perPage = array_key_exists('perPage', $request) ? abs($request['perPage']) : 10;
        $useCache = array_key_exists('useCache', $request) ? boolval($request['useCache']) : true;

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->companyService->list(
                userId: $userId,
                search: $search,
                paginate: $paginate,
                page: $page,
                perPage: $perPage,
                useCache: $useCache
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
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
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            $response = new CompanyResource($result);

            return $response;
        }
    }

    public function getAllActiveCompany(Request $request)
    {
        $userId = $request->user()->id;

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
        $user = Auth::user();
        $defaultCompany = $this->companyService->getDefaultCompany($user);

        return $defaultCompany->hId;
    }

    public function update(Company $company, CompanyRequest $companyRequest)
    {
        $request = $companyRequest->validated();

        $user = Auth::user();

        $code = $request['code'];
        if ($code == config('dcslab.KEYWORDS.AUTO')) {
            do {
                $code = $this->companyService->generateUniqueCode();
            } while (! $this->companyService->isUniqueCode($code, $user->id, $company->id));
        } else {
            if (! $this->companyService->isUniqueCode($code, $user->id, $company->id)) {
                return response()->error([
                    'code' => [trans('rules.unique_code')],
                ], 422);
            }
        }

        $companyArr = [
            'code' => $code,
            'name' => $request['name'],
            'address' => $request['address'],
            'default' => $request['default'],
            'status' => $request['status'],
            'user_id' => $user->id,
        ];

        $result = null;
        $errorMsg = '';

        try {
            if ($companyArr['default']) {
                $this->companyService->resetDefaultCompany($user);
            }

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
        $result = false;
        $errorMsg = '';

        try {
            if ($this->companyService->isDefaultCompany($company)) {
                return response()->error(trans('rules.company.delete_default_company'));
            }

            $result = $this->companyService->delete($company);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}