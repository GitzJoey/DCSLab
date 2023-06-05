<?php

namespace App\Http\Controllers;

use App\Actions\ProductGroup\ProductGroupActions;
use App\Enums\ProductGroupCategory;
use App\Http\Requests\ProductGroupRequest;
use App\Http\Resources\ProductGroupResource;
use App\Models\ProductGroup;
use Exception;

class ProductGroupController extends BaseController
{
    private $productGroupActions;

    public function __construct(ProductGroupActions $productGroupActions)
    {
        parent::__construct();

        $this->middleware('auth');
        $this->productGroupActions = $productGroupActions;
    }

    public function store(ProductGroupRequest $productGroupRequest)
    {
        $request = $productGroupRequest->validated();

        $company_id = $request['company_id'];

        $code = $request['code'];
        if ($code == config('dcslab.KEYWORDS.AUTO')) {
            do {
                $code = $this->productGroupActions->generateUniqueCode();
            } while (! $this->productGroupActions->isUniqueCode($code, $company_id));
        } else {
            if (! $this->productGroupActions->isUniqueCode($code, $company_id)) {
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
            $result = $this->productGroupActions->create($productgroupArr);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function readAny(ProductGroupRequest $productGroupRequest)
    {
        $request = $productGroupRequest->validated();

        $companyId = $request['company_id'];

        $category = null;
        if (array_key_exists('category', $request)) {
            $category = $request['category'];
        }

        $search = $request['search'];
        $paginate = $request['paginate'];
        $page = array_key_exists('page', $request) ? abs($request['page']) : 1;
        $perPage = array_key_exists('per_page', $request) ? abs($request['per_page']) : 10;
        $useCache = array_key_exists('refresh', $request) ? boolval($request['refresh']) : true;

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->productGroupActions->readAny(
                companyId: $companyId,
                category: $category,
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
            $result = $this->productGroupActions->read($productgroup);
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
                $code = $this->productGroupActions->generateUniqueCode();
            } while (! $this->productGroupActions->isUniqueCode($code, $company_id, $productgroup->id));
        } else {
            if (! $this->productGroupActions->isUniqueCode($code, $company_id, $productgroup->id)) {
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
            $result = $this->productGroupActions->update(
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
            $result = $this->productGroupActions->delete($productgroup);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }

    public function getProductGroupCategory()
    {
        return [
            ['name' => 'components.dropdown.values.productGroupCategoryDDL.product', 'code' => ProductGroupCategory::PRODUCTS->name],
            ['name' => 'components.dropdown.values.productGroupCategoryDDL.service', 'code' => ProductGroupCategory::SERVICES->name],
        ];
    }
}
