<?php

namespace App\Actions\Product;

use App\DTOs\ExecuteDTO;
use App\DTOs\ProductWithRemainingStockDTO;
use App\Enums\ProductStockFilterEnum;
use App\Enums\ProductTypeEnum;
use App\Enums\SortDirectionEnum;
use App\Helpers\TimezoneHelper;
use App\Models\Product;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class ProductActions
{
    use CacheHelper;
    use LoggerHelper;

    public function __construct()
    {
    }

    public function readAny(
        bool $withTrashed,
        int $companyId,
        ?string $search,

        ?int $categoryId,
        ?int $brandId,
        ?bool $isPriceIncludeVat,
        ?bool $isUseSerialNumber,
        ?bool $isExpirable,
        ?int $type,
        ?int $status,
        ?int $includeId,
        ?ProductWithRemainingStockDTO $withRemainingStock,

        ?ExecuteDTO $execute
    ) {
        $query = Product::select('products.*')->withTrashed();
        $query->with([
            'company',
            'category',
            'brand',
            'productUnits.unit',
            'images',
        ]);

        $query->join('product_categories', 'products.category_id', '=', 'product_categories.id');
        $query->leftJoin('brands', 'products.brand_id', '=', 'brands.id');

        $query->whereCompanyId('products', $companyId);

        if ($withRemainingStock) {
            $endDate = $withRemainingStock->endDate ? TimezoneHelper::convertToUTC($withRemainingStock->endDate) : null;

            $query->withRemainingStock(
                endDate: $endDate,
                warehouseId: $withRemainingStock->warehouseId,
            );
        }

        $query->where(function ($query) use (
            $withTrashed,
            $search,
            $categoryId,
            $brandId,
            $isPriceIncludeVat,
            $isUseSerialNumber,
            $isExpirable,
            $type,
            $status,
            $includeId,
            $withRemainingStock,
        ) {
            $query->where(function ($query) use (
                $withTrashed,
                $search,
                $categoryId,
                $brandId,
                $isPriceIncludeVat,
                $isUseSerialNumber,
                $isExpirable,
                $type,
                $status,
                $withRemainingStock,
            ) {
                $query->withoutTrashed();
                if ($withTrashed) {
                    $query->withTrashed();
                }

                if ($search) {
                    $query->search($search);
                }

                if ($categoryId) {
                    $query->where('products.category_id', $categoryId);
                }

                if ($brandId) {
                    $query->where('products.brand_id', $brandId);
                }

                if (! is_null($isPriceIncludeVat)) {
                    $query->where('products.is_price_include_vat', $isPriceIncludeVat);
                }

                if (! is_null($isUseSerialNumber)) {
                    $query->where('products.is_use_serial_number', $isUseSerialNumber);
                }

                if (! is_null($isExpirable)) {
                    $query->where('products.is_expirable', $isExpirable);
                }

                if (! is_null($type)) {
                    $query->where('products.type', $type);
                }

                if (! is_null($status)) {
                    $query->where('products.status', $status);
                }

                if ($withRemainingStock) {
                    $query->where(function ($query) use ($withRemainingStock) {
                        if ($withRemainingStock->stockFilter !== null) {
                            $stockFilter = $withRemainingStock->stockFilter;

                            if ($stockFilter === ProductStockFilterEnum::HAS_STOCK->value) {
                                $query->whereRaw('COALESCE(products_with_remaining_stock.remaining_stock, 0) > 0');
                            } elseif ($stockFilter === ProductStockFilterEnum::EMPTY->value) {
                                $query->whereRaw('COALESCE(products_with_remaining_stock.remaining_stock, 0) = 0');
                            } elseif ($stockFilter === ProductStockFilterEnum::VALID->value) {
                                $query->whereRaw('COALESCE(products_with_remaining_stock.remaining_stock, 0) >= 0');
                            } elseif ($stockFilter === ProductStockFilterEnum::INVALID->value) {
                                $query->whereRaw('COALESCE(products_with_remaining_stock.remaining_stock, 0) < 0');
                            }
                        }

                        if ($withRemainingStock->lessThan !== null) {
                            $query->whereRaw(
                                'COALESCE(products_with_remaining_stock.remaining_stock, 0) < ?',
                                [$withRemainingStock->lessThan]
                            );
                        }

                        if ($withRemainingStock->greaterThan !== null) {
                            $query->whereRaw(
                                'COALESCE(products_with_remaining_stock.remaining_stock, 0) > ?',
                                [$withRemainingStock->greaterThan]
                            );
                        }
                    });

                    if ($withRemainingStock->includeServiceProducts === true) {
                        $query->orWhere('products.type', ProductTypeEnum::SERVICE->value);
                    }
                }
            });

            if ($includeId) {
                $query->orWhere('products.id', $includeId);
            }
        });

        if ($includeId) {
            $query->orderByRaw('FIELD(products.id, '.$includeId.') desc');
        }

        if ($withRemainingStock?->sortByRemainingStock) {
            $directionEnum = SortDirectionEnum::tryFrom(strtolower($withRemainingStock->sortByRemainingStock)) ?? SortDirectionEnum::ASC;
            $query->orderByRaw('COALESCE(products_with_remaining_stock.remaining_stock, 0) '.$directionEnum->value);
        }

        $query->orderBy('product_categories.name', 'asc');
        $query->orderBy('brands.name', 'asc');
        $query->orderBy('products.status', 'desc');
        $query->orderBy('products.name', 'asc');

        if ($execute) {
            $timer_start = microtime(true);
            $recordsCount = 0;

            try {
                $cacheParams = [
                    $withTrashed ? 'true' : 'false',
                    $companyId,
                    empty($search) ? '[empty]' : $search,
                    $categoryId ?? '[null]',
                    $brandId ?? '[null]',
                    is_null($isPriceIncludeVat) ? '[null]' : ($isPriceIncludeVat ? 'true' : 'false'),
                    is_null($isUseSerialNumber) ? '[null]' : ($isUseSerialNumber ? 'true' : 'false'),
                    is_null($isExpirable) ? '[null]' : ($isExpirable ? 'true' : 'false'),
                    $type ?? '[null]',
                    $status ?? '[null]',
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

    public function read(Product $product): Product
    {
        return $product->load('company', 'category', 'brand', 'productUnits.unit', 'images');
    }

    public function delete(Product $product): bool
    {
        $timer_start = microtime(true);

        $retval = false;

        try {
            $retval = $product->delete();

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
}
