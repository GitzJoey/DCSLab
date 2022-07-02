<?php

namespace App\Http\Controllers;

use App\Enums\ProductType;
use App\Http\Requests\ProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Services\ProductService;
use Exception;
use Illuminate\Http\Request;
use Vinkla\Hashids\Facades\Hashids;

class ProductController extends BaseController
{
    private $productService;

    public function __construct(ProductService $productService)
    {
        parent::__construct();

        $this->middleware('auth');
        $this->productService = $productService;
    }

    public function listProducts(ProductRequest $productRequest)
    {
        $request = $productRequest->validated();

        $search = $request['search'];
        $paginate = $request['paginate'];
        $page = array_key_exists('page', $request) ? abs($request['page']) : 1;
        $perPage = array_key_exists('perPage', $request) ? abs($request['perPage']) : 10;

        $companyId = $request['company_id'];

        $result = $this->productService->list(
            companyId: $companyId,
            isProduct: true,
            isService: false,
            search: $search,
            paginate: $paginate,
            page: $page,
            perPage: $perPage
        );

        if (is_null($result)) {
            return response()->error();
        } else {
            $response = ProductResource::collection($result);

            return $response;
        }
    }

    public function listServices(ProductRequest $productRequest)
    {
        $request = $productRequest->validated();

        $search = $request['search'];
        $paginate = $request['paginate'];
        $page = array_key_exists('page', $request) ? abs($request['page']) : 1;
        $perPage = array_key_exists('perPage', $request) ? abs($request['perPage']) : 10;

        $companyId = $request['company_id'];

        $result = $this->productService->list(
                    companyId: $companyId,
                    isProduct: false,
                    isService: true,
                    search: $search,
                    paginate: $paginate,
                    page: $page,
                    perPage: $perPage
                );

        if (is_null($result)) {
            return response()->error();
        } else {
            $response = ProductResource::collection($result);

            return $response;
        }
    }

