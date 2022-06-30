<?php

namespace App\Http\Controllers;

use App\Services\BrandService;
use App\Http\Requests\BrandRequest;
use App\Http\Resources\BrandResource;
use App\Models\Brand;
use Exception;

class BrandController extends BaseController
{
    private $brandService;

    public function __construct(BrandService $brandService)
    {
        parent::__construct();

        $this->middleware('auth');
        $this->brandService = $brandService;
    }

    public function list(BrandRequest $brandRequest)
    {
        $request = $brandRequest->validated();

        $companyId = $request['company_id'];
        $search = $request['search'];
        $paginate = $request['paginate'];
        $page = array_key_exists('page', $request) ? abs($request['page']) : 1;
        $perPage = array_key_exists('perPage', $request) ? abs($request['perPage']) : 10;

        $result = $this->brandService->list(
            companyId: $companyId,
            search: $search, 
            paginate: $paginate, 
            page: $page,
            perPage: $perPage
        );

        if (is_null($result)) {
            return response()->error();
        } else {
            $response = BrandResource::collection($result);

            return $response;
        }
    }

    public function read(Brand $brand, BrandRequest $brandRequest)
    {
        $request = $brandRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->brandService->read($brand);
        } catch(Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }
        
        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            $response = BrandResource::collection($result);
            return $response;    
        }
    }

    public function store(BrandRequest $brandRequest)
    {
        $request = $brandRequest->validated();

        $company_id = $request['company_id'];

        $code = $request['code'];
        if ($code == config('dcslab.KEYWORDS.AUTO')) {
            do {
                $code = $this->brandService->generateUniqueCode();
            } while (!$this->brandService->isUniqueCode($code, $company_id));
        } else {
            if (!$this->brandService->isUniqueCode($code, $company_id)) {
                return response()->error([
                    'code' => [trans('rules.unique_code')]
                ], 422);
            }
        }

        $brandArr = [
            'code' => $code,
            'company_id' => $request['company_id'],
            'name' => $request['name'],
        ];

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->brandService->create($brandArr);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function update(Brand $brand, BrandRequest $brandRequest)
    {
        $request = $brandRequest->validated();

        $company_id = $request['company_id'];

        $code = $request['code'];
        if ($code == config('dcslab.KEYWORDS.AUTO')) {
            do {
                $code = $this->brandService->generateUniqueCode();
            } while (!$this->brandService->isUniqueCode($code, $company_id, $brand->id));
        } else {
            if (!$this->brandService->isUniqueCode($code, $company_id, $brand->id)) {
                return response()->error([
                    'code' => [trans('rules.unique_code')]
                ], 422);
            }
        }

        $brandArr = [
            'company_id' => $company_id,
            'code' => $code,
            'name' => $request['name'],
        ];

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->brandService->update(
                $brand,
                $brandArr
            );
        } catch(Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }
    
    public function delete(Brand $brand)
    {
        $result = false;
        $errorMsg = '';

        try {
            $result = $this->brandService->delete($brand);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return !$result ? response()->error($errorMsg) : response()->success();
    }
}