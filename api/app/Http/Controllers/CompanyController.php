<?php

namespace App\Http\Controllers;

use App\Actions\Company\CompanyActions;
use App\Http\Requests\CompanyRequest;
use App\Http\Resources\CompanyResource;
use App\Models\Company;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyController extends Controller
{
    private $companyActions;

    public function __construct(CompanyActions $companyActions)
    {
        parent::__construct();

        $this->middleware('auth');
        $this->companyActions = $companyActions;
    }

    public function store(CompanyRequest $companyRequest)
    {
        $request = $companyRequest->validated();

        $user = Auth::user();

        $code = $request['code'];
        if ($code == config('dcslab.KEYWORDS.AUTO')) {
            do {
                $code = $this->companyActions->generateUniqueCode();
            } while (! $this->companyActions->isUniqueCode($code, $user->id));
        } else {
            if (! $this->companyActions->isUniqueCode($code, $user->id)) {
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
                $this->companyActions->resetDefaultCompany($user);
            }

            $result = $this->companyActions->create($companyArr);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function readAny(CompanyRequest $companyRequest)
    {
        $userId = Auth::id();
        $request = $companyRequest->validated();

        $search = $request['search'];
        $paginate = $request['paginate'];
        $page = array_key_exists('page', $request) ? abs($request['page']) : 1;
        $perPage = array_key_exists('perPage', $request) ? abs($request['perPage']) : 10;
        $useCache = array_key_exists('refresh', $request) ? boolval($request['refresh']) : true;

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->companyActions->readAny(
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
            $result = $this->companyActions->read($company);
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

        $result = $this->companyActions->getAllActiveCompany($userId, $with);

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
        $defaultCompany = $this->companyActions->getDefaultCompany($user);

        return $defaultCompany->hId;
    }

    public function update(Company $company, CompanyRequest $companyRequest)
    {
        $request = $companyRequest->validated();

        $user = Auth::user();

        $code = $request['code'];
        if ($code == config('dcslab.KEYWORDS.AUTO')) {
            do {
                $code = $this->companyActions->generateUniqueCode();
            } while (! $this->companyActions->isUniqueCode($code, $user->id, $company->id));
        } else {
            if (! $this->companyActions->isUniqueCode($code, $user->id, $company->id)) {
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
                $this->companyActions->resetDefaultCompany($user);
            }

            $result = $this->companyActions->update(
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
            if ($this->companyActions->isDefaultCompany($company)) {
                return response()->error(trans('rules.company.delete_default_company'));
            }

            $result = $this->companyActions->delete($company);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}