<?php

namespace App\Http\Controllers;

use App\Actions\Company\CompanyActions;
use App\Http\Requests\CompanyRequest;
use App\Http\Resources\CompanyResource;
use App\Models\Company;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class CompanyController extends BaseController
{
    private CompanyActions $companyActions;

    public function __construct(CompanyActions $companyActions)
    {
        parent::__construct();

        $this->companyActions = $companyActions;
    }

    public function index(CompanyRequest $companyRequest): JsonResponse
    {
        $userId = Auth::id();
        $request = $companyRequest->validated();

        $search = $request['search'];
        $paginate = $request['paginate'];
        $page = array_key_exists('page', $request) ? abs($request['page']) : 1;
        $perPage = array_key_exists('per_page', $request) ? abs($request['per_page']) : 10;
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
            $errorMsg = app()->isProduction() ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return $this->apiResponse($errorMsg, Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $response = CompanyResource::collection($result);

        return $this->apiResponse($response, Response::HTTP_OK);
    }

    public function create(CompanyRequest $companyRequest): JsonResponse
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
                return $this->apiResponse([
                    'code' => [trans('controller.global.unique_code')],
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
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
            $errorMsg = app()->isProduction() ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return $this->apiResponse($errorMsg, Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->apiResponse(new CompanyResource($result), Response::HTTP_CREATED);
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
                    'code' => [trans('controller.global.unique_code')],
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
            $errorMsg = app()->isProduction() ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return $this->apiResponse($errorMsg, Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->apiResponse(new CompanyResource($result), Response::HTTP_OK);
    }

    public function destroy(Company $company, CompanyRequest $companyRequest)
    {
        // Throw Error
        // throw New \Exception('Test Exception From Controller');

        // Throw Empty Response Error (HttpStatus 500)
        // return response()->error();

        // Custom Validation Error 1 Message (HttpStatus 422)
        // return response()->error('Custom Validation Error 1 Message', 422);

        // Custom Validation With Multiple Error (HttpStatus 422)
        // return response()->error(['name' => ['Custom Validation With Multiple Error'], 'address' => ['Custom Validation With Multiple Error']], 422);

        $result = false;
        $errorMsg = '';

        $request = $companyRequest->validated();

        try {
            $result = $this->companyActions->delete($company);
        } catch (Exception $e) {
            $errorMsg = app()->isProduction() ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return $this->apiResponse($errorMsg, Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->apiResponse(null, Response::HTTP_NO_CONTENT);
    }
}
