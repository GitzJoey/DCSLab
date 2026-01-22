<?php

namespace App\Http\Controllers;

use App\Actions\Product\ProductActions;
use App\Actions\Product\ProductPhysicalActions;
use App\Actions\Product\ProductServiceActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\ExecuteGetDTO;
use App\DTOs\ExecutePaginationDTO;
use App\DTOs\ProductPhysicalCreateDTO;
use App\DTOs\ProductPhysicalUpdateDTO;
use App\DTOs\ProductServiceCreateDTO;
use App\DTOs\ProductServiceUpdateDTO;
use App\Enums\ProductTypeEnum;
use App\Enums\RecordStatusEnum;
use App\Helpers\HashidsHelper;
use App\Http\Requests\Product\ProductPhysicalStoreRequest;
use App\Http\Requests\Product\ProductPhysicalUpdateRequest;
use App\Http\Requests\Product\ProductServiceStoreRequest;
use App\Http\Requests\Product\ProductServiceUpdateRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Rules\ExistsForCompany;
use App\Rules\IsValidCompany;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Enum;

class ProductController extends BaseController
{
    private $productActions;

    private $productPhysicalActions;

    private $productServiceActions;

    public function __construct(
        ProductActions $productActions,
        ProductPhysicalActions $productPhysicalActions,
        ProductServiceActions $productServiceActions
    ) {
        parent::__construct();

        $this->productActions = $productActions;
        $this->productPhysicalActions = $productPhysicalActions;
        $this->productServiceActions = $productServiceActions;
    }

