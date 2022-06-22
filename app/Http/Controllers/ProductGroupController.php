<?php

namespace App\Http\Controllers;

use App\Services\ProductGroupService;
use App\Http\Requests\ProductGroupRequest;
use App\Http\Resources\ProductGroupResource;

class ProductGroupController extends BaseController
{
    private $productGroupService;

    public function __construct(ProductGroupService $productGroupService)
    {
        parent::__construct();

        $this->middleware('auth');
        $this->productGroupService = $productGroupService;
    }

    public function read(ProductGroupRequest $productGroupRequest)
    {
        $request = $productGroupRequest->validated();

        $companyId = $request['company_id'];
        $category = array_key_exists('category', $request) ? $request['category'] : null;
        $search = $request['search'];
        $paginate = $request['paginate'];
        $page = array_key_exists('page', $request) ? abs($request['page']) : 1;
        $perPage = array_key_exists('perPage', $request) ? abs($request['perPage']) : 10;

        $result = $this->productGroupService->read(
            companyId: $companyId, 
            category: $category, 
            search: $search, 
            paginate: $paginate, 
            page: $page,
            perPage: $perPage
        );

        if (is_null($result)) {
            return response()->error();
        } else {
            $response = ProductGroupResource::collection($result);

            return $response;
        }
    }
    
    public function store(ProductGroupRequest $productGroupRequest)
    {
        $request = $productGroupRequest->validated();

        $company_id = $request['company_id'];

        $code = $request['code'];
        if ($code == config('const.DEFAULT.KEYWORDS.AUTO')) {
            do {
                $code = $this->productGroupService->generateUniqueCode($company_id);
            } while (!$this->productGroupService->isUniqueCode($code, $company_id));
        } else {
            if (!$this->productGroupService->isUniqueCode($code, $company_id)) {
                return response()->error([
                    'code' => [trans('rules.unique_code')]
                ], 422);
            }
        }

        $name = $request['name'];
        $category = $request['category'];

        $result = $this->productGroupService->create(
            $company_id,
            $code, 
            $name,
            $category
        );

        return is_null($result) ? response()->error() : response()->success();
    }

    public function update($id, ProductGroupRequest $productGroupRequest)
    {
        $request = $productGroupRequest->validated();

        $company_id = $request['company_id'];

        $code = $request['code'];
        if ($code == config('const.DEFAULT.KEYWORDS.AUTO')) {
            do {
                $code = $this->productGroupService->generateUniqueCode($company_id);
            } while (!$this->productGroupService->isUniqueCode($code, $company_id, $id));
        } else {
            if (!$this->productGroupService->isUniqueCode($code, $company_id, $id)) {
                return response()->error([
                    'code' => [trans('rules.unique_code')]
                ], 422);
            }
        }

        $name = $request['name'];
        $category = $request['category'];

        $result = $this->productGroupService->update(
            id: $id,
            company_id: $company_id,
            code: $code, 
            name: $name,
            category: $category
        );

        return is_null($result) ? response()->error() : response()->success();
    }
}