<?php

namespace App\Actions\ProductUnit;

use App\DTOs\ExecuteDTO;
use App\DTOs\ProductUnitCreatePhysicalDTO;
use App\DTOs\ProductUnitCreateServiceDTO;
use App\DTOs\ProductUnitUpdatePhysicalDTO;
use App\DTOs\ProductUnitUpdateServiceDTO;
use App\Models\Company;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class ProductUnitActions
{
    use CacheHelper;
    use LoggerHelper;

    public function __construct()
    {
    }

    public function createPhysical(ProductUnitCreatePhysicalDTO $data): ProductUnit
    {
        $timer_start = microtime(true);

        try {
            if ($data->conversionValue === 1) $this->resetBaseUnit($data->companyId, $data->productId);
            if ($data->isPrimaryUnit) $this->resetPrimaryUnit($data->companyId, $data->productId);

            $productUnit = new ProductUnit();
            $productUnit->company_id = $data->companyId;
            $productUnit->product_id = $data->productId;
            $productUnit->code = $this->generateUniqueCode($data->companyId, $data->code, null);
            $productUnit->is_manufacturer_sku = $data->isManufacturerSKU;
            $productUnit->unit_id = $data->unitId;
            $productUnit->price = $data->price;
            $productUnit->is_base = $data->conversionValue === 1;
            $productUnit->conversion_value = $data->conversionValue;
            $productUnit->is_primary_unit = $data->isPrimaryUnit;
            $productUnit->point = $data->point;
            $productUnit->remarks = $data->remarks;
            $productUnit->save();

            $this->flushCache();

            return $productUnit;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function createService(ProductUnitCreateServiceDTO $data): ProductUnit
    {
        $timer_start = microtime(true);

        try {
            $product = Product::findOrFail($data->productId);

            $productUnit = new ProductUnit();
            $productUnit->company_id = $data->companyId;
            $productUnit->product_id = $data->productId;
            $productUnit->code = $product->code;
            $productUnit->is_manufacturer_sku = false;
            $productUnit->unit_id = $data->unitId;
            $productUnit->price = $data->price;
            $productUnit->is_base = true;
            $productUnit->conversion_value = 1;
            $productUnit->is_primary_unit = true;
            $productUnit->point = $data->point;
            $productUnit->remarks = $data->remarks;
            $productUnit->save();

            $this->flushCache();

            return $productUnit;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function readAny(
        bool $withTrashed,

        ?string $search,
        int $companyId,
        ?int $productId,
        ?int $unitId,
        ?bool $isBase,
        ?bool $isPrimaryUnit,
        ?int $includeId,

        ?ExecuteDTO $execute
    ) {
        $query = ProductUnit::with(['company', 'unit', 'product'])->select('product_units.*')
            ->where('product_units.company_id', $companyId)
            ->withTrashed();

        $query->where(function ($query) use ($withTrashed, $search, $productId, $unitId, $isBase, $isPrimaryUnit, $includeId) {
            $query->where(function ($query) use ($withTrashed, $search, $productId, $unitId, $isBase, $isPrimaryUnit) {
                $query->withoutTrashed();
                if ($withTrashed) {
                    $query->withTrashed();
                }

                if ($search) {
                    $query->search($search);
                }

                if ($productId) {
                    $query->where('product_units.product_id', $productId);
                }

                if ($unitId) {
                    $query->where('product_units.unit_id', $unitId);
                }

                if (! is_null($isBase)) {
                    $query->where('product_units.is_base', $isBase);
                }

                if (! is_null($isPrimaryUnit)) {
                    $query->where('product_units.is_primary_unit', $isPrimaryUnit);
                }
            });

            if ($includeId) {
                $query->orWhere('product_units.id', $includeId);
            }
        });

        if ($includeId) {
            $query->orderByRaw('FIELD(product_units.id, '.$includeId.') desc');
        }
        $query->orderBy('product_units.conversion_value', 'asc');

        if ($execute) {
            $timer_start = microtime(true);
            $recordsCount = 0;

            try {
                $cacheParams = [
                    $withTrashed ? 'true' : 'false',
                    empty($search) ? '[empty]' : $search,
                    $companyId,
                    $productId ?? '[null]',
                    $unitId ?? '[null]',
                    is_null($isBase) ? '[null]' : ($isBase ? 'true' : 'false'),
                    is_null($isPrimaryUnit) ? '[null]' : ($isPrimaryUnit ? 'true' : 'false'),
                    $includeId ?? '[null]',
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ];

                $cacheKey = 'readAny_'.implode('-', $cacheParams);

                if ($execute->useCache) {
                    $cacheData = $this->readFromCache($cacheKey);
                    if ($cacheData !== Config::get('dcslab.ERROR_RETURN_VALUE')) {
                        return $cacheData;
                    }
                }

                if ($execute->pagination) {
                    $result = $query->paginate(
                        perPage: $execute->pagination->perPage,
                        columns: ['*'],
                        pageName: 'page',
                        page: $execute->pagination->page
                    );
                } else {
                    if ($execute->get?->limit) {
                        $query->limit($execute->get->limit);
                    }
                    $result = $query->get();
                }

                $recordsCount = $result->count();

                if ($execute->useCache) {
                    $this->saveToCache($cacheKey, $result);
                }

                return $result;
            } catch (Exception $e) {
                $this->loggerDebug(__METHOD__, $e);
                throw $e;
            } finally {
                $execution_time = microtime(true) - $timer_start;
                $this->loggerPerformance(__METHOD__, $execution_time, $recordsCount);
            }
        }

        return $query;
    }

    public function read(ProductUnit $productUnit): ProductUnit
    {
        return $productUnit->load('company', 'unit', 'product');
    }

    public function updatePhysical(ProductUnit $productUnit, ProductUnitUpdatePhysicalDTO $data): ProductUnit
    {
        $timer_start = microtime(true);

        try {
            if ($data->conversionValue === 1) $this->resetBaseUnit($productUnit->company_id, $productUnit->product_id);
            if ($data->isPrimaryUnit)  $this->resetPrimaryUnit($productUnit->company_id, $productUnit->product_id);

            $productUnit->code = $this->generateUniqueCode($productUnit->company_id, $data->code, $productUnit->id);
            $productUnit->is_manufacturer_sku = $data->isManufacturerSku;
            $productUnit->unit_id = $data->unitId;
            $productUnit->price = $data->price;
            $productUnit->is_base = $data->conversionValue === 1;
            $productUnit->conversion_value = $data->conversionValue;
            $productUnit->is_primary_unit = $data->isPrimaryUnit;
            $productUnit->point = $data->point;
            $productUnit->remarks = $data->remarks;
            $productUnit->save();

            $this->flushCache();

            return $productUnit->refresh();
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function updateService(ProductUnit $productUnit, ProductUnitUpdateServiceDTO $data): ProductUnit
    {
        $timer_start = microtime(true);

        try {
            $productUnit->code = $productUnit->product->code;
            $productUnit->is_manufacturer_sku = false;
            $productUnit->unit_id = $data->unitId;
            $productUnit->price = $data->price;
            $productUnit->is_base = true;
            $productUnit->conversion_value = 1;
            $productUnit->is_primary_unit = true;
            $productUnit->point = $data->point;
            $productUnit->remarks = $data->remarks;
            $productUnit->save();

            $this->flushCache();

            return $productUnit->refresh();
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(ProductUnit $productUnit): bool
    {
        $timer_start = microtime(true);

        $retval = false;

        try {
            $retval = $productUnit->delete();

            $this->flushCache();

            return $retval;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function resetBaseUnit(int $companyId, int $productId): void
    {
        ProductUnit::where('company_id', $companyId)
            ->where('product_id', $productId)
            ->update(['is_base' => false]);
    }

    public function resetPrimaryUnit(int $companyId, int $productId): void
    {
        ProductUnit::where('company_id', $companyId)
            ->where('product_id', $productId)
            ->update(['is_primary_unit' => false]);
    }

    public function generateUniqueCode(int $companyId, string $code, ?int $exceptId): string
    {
        if ($code != Config::get('dcslab.KEYWORDS.AUTO')) return $code;

        $company = Company::find($companyId);

        $tryCount = 0;
        do {
            $count = $company->productUnits()->withTrashed()->count() + 1 + $tryCount;
            $code = 'PU'.str_pad($count, 3, '0', STR_PAD_LEFT);
            $tryCount++;
        } while (! $this->isUniqueCode($companyId, $code, $exceptId));

        return $code;
    }

    public function isUniqueCode(int $companyId, string $code, ?int $exceptId): bool
    {
        $company = Company::find($companyId);

        if ($company->productUnits()->count() == 0) {
            return true;
        }

        $query = $company->productUnits()->where('code', '=', $code);
        if ($exceptId) {
            $query->where('product_units.id', '<>', $exceptId);
        }

        return $query->doesntExist();
    }

    public function isUniqueUnit(int $companyId, int $productId, int $unitId, ?int $exceptId): bool
    {
        $query = ProductUnit::where('company_id', $companyId)
            ->where('product_id', $productId)
            ->where('unit_id', $unitId);

        if ($exceptId) {
            $query->where('id', '<>', $exceptId);
        }

        return $query->doesntExist();
    }

    public function isUniqueConversionValue(int $companyId, int $productId, float $conversionValue, ?int $exceptId): bool
    {
        $query = ProductUnit::where('company_id', $companyId)
            ->where('product_id', $productId)
            ->where('conversion_value', $conversionValue);

        if ($exceptId) {
            $query->where('id', '<>', $exceptId);
        }

        return $query->doesntExist();
    }
}