    public function storePhysical(ProductPhysicalStoreRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            if ($validatedRequest['code'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUniqueCode = $this->productPhysicalActions->isUniqueCode(
                    $validatedRequest['company_id'], $validatedRequest['code'], null
                );
                if (! $isUniqueCode) return response()->error(['code' => [trans('rules.unique_code')]], 422);
            }

            $isUniqueName = $this->productPhysicalActions->isUniqueName(
                $validatedRequest['company_id'], $validatedRequest['name'], null
            );
            if (! $isUniqueName) return response()->error(['name' => [trans('rules.unique_name')]], 422);

            if ($validatedRequest['slug'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUniqueSlug = $this->productPhysicalActions->isUniqueSlug(
                    $validatedRequest['company_id'], $validatedRequest['slug'], null
                );
                if (! $isUniqueSlug) return response()->error(['slug' => [trans('rules.unique_slug')]], 422);
            }

            if (! (bool) $validatedRequest['is_taxable'] && (float) $validatedRequest['vat_rate'] > 0) {
                return response()->error(['vat_rate' => [trans('rules.product.vat.must_be_zero_if_not_taxable')]], 422);
            }

            if (! array_key_exists('remarks', $validatedRequest)) $validatedRequest['remarks'] = null;

            $units = $validatedRequest['product_units'];

            $unitCodes = array_map(fn ($u) => $u['code'], $units);
            $unitCodes = array_filter($unitCodes, fn ($code) => $code !== config('dcslab.KEYWORDS.AUTO'));
            $duplicateUnitCodes = array_filter(array_count_values($unitCodes), fn ($c) => $c > 1);
            if (! empty($duplicateUnitCodes)) {
                return response()->error(['product_units.code' => [trans('rules.product.unit.duplicate_code')]], 422);
            }

            $unitIds = array_map(fn ($u) => $u['unit_id'], $units);
            $dupUnitIds = array_filter(array_count_values($unitIds), fn ($c) => $c > 1);
            if (! empty($dupUnitIds)) {
                return response()->error(['product_units.unit_id' => [trans('rules.product.unit.duplicate_unit')]], 422);
            }

            $conversionValues = array_map(fn ($u) => (string) $u['conversion_value'], $units);
            $dupConversion = array_filter(array_count_values($conversionValues), fn ($c) => $c > 1);
            if (! empty($dupConversion)) {
                return response()->error(['product_units.conversion_value' => [trans('rules.product.unit.duplicate_conversion')]], 422);
            }

            $baseUnits = array_filter($units, fn ($u) => (float) $u['conversion_value'] == 1.0);
            $baseCount = count($baseUnits);
            if ($baseCount !== 1) {
                return response()->error(['product_units.conversion_value' => [trans('rules.product.unit.single_base')]], 422);
            }

            $primaryCount = count(array_filter($units, fn ($u) => (bool) $u['is_primary_unit']));
            if ($primaryCount !== 1) {
                return response()->error(['product_units.is_primary_unit' => [trans('rules.product.unit.single_primary')]], 422);
            }

            foreach ($validatedRequest['product_units'] as $index => $productUnit) {
                if (! array_key_exists('remarks', $productUnit)) {
                    $validatedRequest['product_units'][$index]['remarks'] = null;
                }
            }

            $result = $this->productPhysicalActions->create(
                new ProductPhysicalCreateDTO(
                    companyId: $validatedRequest['company_id'],
                    code: $validatedRequest['code'],
                    categoryId: $validatedRequest['category_id'],
                    brandId: $validatedRequest['brand_id'] ?? null,
                    name: $validatedRequest['name'],
                    slug: $validatedRequest['slug'],
                    isTaxable: $validatedRequest['is_taxable'],
                    vatRate: $validatedRequest['vat_rate'],
                    isPriceIncludeVat: $validatedRequest['is_price_include_vat'],
                    isUseSerialNumber: $validatedRequest['is_use_serial_number'] ?? false,
                    isExpirable: $validatedRequest['is_expirable'] ?? false,
                    remarks: $validatedRequest['remarks'] ?? null,
                    type: $validatedRequest['type'],
                    status: $validatedRequest['status'],
                    productUnits: $validatedRequest['product_units'],
                )
            );

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function storeService(ProductServiceStoreRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            if ($validatedRequest['code'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUniqueCode = $this->productServiceActions->isUniqueCode(
                    $validatedRequest['company_id'], $validatedRequest['code'], null
                );
                if (! $isUniqueCode) return response()->error(['code' => [trans('rules.unique_code')]], 422);
            }

            $isUniqueName = $this->productServiceActions->isUniqueName(
                $validatedRequest['company_id'], $validatedRequest['name'], null
            );
            if (! $isUniqueName) return response()->error(['name' => [trans('rules.unique_name')]], 422);

            if ($validatedRequest['slug'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUniqueSlug = $this->productServiceActions->isUniqueSlug(
                    $validatedRequest['company_id'], $validatedRequest['slug'], null
                );
                if (! $isUniqueSlug) return response()->error(['slug' => [trans('rules.unique_slug')]], 422);
            }

            if (! array_key_exists('remarks', $validatedRequest)) $validatedRequest['remarks'] = null;

            $result = $this->productServiceActions->create(
                new ProductServiceCreateDTO(
                    companyId: $validatedRequest['company_id'],
                    code: $validatedRequest['code'],
                    categoryId: $validatedRequest['category_id'],
                    name: $validatedRequest['name'],
                    slug: $validatedRequest['slug'],
                    isTaxable: $validatedRequest['is_taxable'],
                    vatRate: $validatedRequest['vat_rate'],
                    isPriceIncludeVat: $validatedRequest['is_price_include_vat'],
                    remarks: $validatedRequest['remarks'] ?? null,
                    status: $validatedRequest['status'],
                    unitId: $validatedRequest['unit_id'],
                    price: $validatedRequest['price'],
                    point: $validatedRequest['point'],
                )
            );

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function readAny(Request $request)
    {
        if (! Auth::check())  return response()->error(trans('rules.auth.unauthorized'), 401);
        $this->authorize('viewAny', Product::class);

        if ($request->filled('company_id')) $request->merge(['company_id' => HashidsHelper::decodeId($request->company_id)]);
        if ($request->filled('include_id')) $request->merge(['include_id' => HashidsHelper::decodeId($request->include_id)]);
        if ($request->filled('category_id')) $request->merge(['category_id' => HashidsHelper::decodeId($request->category_id)]);
        if ($request->filled('brand_id')) $request->merge(['brand_id' => HashidsHelper::decodeId($request->brand_id)]);

        $validatedRequest = $request->validate([
            'refresh' => ['required', 'boolean'],
            'with_trashed' => ['required', 'boolean'],

            'search' => ['nullable', 'string'],
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'category_id' => ['nullable', 'integer', new ExistsForCompany('product_categories', $request->company_id)],
            'brand_id' => ['nullable', 'integer', new ExistsForCompany('brands', $request->company_id)],
            'is_taxable' => ['nullable', 'boolean'],
            'vat_rate' => ['nullable', 'numeric', 'min:0'],
            'is_price_include_vat' => ['nullable', 'boolean'],
            'is_use_serial_number' => ['nullable', 'boolean'],
            'is_expirable' => ['nullable', 'boolean'],
            'type' => ['nullable', 'integer', new Enum(ProductTypeEnum::class)],
            'status' => ['nullable', 'integer', new Enum(RecordStatusEnum::class)],
            'include_id' => ['nullable', 'integer', new ExistsForCompany('products', $request->company_id)],

            'paginate' => ['nullable', 'array', 'required_without:get', 'prohibits:get'],
            'paginate.page' => ['required_with:paginate', 'integer', 'min:1'],
            'paginate.per_page' => ['required_with:paginate', 'integer', 'min:10'],
            'get' => ['nullable', 'array', 'required_without:paginate', 'prohibits:paginate'],
            'get.limit' => ['required_with:get', 'integer', 'min:10'],
        ]);

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->productActions->readAny(
                withTrashed: $validatedRequest['with_trashed'],
                companyId: $validatedRequest['company_id'],
                search: $validatedRequest['search'] ?? null,
                categoryId: $validatedRequest['category_id'] ?? null,
                brandId: $validatedRequest['brand_id'] ?? null,
                isTaxable: $validatedRequest['is_taxable'] ?? null,
                vatRate: $validatedRequest['vat_rate'] ?? null,
                isPriceIncludeVat: $validatedRequest['is_price_include_vat'] ?? null,
                isUseSerialNumber: $validatedRequest['is_use_serial_number'] ?? null,
                isExpirable: $validatedRequest['is_expirable'] ?? null,
                type: $validatedRequest['type'] ?? null,
                status: $validatedRequest['status'] ?? null,
                includeId: $validatedRequest['include_id'] ?? null,

                execute: new ExecuteDTO(
                    useCache: ! $validatedRequest['refresh'],
                    pagination: (function () use ($validatedRequest) {
                        $pagination = null;
                        if (isset($validatedRequest['paginate'])) {
                            $pagination = new ExecutePaginationDTO(
                                page: $validatedRequest['paginate']['page'],
                                perPage: $validatedRequest['paginate']['per_page'],
                            );
                        }

                        return $pagination;
                    })(),
                    get: (function () use ($validatedRequest) {
                        $get = null;
                        if (isset($validatedRequest['get'])) {
                            $get = new ExecuteGetDTO(
                                limit: $validatedRequest['get']['limit'],
                            );
                        }

                        return $get;
                    })()
                )
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            return ProductResource::collection($result);
        }
    }

    public function read(Product $product)
    {
        if (! Auth::check())  return response()->error(trans('rules.auth.unauthorized'), 401);
        $this->authorize('view', $product);

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
            return new ProductResource($result);
        }
    }

    public function updatePhysical(Product $product, ProductPhysicalUpdateRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            if ($validatedRequest['code'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUniqueCode = $this->productPhysicalActions->isUniqueCode(
                    $validatedRequest['company_id'], $validatedRequest['code'], $product->id,
                );
                if (! $isUniqueCode) return response()->error(['code' => [trans('rules.unique_code')]], 422);
            }

            $isUniqueName = $this->productPhysicalActions->isUniqueName(
                $validatedRequest['company_id'], $validatedRequest['name'], $product->id
            );
            if (! $isUniqueName) return response()->error(['name' => [trans('rules.unique_name')]], 422);

            if ($validatedRequest['slug'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUniqueSlug = $this->productPhysicalActions->isUniqueSlug(
                    $validatedRequest['company_id'], $validatedRequest['slug'], $product->id
                );
                if (! $isUniqueSlug) return response()->error(['slug' => [trans('rules.unique_slug')]], 422);
            }

            if (! (bool) $validatedRequest['is_taxable'] && (float) $validatedRequest['vat_rate'] > 0) {
                return response()->error(['vat_rate' => [trans('rules.product.vat.must_be_zero_if_not_taxable')]], 422);
            }
            if ((bool) $validatedRequest['is_taxable']) {
                $vat = (float) $validatedRequest['vat_rate'];
                if ($vat < 0 || $vat > 100) {
                    return response()->error(['vat_rate' => [trans('rules.product.vat.out_of_range')]], 422);
                }
            }

            if (! array_key_exists('remarks', $validatedRequest)) $validatedRequest['remarks'] = null;

            if (! array_key_exists('delete_product_unit_ids', $validatedRequest)) $validatedRequest['delete_product_unit_ids'] = null;

            // Prevent deleting base unit
            if (! empty($validatedRequest['delete_product_unit_ids'])) {
                $deletedUnits = $product->productUnits()->whereIn('id', $validatedRequest['delete_product_unit_ids'])->get();
                if ($deletedUnits->contains(fn ($unit) => (bool) $unit->is_base)) {
                    return response()->error(['delete_product_unit_ids' => [trans('rules.product.unit.cannot_delete_base_unit')]], 422);
                }
            }

            // Custom validations for product units
            $units = $validatedRequest['product_units'];

            $unitCodes = array_map(fn ($u) => $u['code'], $units);
            $unitCodes = array_filter($unitCodes, fn ($code) => $code !== config('dcslab.KEYWORDS.AUTO'));
            $dupUnitCodes = array_filter(array_count_values($unitCodes), fn ($c) => $c > 1);
            if (! empty($dupUnitCodes)) {
                return response()->error(['product_units.code' => [trans('rules.product.unit.duplicate_code')]], 422);
            }

            $unitIds = array_map(fn ($u) => $u['unit_id'], $units);
            $dupUnitIds = array_filter(array_count_values($unitIds), fn ($c) => $c > 1);
            if (! empty($dupUnitIds)) {
                return response()->error(['product_units.unit_id' => [trans('rules.product.unit.duplicate_unit')]], 422);
            }

            $conversionValues = array_map(fn ($u) => (string) $u['conversion_value'], $units);
            $dupConversion = array_filter(array_count_values($conversionValues), fn ($c) => $c > 1);
            if (! empty($dupConversion)) {
                return response()->error(['product_units.conversion_value' => [trans('rules.product.unit.duplicate_conversion')]], 422);
            }

            $baseUnits = array_filter($units, fn ($u) => (float) $u['conversion_value'] == 1.0);
            $baseCount = count($baseUnits);
            if ($baseCount !== 1) {
                return response()->error(['product_units.conversion_value' => [trans('rules.product.unit.single_base')]], 422);
            }

            $primaryCount = count(array_filter($units, fn ($u) => (bool) $u['is_primary_unit']));
            if ($primaryCount !== 1) {
                return response()->error(['product_units.is_primary_unit' => [trans('rules.product.unit.single_primary')]], 422);
            }

            foreach ($validatedRequest['product_units'] as $index => $productUnit) {
                if (! array_key_exists('remarks', $productUnit)) {
                    $validatedRequest['product_units'][$index]['remarks'] = null;
                }
            }

            $result = $this->productPhysicalActions->update(
                product: $product,
                data: new ProductPhysicalUpdateDTO(
                    companyId: $validatedRequest['company_id'],
                    code: $validatedRequest['code'],
                    categoryId: $validatedRequest['category_id'],
                    brandId: $validatedRequest['brand_id'] ?? null,
                    name: $validatedRequest['name'],
                    slug: $validatedRequest['slug'],
                    isTaxable: $validatedRequest['is_taxable'],
                    vatRate: $validatedRequest['vat_rate'],
                    isPriceIncludeVat: $validatedRequest['is_price_include_vat'],
                    isUseSerialNumber: $validatedRequest['is_use_serial_number'] ?? false,
                    isExpirable: $validatedRequest['is_expirable'] ?? false,
                    remarks: $validatedRequest['remarks'] ?? null,
                    type: $validatedRequest['type'],
                    status: $validatedRequest['status'],
                    productUnits: $validatedRequest['product_units'],
                    deleteProductUnitIds: $validatedRequest['delete_product_unit_ids'] ?? null,
                )
            );

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function updateService(Product $product, ProductServiceUpdateRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            if ($validatedRequest['code'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUniqueCode = $this->productServiceActions->isUniqueCode(
                    $validatedRequest['company_id'], $validatedRequest['code'], $product->id,
                );
                if (! $isUniqueCode) return response()->error(['code' => [trans('rules.unique_code')]], 422);
            }

            $isUniqueName = $this->productServiceActions->isUniqueName(
                $validatedRequest['company_id'], $validatedRequest['name'], $product->id
            );
            if (! $isUniqueName) return response()->error(['name' => [trans('rules.unique_name')]], 422);

            if ($validatedRequest['slug'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUniqueSlug = $this->productServiceActions->isUniqueSlug(
                    $validatedRequest['company_id'], $validatedRequest['slug'], $product->id
                );
                if (! $isUniqueSlug) return response()->error(['slug' => [trans('rules.unique_slug')]], 422);
            }

            if (! array_key_exists('remarks', $validatedRequest)) $validatedRequest['remarks'] = null;

            $result = $this->productServiceActions->update(
                product: $product,
                data: new ProductServiceUpdateDTO(
                    companyId: $validatedRequest['company_id'],
                    code: $validatedRequest['code'],
                    categoryId: $validatedRequest['category_id'],
                    name: $validatedRequest['name'],
                    slug: $validatedRequest['slug'],
                    isTaxable: $validatedRequest['is_taxable'],
                    vatRate: $validatedRequest['vat_rate'],
                    isPriceIncludeVat: $validatedRequest['is_price_include_vat'],
                    remarks: $validatedRequest['remarks'] ?? null,
                    status: $validatedRequest['status'],
                    unitId: $validatedRequest['unit_id'],
                    price: $validatedRequest['price'],
                    point: $validatedRequest['point'],
                )
            );

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(Product $product)
    {
        $result = false;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            $result = $this->productActions->delete($product);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
