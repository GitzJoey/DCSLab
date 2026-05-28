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

    public function view(Company $company, CompanyRequest $companyRequest): JsonResponse
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
            return $this->apiResponse($errorMsg, Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $response = new CompanyResource($result);
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
                return  $this->apiResponse([
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
}
