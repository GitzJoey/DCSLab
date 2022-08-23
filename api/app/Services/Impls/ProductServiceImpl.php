<?php

namespace App\Services\Impls;

use App\Actions\RandomGenerator;
use App\Enums\ProductCategory;
use App\Enums\ProductType;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Services\ProductService;
use App\Traits\CacheHelper;
use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductServiceImpl implements ProductService
{
    use CacheHelper;

    public function __construct()
    {
    }

    public function create(
        array $productArr,
        array $productUnitsArr
    ): Product {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $product = new Product();
            $product->company_id = $productArr['company_id'];
            $product->code = $productArr['code'];
            $product->product_group_id = $productArr['product_group_id'];
            $product->brand_id = $productArr['brand_id'];
            $product->name = $productArr['name'];
            $product->taxable_supply = $productArr['taxable_supply'];
            $product->standard_rated_supply = $productArr['standard_rated_supply'];
            $product->price_include_vat = $productArr['price_include_vat'];
            $product->remarks = $productArr['remarks'];
            $product->point = $productArr['point'];
            $product->use_serial_number = $productArr['use_serial_number'];
            $product->has_expiry_date = $productArr['has_expiry_date'];
            $product->product_type = $productArr['product_type'];
            $product->status = $productArr['status'];

            $product->save();

            $pu = [];
            foreach ($productUnitsArr as $product_unit) {
                array_push($pu, new ProductUnit([
                    'company_id' => $product->company_id,
                    'product_id' => $product->id,
                    'code' => $product_unit['code'],
                    'unit_id' => $product_unit['unit_id'],
                    'conversion_value' => $product_unit['conversion_value'],
                    'is_base' => $product_unit['is_base'],
                    'is_primary_unit' => $product_unit['is_primary_unit'],
                    'remarks' => $product_unit['remarks'],
                ]));
            }

            if (!empty($pu) && $this->checkUniqueCodeInArray($pu)) {
                $product->productUnits()->saveMany($pu);
            }

            DB::commit();

            $this->flushCache();

            return $product;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '' : auth()->id()).'] '.__METHOD__.$e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '' : auth()->id()).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }

    public function list(
        int $companyId,
        int $productCategory,
        string $search = '',
        bool $paginate = true,
        int $page = 1,
        ?int $perPage = 10,
        array $with = [],
        bool $withTrashed = false,
        bool $useCache = true
    ): Paginator|Collection {
        $timer_start = microtime(true);

        try {
            $cacheKey = '';
            if ($useCache) {
                $cacheKey = 'read_'.$companyId.'-'.$productCategory.'-'.(empty($search) ? '[empty]' : $search).'-'.$paginate.'-'.$page.'-'.$perPage;
                $cacheResult = $this->readFromCache($cacheKey);

                if (!is_null($cacheResult)) {
                    return $cacheResult;
                }
            }

            $result = null;

            if (!$companyId) {
                return null;
            }

            $product = count($with) != 0 ? Product::with($with) : Product::with('company', 'productGroup', 'brand', 'productUnits.unit');
            $product = $product->whereCompanyId($companyId);

            switch ($productCategory) {
                case ProductCategory::PRODUCTS->value:
                    $product = $product->where('product_type', '<>', ProductType::SERVICE->value);
                    break;
                case ProductCategory::SERVICES->value:
                    $product = $product->where('product_type', '=', ProductType::SERVICE->value);
                    break;
                case ProductCategory::PRODUCTS_AND_SERVICES->value:
                default:
                    break;
            }

            if ($withTrashed)
            $product = $product->withTrashed();

            if (empty($search)) {
                $product = $product->latest();
            } else {
                $product = $product->where('name', 'like', '%'.$search.'%')->latest();
            }

            if ($paginate) {
                $perPage = is_numeric($perPage) ? $perPage : Config::get('dcslab.PAGINATION_LIMIT');
                $result = $product->paginate(perPage: abs($perPage), page: abs($page));
            } else {
                $result = $product->get();
            }

            if ($useCache) {
                $this->saveToCache($cacheKey, $result);
            }

            return $result;
        } catch (Exception $e) {
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '' : auth()->id()).'] '.__METHOD__.$e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '' : auth()->id()).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)'.($useCache ? ' (C)' : ' (DB)'));
        }
    }

    public function read(Product $product): Product
    {
        $brand = $product->brand_id;
        if ($brand) {
            $result = Product::with('productGroup', 'brand', 'productUnits.unit')->where('id', '=', $product->id)->first();
        } else {
            $result = Product::with('productGroup', 'productUnits.unit')->where('id', '=', $product->id)->first();
        }

        return $result;
    }

    public function update(
        Product $product,
        array $productArr,
        array $productUnitsArr
    ): Product {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $product->code = $productArr['code'];
            $product->product_group_id = $productArr['product_group_id'];
            $product->brand_id = $productArr['brand_id'];
            $product->name = $productArr['name'];
            $product->taxable_supply = $productArr['taxable_supply'];
            $product->standard_rated_supply = $productArr['standard_rated_supply'];
            $product->price_include_vat = $productArr['price_include_vat'];
            $product->remarks = $productArr['remarks'];
            $product->point = $productArr['point'];
            $product->use_serial_number = $productArr['use_serial_number'];
            $product->has_expiry_date = $productArr['has_expiry_date'];
            $product->product_type = $productArr['product_type'];
            $product->status = $productArr['status'];
            $product->save();

            $pu = [];
            foreach ($productUnitsArr as $product_unit) {
                array_push($pu, [
                    'id' => $product_unit['id'],
                    'company_id' => $product->company_id,
                    'product_id' => $product->id,
                    'code' => $product_unit['code'],
                    'unit_id' => $product_unit['unit_id'],
                    'conversion_value' => $product_unit['conversion_value'],
                    'is_base' => $product_unit['is_base'],
                    'is_primary_unit' => $product_unit['is_primary_unit'],
                    'remarks' => $product_unit['remarks'],
                ]);
            }

            $puIds = [];
            foreach ($pu as $puId) {
                array_push($puIds, $puId['id']);
            }

            $puIdsOld = $product->productUnits()->pluck('id')->ToArray();

            $deletedProductUnitIds = [];
            $deletedProductUnitIds = array_diff($puIdsOld, $puIds);

            foreach ($deletedProductUnitIds as $deletedProductUnitId) {
                $productUnit = $product->productUnits()->where('id', $deletedProductUnitId);
                $productUnit->delete();
            }

            if (!empty($pu) && $this->checkUniqueCodeInArray($pu)) {
                ProductUnit::upsert(
                    $pu, 
                    ['id'], 
                    [
                        'company_id',
                        'product_id',
                        'code',
                        'unit_id',
                        'is_base',
                        'conversion_value',
                        'is_primary_unit',
                        'remarks',
                    ]
                );
            }

            DB::commit();

            $this->flushCache();

            return $product->refresh();
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '' : auth()->id()).'] '.__METHOD__.$e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '' : auth()->id()).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }

    public function delete(Product $product): bool
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        $retval = false;

        try {
            $product->productUnits()->delete();

            $retval = $product->delete();

            DB::commit();

            $this->flushCache();

            return $retval;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '' : auth()->id()).'] '.__METHOD__.$e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '' : auth()->id()).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }

    public function generateUniqueCodeForProduct(): string
    {
        $rand = new RandomGenerator();
        $code = $rand->generateAlphaNumeric(3).$rand->generateFixedLengthNumber(3);

        return $code;
    }

    public function generateUniqueCodeForProductUnits(): string
    {
        $rand = new RandomGenerator();
        $code = $rand->generateAlphaNumeric(3).$rand->generateFixedLengthNumber(3);

        return $code;
    }

    public function isUniqueCodeForProduct(string $code, int $companyId, ?int $exceptId = null): bool
    {
        $result = Product::whereCompanyId($companyId)->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0 ? true : false;
    }

    public function isUniqueCodeForProductUnits(string $code, int $companyId, ?int $exceptId = null): bool
    {
        $result = ProductUnit::whereCompanyId($companyId)->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0 ? true : false;
    }

    private function checkUniqueCodeInArray(array $productUnits): bool
    {
        $allCodes = Arr::pluck($productUnits, 'code');

        return count($allCodes) == count(array_unique($allCodes));
    }
}
