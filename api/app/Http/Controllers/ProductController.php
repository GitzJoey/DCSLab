<?php

namespace App\Http\Controllers;

use App\Actions\Product\ProductActions;
use App\Enums\ProductType;
use App\Http\Requests\ProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Company;
use App\Models\Product;
use Exception;
use Illuminate\Http\Request;

class ProductController extends BaseController
{
    private $productActions;

    public function __construct(ProductActions $productActions)
    {
        parent::__construct();

        $this->middleware('auth');
        $this->productActions = $productActions;
    }

    public function store(ProductRequest $productRequest)
    {
        $request = $productRequest->validated();

        $company_id = $request['company_id'];
        $product_group_id = $request['product_group_id'];
        $brand_id = $request['brand_id'];

        $code = $request['code'];
        if ($code == config('dcslab.KEYWORDS.AUTO')) {
            do {
                $code = $this->productActions->generateUniqueCodeForProduct();
            } while (! $this->productActions->isUniqueCodeForProduct($code, $company_id));
        } else {
            if (! $this->productActions->isUniqueCodeForProduct($code, $company_id)) {
                return response()->error([
                    'code' => [trans('rules.unique_code')],
                ], 422);
            }
        }

        if (! Company::find($company_id)->productGroups()->where('id', '=', $product_group_id)->exists()) {
            return response()->error([
                'product_group_id' => [trans('rules.valid_product_group')],
            ], 422);
        }

        if ($request['product_type'] !== ProductType::SERVICE->value) {
            if (! Company::find($company_id)->brands()->where('id', '=', $brand_id)->exists()) {
                return response()->error([
                    'brand_id' => [trans('rules.valid_brand')],
                ], 422);
            }
        }

        $useSerialNumber = false;
        if (array_key_exists('use_serial_number', $request)) {
            $useSerialNumber = $request['use_serial_number'];
        }

        $hasExpiryDate = false;
        if (array_key_exists('has_expiry_date', $request)) {
            $hasExpiryDate = $request['has_expiry_date'];
        }

        $productArr = [
            'company_id' => $company_id,
            'code' => $code,
            'product_group_id' => $product_group_id,
            'brand_id' => $request['brand_id'],
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

        $productUnitArr = [];
        $count_unit = count($request['arr_product_unit_unit_id']);
        for ($i = 0; $i < $count_unit; $i++) {
            $productUnitCode = array_key_exists('arr_product_unit_code', $request) ? $request['arr_product_unit_code'][$i] : '[AUTO]';

            if ($productUnitCode == config('dcslab.KEYWORDS.AUTO')) {
                do {
                    $productUnitCode = $this->productActions->generateUniqueCodeForProductUnits();
                } while (! $this->productActions->isUniqueCodeForProductUnits($productUnitCode, $company_id));
            } else {
                if (! $this->productActions->isUniqueCodeForProductUnits($productUnitCode, $company_id)) {
                    return response()->error([
                        'arr_product_unit_code' => [trans('rules.unique_code')],
                    ], 422);
                }
            }

            $product_units_unit_id = $request['arr_product_unit_unit_id'][$i];
            if (! Company::find($company_id)->Units()->where('id', '=', $product_units_unit_id)->exists()) {
                return response()->error([
                    'arr_product_unit_unit_id' => [trans('rules.valid_unit')],
                ], 422);
            }

            $product_units_conv_value = $request['arr_product_unit_conversion_value'][$i];

            $product_units_is_base = filter_var($request['arr_product_unit_is_base'][$i], FILTER_VALIDATE_BOOLEAN);
            $product_units_is_base = $product_units_is_base == true ? 1 : 0;

            $product_units_is_primary_unit = filter_var($request['arr_product_unit_is_primary_unit'][$i], FILTER_VALIDATE_BOOLEAN);
            $product_units_is_primary_unit = $product_units_is_primary_unit == true ? 1 : 0;

            $product_units_remarks = $request['arr_product_unit_remarks'][$i];

            array_push($productUnitArr, [
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
            $result = $this->productActions->create(
                $productArr,
                $productUnitArr
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function readAny(ProductRequest $productRequest)
    {
        $request = $productRequest->validated();

        $productCategory = array_key_exists('productCategory', $request) ? $request['productCategory'] : null;
        $search = $request['search'];
        $paginate = $request['paginate'];
        $page = array_key_exists('page', $request) ? abs($request['page']) : 1;
        $perPage = array_key_exists('perPage', $request) ? abs($request['perPage']) : 10;
        $useCache = array_key_exists('refresh', $request) ? boolval($request['refresh']) : true;

        $companyId = $request['company_id'];

        $result = null;
        $errorMsg = '';

        try {
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        $result = $this->productActions->readAny(
            companyId: $companyId,
            productCategory: $productCategory,
            search: $search,
            paginate: $paginate,
            page: $page,
            perPage: $perPage,
            useCache: $useCache
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
            $result = $this->productActions->read($product);
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

    public function getAllActiveProduct(ProductRequest $productRequest)
    {
        $request = $productRequest->validated();

        $company_ulid = Company::where('id', '=', $request['company_id'])->first()->ulid;

        $result = $this->productActions->getAllActiveProduct($company_ulid);

        if (is_null($result)) {
            return response()->error();
        } else {
            $response = ProductResource::collection($result);

            return $response;
        }
    }

    public function update(Product $product, ProductRequest $productRequest)
    {
        $request = $productRequest->validated();

        $company_id = $request['company_id'];
        $product_group_id = $request['product_group_id'];
        $brand_id = $request['brand_id'];

        $code = $request['code'];
        if ($code == config('dcslab.KEYWORDS.AUTO')) {
            do {
                $code = $this->productActions->generateUniqueCodeForProduct($company_id);
            } while (! $this->productActions->isUniqueCodeForProduct($code, $company_id, $product->id));
        } else {
            if (! $this->productActions->isUniqueCodeForProduct($code, $company_id, $product->id)) {
                return response()->error([
                    'code' => [trans('rules.unique_code')],
                ], 422);
            }
        }

        if (! Company::find($company_id)->productGroups()->where('id', '=', $product_group_id)->exists()) {
            return response()->error([
                'product_group_id' => [trans('rules.valid_product_group')],
            ], 422);
        }

        if ($request['product_type'] !== ProductType::SERVICE->value) {
            if (! Company::find($company_id)->brands()->where('id', '=', $brand_id)->exists()) {
                return response()->error([
                    'brand_id' => [trans('rules.valid_brand')],
                ], 422);
            }
        }

        $useSerialNumber = false;
        if (array_key_exists('use_serial_number', $request)) {
            $useSerialNumber = $request['use_serial_number'];
        }

        $hasExpiryDate = false;
        if (array_key_exists('has_expiry_date', $request)) {
            $hasExpiryDate = $request['has_expiry_date'];
        }

        $productArr = [
            'company_id' => $company_id,
            'code' => $code,
            'product_group_id' => $request['product_group_id'],
            'brand_id' => $request['brand_id'],
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

        $productUnitArr = [];
        $count_unit = count($request['arr_product_unit_unit_id']);
        for ($i = 0; $i < $count_unit; $i++) {
            $arr_product_unit_id = $request['arr_product_unit_id'][$i] != null ? $request['arr_product_unit_id'][$i] : null;

            $productUnitCode = array_key_exists('arr_product_unit_code', $request) ? $request['arr_product_unit_code'][$i] : '[AUTO]';

            if ($productUnitCode == config('dcslab.KEYWORDS.AUTO')) {
                do {
                    $productUnitCode = $this->productActions->generateUniqueCodeForProductUnits();
                } while (! $this->productActions->isUniqueCodeForProductUnits($productUnitCode, $company_id, $arr_product_unit_id));
            } else {
                if (! $this->productActions->isUniqueCodeForProductUnits($productUnitCode, $company_id, $arr_product_unit_id)) {
                    return response()->error([
                        'code' => [trans('rules.unique_code')],
                    ], 422);
                }
            }

            $product_units_unit_id = $request['arr_product_unit_unit_id'][$i];
            if (! Company::find($company_id)->Units()->where('id', '=', $product_units_unit_id)->exists()) {
                return response()->error([
                    'arr_product_unit_unit_id' => [trans('rules.valid_unit')],
                ], 422);
            }

            $product_units_conv_value = $request['arr_product_unit_conversion_value'][$i];

            $product_units_is_base = filter_var($request['arr_product_unit_is_base'][$i], FILTER_VALIDATE_BOOLEAN);
            $product_units_is_base = $product_units_is_base == true ? 1 : 0;

            $product_units_is_primary_unit = filter_var($request['arr_product_unit_is_primary_unit'][$i], FILTER_VALIDATE_BOOLEAN);
            $product_units_is_primary_unit = $product_units_is_primary_unit == true ? 1 : 0;

            $product_units_remarks = $request['arr_product_unit_remarks'][$i];

            array_push($productUnitArr, [
                'id' => $arr_product_unit_id,
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
            $result = $this->productActions->update(
                $product,
                $productArr,
                $productUnitArr
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
            $result = $this->productActions->delete($product);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