    public function readProducts(Product $product, ProductRequest $productRequest)
    {
        $request = $productRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->productService->read($product);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            $response = new ProductResource($result);

            return $response;
        }
    }

    public function readServices(Product $product, ProductRequest $productRequest)
    {
        $request = $productRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->productService->read($product);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            $response = ProductResource::collection($result);

            return $response;
        }
    }

    public function store(ProductRequest $productRequest)
    {
        $request = $productRequest->validated();

        $company_id = $request['company_id'];

        $code = $request['code'];
        if ($code == config('dcslab.KEYWORDS.AUTO')) {
            do {
                $code = $this->productService->generateUniqueCodeForProduct();
            } while (!$this->productService->isUniqueCodeForProduct($code, $company_id));
        } else {
            if (!$this->productService->isUniqueCodeForProduct($code, $company_id)) {
                return response()->error([
                    'code' => [trans('rules.unique_code')],
                ], 422);
            }
        }

        $productArr = [
            'code' => $code,
            'name' => $request['name'],
            'product_group_id' => Hashids::decode($request['product_group_id'])[0],
            'brand_id' => Hashids::decode($request['brand_id'])[0],
            'remarks' => $request['remarks'],
            'point' => $request['point'],
            'use_serial_number' => $request['use_serial_number'],
            'has_expiry_date' => $request['has_expiry_date'],
            'product_type' => $request['product_type'],
            'status' => $request['status'],
            'taxable_supply' => $request['taxable_supply'],
            'standard_rated_supply' => $request['standard_rated_supply'],
            'price_include_vat' => $request['price_include_vat'],
        ];

        $productUnitsArr = [];
        $count_unit = count($request['unit_id']);
        for ($i = 0; $i < $count_unit; $i++) {
            $is_base = $request['is_base'][$i] == '1' ? true : false;

            $is_primary_unit = $request['is_primary_unit'][$i] == '1' ? true : false;

            array_push($productUnitsArr, [
                'company_id' => $company_id,
                'code' => $code,
                'unit_id' => Hashids::decode($request['unit_id'][$i])[0],
                'conv_value' => $request['conv_value'][$i],
                'is_base' => $is_base,
                'is_primary_unit' => $is_primary_unit,
                'remarks' => '',
            ]);
        }

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->productService->create(
                $productArr,
                $productUnitsArr
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function update(Product $product, ProductRequest $productRequest)
    {
        $request = $productRequest->validated();

        $company_id = $request['company_id'];

        $code = $request['code'];
        if ($code == config('dcslab.KEYWORDS.AUTO')) {
            do {
                $code = $this->productService->generateUniqueCodeForProduct($company_id);
            } while (!$this->productService->isUniqueCodeForProduct($code, $company_id, $product->id));
        } else {
            if (!$this->productService->isUniqueCodeForProduct($code, $company_id, $product->id)) {
                return response()->error([
                    'code' => [trans('rules.unique_code')],
                ], 422);
            }
        }

        $productArr = [
            'company_id' => $company_id,
            'code' => $code,
            'name' => $request['name'],
            'product_group_id' => Hashids::decode($request['product_group_id'])[0],
            'brand_id' => Hashids::decode($request['brand_id'])[0],
            'remarks' => $request['remarks'],
            'point' => $request['point'],
            'use_serial_number' => $request['use_serial_number'],
            'has_expiry_date' => $request['has_expiry_date'],
            'product_type' => $request['product_type'],
            'status' => $request['status'],
            'taxable_supply' => $request['taxable_supply'],
            'standard_rated_supply' => $request['standard_rated_supply'],
            'price_include_vat' => $request['price_include_vat'],
        ];

        $use_serial_number = $request['use_serial_number'];
        $has_expiry_date = $request['has_expiry_date'];

        $productUnitsArr = [];
        $count_unit = count($request['unit_id']);
        for ($i = 0; $i < $count_unit; $i++) {
            $product_unit_id = $request['product_units_hId'][$i] == 0 ? 0 : Hashids::decode($request['product_units_hId'][$i])[0];
            $code = $request['product_units_code'][$i];

            $is_base = is_null($request['is_base'][$i]) ? 0 : $request['is_base'][$i];
            $is_base = is_numeric($is_base) ? $is_base : 0;

            $is_primary_unit = is_null($request['is_primary_unit'][$i]) ? 0 : $request['is_primary_unit'][$i];
            $is_primary_unit = is_numeric($is_primary_unit) ? $is_primary_unit : 0;

            $use_serial_number = is_null($use_serial_number) ? 0 : $use_serial_number;
            $use_serial_number = is_numeric($use_serial_number) ? $use_serial_number : 0;

            $has_expiry_date = is_null($has_expiry_date) ? 0 : $has_expiry_date;
            $has_expiry_date = is_numeric($has_expiry_date) ? $has_expiry_date : 0;

            array_push($productUnitsArr, [
                'id' => $product_unit_id,
                'company_id' => $company_id,
                'product_id' => $product->id,
                'code' => $code,
                'unit_id' => Hashids::decode($request['unit_id'][$i])[0],
                'conv_value' => $request['conv_value'][$i],
                'is_base' => $is_base,
                'is_primary_unit' => $is_primary_unit,
                'remarks' => $request['remarks'],
            ]);
        }

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->productService->update(
                $product,
                $productArr,
                $productUnitsArr
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(Product $product)
    {
        $result = false;
        $errorMsg = '';

        try {
            $result = $this->productService->delete($product);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return !$result ? response()->error($errorMsg) : response()->success();
    }

    public function getProductType(Request $request)
    {
        if ($request->has('type') && $request['type'] == 'products') {
            return [
                ['name' => 'components.dropdown.values.productTypeDDL.raw', 'code' => ProductType::RAW_MATERIAL->name],
                ['name' => 'components.dropdown.values.productTypeDDL.wip', 'code' => ProductType::WORK_IN_PROGRESS->name],
                ['name' => 'components.dropdown.values.productTypeDDL.fg', 'code' => ProductType::FINISHED_GOODS->name],
            ];
        } elseif ($request->has('type') && $request['type'] == 'service') {
            return [
                ['name' => 'components.dropdown.values.productTypeDDL.svc', 'code' => ProductType::SERVICE->name],
            ];
        } else {
            return [
                ['name' => 'components.dropdown.values.productTypeDDL.raw', 'code' => ProductType::RAW_MATERIAL->name],
                ['name' => 'components.dropdown.values.productTypeDDL.wip', 'code' => ProductType::WORK_IN_PROGRESS->name],
                ['name' => 'components.dropdown.values.productTypeDDL.fg', 'code' => ProductType::FINISHED_GOODS->name],
                ['name' => 'components.dropdown.values.productTypeDDL.svc', 'code' => ProductType::SERVICE->name],
            ];
        }
    }
}
