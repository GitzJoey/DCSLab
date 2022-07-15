<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductGroupRequest;
use App\Http\Resources\ProductGroupResource;
use App\Models\ProductGroup;
use App\Services\ProductGroupService;
use Exception;

class ProductGroupController extends BaseController
{
    private $productGroupService;

    public function __construct(ProductGroupService $productGroupService)
    {
        parent::__construct();

        $this->middleware('auth');
        $this->productGroupService = $productGroupService;
    }

    public function store(ProductGroupRequest $productGroupRequest)
    {
        $request = $productGroupRequest->validated();

        $company_id = $request['company_id'];

        $code = $request['code'];
        if ($code == config('dcslab.KEYWORDS.AUTO')) {
            do {
                $code = $this->productGroupService->generateUniqueCode();
            } while (!$this->productGroupService->isUniqueCode($code, $company_id));
        } else {
            if (!$this->productGroupService->isUniqueCode($code, $company_id)) {
                return response()->error([
                    'code' => [trans('rules.unique_code')],
                ], 422);
            }
        }

        $productgroupArr = [
            'company_id' => $request['company_id'],
            'code' => $code,
            'name' => $request['name'],
            'category' => $request['category'],
        ];

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->productGroupService->create($productgroupArr);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function list(ProductGroupRequest $productGroupRequest)
    {
        $request = $productGroupRequest->validated();

        $companyId = $request['company_id'];
        $category = array_key_exists('category', $request) ? $request['category'] : 3;
        $search = $request['search'];
        $paginate = $request['paginate'];
        $page = array_key_exists('page', $request) ? abs($request['page']) : 1;
        $perPage = array_key_exists('perPage', $request) ? abs($request['perPage']) : 10;

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->productGroupService->list(
                companyId: $companyId,
                category: $category,
                search: $search,
                paginate: $paginate,
                page: $page,
                perPage: $perPage
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            $response = ProductGroupResource::collection($result);

            return $response;
        }
    }

    public function read(ProductGroup $productgroup, ProductGroupRequest $productgroupRequest)
    {
        $request = $productgroupRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->productGroupService->read($productgroup);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            $response = new ProductGroupResource($result);

            return $response;
        }
    }

    public function update(ProductGroup $productgroup, ProductGroupRequest $productGroupRequest)
    {
        $request = $productGroupRequest->validated();

        $company_id = $request['company_id'];

        $code = $request['code'];
        if ($code == config('dcslab.KEYWORDS.AUTO')) {
            do {
                $code = $this->productGroupService->generateUniqueCode();
            } while (!$this->productGroupService->isUniqueCode($code, $company_id, $productgroup->id));
        } else {
            if (!$this->productGroupService->isUniqueCode($code, $company_id, $productgroup->id)) {
                return response()->error([
                    'code' => [trans('rules.unique_code')],
                ], 422);
            }
        }

        $productgroupArr = [
            'code' => $code,
            'name' => $request['name'],
            'category' => $request['category'],
        ];

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->productGroupService->update(
                $productgroup,
                $productgroupArr
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(ProductGroup $productgroup)
    {
        $result = false;
        $errorMsg = '';

        try {
            $result = $this->productGroupService->delete($productgroup);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return !$result ? response()->error($errorMsg) : response()->success();
    }
}
