<?php

namespace App\Http\Controllers;

use App\Enums\ProductCategory;
use Exception;
use App\Models\Company;
use App\Models\Product;
use App\Enums\ProductType;
use Illuminate\Http\Request;
use App\Services\ProductService;
use Vinkla\Hashids\Facades\Hashids;
use App\Http\Requests\ProductRequest;
use App\Http\Resources\ProductResource;

class ProductController extends BaseController
{
    private $productService;

    public function __construct(ProductService $productService)
    {
        parent::__construct();

        $this->middleware('auth');
        $this->productService = $productService;
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
        
        $brandId = null;
        if(array_key_exists('brand_id', $request)) {
            $brandId = Hashids::decode($request['brand_id'])[0];
        }
        
        $useSerialNumber = false;
        if (array_key_exists('use_serial_number', $request)) {
            $useSerialNumber = $request['use_serial_number'];
        };

        $hasExpiryDate = false;
        if (array_key_exists('has_expiry_date', $request)) {
            $hasExpiryDate = $request['has_expiry_date'];
        };

        $productArr = [
            'company_id' => $company_id,
            'code' => $code,
            'product_group_id' => Hashids::decode($request['product_group_id'])[0],
            'brand_id' => $brandId,
            'name' => $request['name'],
            'product_type' => $request['product_type'],
            'taxable_supply' => $request['taxable_supply'],
            'standard_rated_supply' => $request['standard_rated_supply'],
            'price_include_vat' => $request['price_include_vat'],
            'point' => $request['point'],
            'use_serial_number' => $useSerialNumber,
            'has_expiry_date' => $hasExpiryDate,
            'remarks' => $request['remarks'],
            'status' => $request['status'],
        ];

        $productUnitsArr = [];
        $count_unit = count($request['product_units_unit_id']);
        for ($i = 0; $i < $count_unit; $i++) {
            $productUnitCode = array_key_exists('product_units_code', $request) ? $request['product_units_code'][$i] : '[AUTO]';

            if ($productUnitCode == config('dcslab.KEYWORDS.AUTO')) {
                do {
                    $productUnitCode = $this->productService->generateUniqueCodeForProductUnits();
                } while (!$this->productService->isUniqueCodeForProductUnits($productUnitCode, $company_id));
            } else {
                if (!$this->productService->isUniqueCodeForProductUnits($productUnitCode, $company_id)) {
                    return response()->error([
                        'code' => [trans('rules.unique_code')],
                    ], 422);
                }
            }
            
            $product_units_unit_id = Hashids::decode($request['product_units_unit_id'][$i])[0];

            $product_units_conv_value = $request['product_units_conv_value'][$i];

            $product_units_is_base = filter_var($request['product_units_is_base'][$i], FILTER_VALIDATE_BOOLEAN);
            $product_units_is_base = $product_units_is_base == true ? 1 : 0;

            $product_units_is_primary_unit = filter_var($request['product_units_is_primary_unit'][$i], FILTER_VALIDATE_BOOLEAN);
            $product_units_is_primary_unit = $product_units_is_primary_unit == true ? 1 : 0;

            $product_units_remarks = $request['product_units_remarks'][$i];

            array_push($productUnitsArr, [
                'company_id' => $company_id,
                'code' => $productUnitCode,
                'unit_id' => $product_units_unit_id,
                'conversion_value' => $product_units_conv_value,
                'is_base' => $product_units_is_base,
                'is_primary_unit' => $product_units_is_primary_unit,
                'remarks' => $product_units_remarks,
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

    public function list(ProductRequest $productRequest)
    {
        $request = $productRequest->validated();

        $productCategory = array_key_exists('productCategory', $request) ? $request['productCategory'] : ProductCategory::PRODUCTS_AND_SERVICES->value;
        $search = $request['search'];
        $paginate = $request['paginate'];
        $page = array_key_exists('page', $request) ? abs($request['page']) : 1;
        $perPage = array_key_exists('perPage', $request) ? abs($request['perPage']) : 10;

        $companyId = $request['company_id'];

        $result = null;
        $errorMsg = '';

        try {
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        $result = $this->productService->list(
            companyId: $companyId,
            productCategory: $productCategory,
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

    public function getProductType(Request $request)
    {
        if ($request->has('type') && $request['type'] == 'product') {
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

    public function read(Product $product, ProductRequest $productRequest)
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

        $brandId = null;
        if(array_key_exists('brand_id', $request)) {
            $brandId = Hashids::decode($request['brand_id'])[0];
        }

        $useSerialNumber = false;
        if (array_key_exists('use_serial_number', $request)) {
            $useSerialNumber = $request['use_serial_number'];
        };

        $hasExpiryDate = false;
        if (array_key_exists('has_expiry_date', $request)) {
            $hasExpiryDate = $request['has_expiry_date'];
        };

        $productArr = [
            'company_id' => $company_id,
            'code' => $code,
            'product_group_id' => Hashids::decode($request['product_group_id'])[0],
            'brand_id' => $brandId,
            'name' => $request['name'],
            'product_type' => $request['product_type'],
            'taxable_supply' => $request['taxable_supply'],
            'standard_rated_supply' => $request['standard_rated_supply'],
            'price_include_vat' => $request['price_include_vat'],
            'point' => $request['point'],
            'use_serial_number' => $useSerialNumber,
            'has_expiry_date' => $hasExpiryDate,
            'remarks' => $request['remarks'],
            'status' => $request['status'],
        ];

        $productUnitsArr = [];
        $count_unit = count($request['product_units_unit_id']);
        for ($i = 0; $i < $count_unit; $i++) {
            $product_unit_id = $request['product_units_hId'][$i] == '' ? null : Hashids::decode($request['product_units_hId'][$i])[0];
            
            $productUnitCode = array_key_exists('product_units_code', $request) ? $request['product_units_code'][$i] : '[AUTO]';

            if ($productUnitCode == config('dcslab.KEYWORDS.AUTO')) {
                do {
                    $productUnitCode = $this->productService->generateUniqueCodeForProductUnits();
                } while (!$this->productService->isUniqueCodeForProductUnits($productUnitCode, $company_id, $product_unit_id));
            } else {
                if (!$this->productService->isUniqueCodeForProductUnits($productUnitCode, $company_id, $product_unit_id)) {
                    return response()->error([
                        'code' => [trans('rules.unique_code')],
                    ], 422);
                }
            }
            
            $product_units_unit_id = Hashids::decode($request['product_units_unit_id'][$i])[0];

            $product_units_conv_value = $request['product_units_conv_value'][$i];

            $product_units_is_base = filter_var($request['product_units_is_base'][$i], FILTER_VALIDATE_BOOLEAN);
            $product_units_is_base = $product_units_is_base == true ? 1 : 0;

            $product_units_is_primary_unit = filter_var($request['product_units_is_primary_unit'][$i], FILTER_VALIDATE_BOOLEAN);
            $product_units_is_primary_unit = $product_units_is_primary_unit == true ? 1 : 0;

            $product_units_remarks = $request['product_units_remarks'][$i];

            array_push($productUnitsArr, [
                'id' => $product_unit_id,
                'company_id' => $company_id,
                'product_id' => $product->id,
                'code' => $productUnitCode,
                'unit_id' => $product_units_unit_id,
                'conversion_value' => $product_units_conv_value,
                'is_base' => $product_units_is_base,
                'is_primary_unit' => $product_units_is_primary_unit,
                'remarks' => $product_units_remarks,
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
}
