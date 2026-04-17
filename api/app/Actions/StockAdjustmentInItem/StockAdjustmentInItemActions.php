<?php

namespace App\Actions\StockAdjustmentInItem;

use App\Actions\StockAdjustmentInItemSerial\StockAdjustmentInItemSerialActions;
use App\Actions\StockTransaction\StockTransactionActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\StockAdjustmentInItemCreateDTO;
use App\DTOs\StockAdjustmentInItemSerialCreateDTO;
use App\DTOs\StockAdjustmentInItemSerialUpdateDTO;
use App\DTOs\StockAdjustmentInItemUpdateDTO;
use App\DTOs\StockTransactionCreateDTO;
use App\DTOs\StockTransactionUpdateDTO;
use App\Helpers\TimezoneHelper;
use App\Models\StockAdjustmentInItem;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class StockAdjustmentInItemActions
{
    use CacheHelper;
    use LoggerHelper;

    private const LIST_EAGER_LOADS = [
        'company',
        'branch',
        'stockAdjustment.category',
        'stockAdjustment.inWarehouse',
        'stockAdjustment.outWarehouse',
        'productUnit.unit',
        'productUnit.product.category',
        'productUnit.product.brand',
        'productUnit.product.baseProductUnit.unit',
        'productUnit.product.images',
        'productUnit.product.mainImage',
        'serials',
    ];

    private $stockAdjustmentInItemSerialActions;

    private $stockTransactionActions;

    public function __construct(
        StockTransactionActions $stockTransactionActions,
        StockAdjustmentInItemSerialActions $stockAdjustmentInItemSerialActions,
    ) {
        $this->stockTransactionActions = $stockTransactionActions;
        $this->stockAdjustmentInItemSerialActions = $stockAdjustmentInItemSerialActions;
    }

    public function readAny(
        bool $withTrashed,
        int $companyId,
        ?int $branchId,
        ?string $search,

        ?string $stockAdjustmentCode,
        ?string $stockAdjustmentStartDate,
        ?string $stockAdjustmentEndDate,
        ?int $stockAdjustmentCategoryId,
        ?int $stockAdjustmentInWarehouseId,
        ?int $stockAdjustmentOutWarehouseId,
        ?string $productUnitCode,
        ?string $productUnitProductName,
        ?int $productUnitProductCategoryId,
        ?int $productUnitProductBrandId,

        ?ExecuteDTO $execute
    ) {
        $query = StockAdjustmentInItem::select('stock_adjustment_in_items.*')
            ->with(self::LIST_EAGER_LOADS)
            ->join('companies', 'companies.id', '=', 'stock_adjustment_in_items.company_id')
            ->join('stock_adjustments', 'stock_adjustments.id', '=', 'stock_adjustment_in_items.stock_adjustment_id')
            ->join('product_units', 'product_units.id', '=', 'stock_adjustment_in_items.product_unit_id')
            ->join('products', 'products.id', '=', 'product_units.product_id')
            ->join('product_categories', 'product_categories.id', '=', 'products.category_id')
            ->leftJoin('brands', 'brands.id', '=', 'products.brand_id')
            ->whereCompanyId('stock_adjustment_in_items', $companyId)
            ->whereBranchId('stock_adjustment_in_items', $branchId)
            ->withTrashed();

        $query->where(function ($query) use (
            $withTrashed,
            $search,
            $stockAdjustmentCode,
            $stockAdjustmentStartDate,
            $stockAdjustmentEndDate,
            $stockAdjustmentCategoryId,
            $stockAdjustmentInWarehouseId,
            $stockAdjustmentOutWarehouseId,
            $productUnitCode,
            $productUnitProductName,
            $productUnitProductCategoryId,
            $productUnitProductBrandId,
        ) {
            $query->withoutTrashed();
            if ($withTrashed) $query->withTrashed();

            if ($search) {
                $query->search($search);
            }

            $stockAdjustmentStartDateUtc = $stockAdjustmentStartDate ? TimezoneHelper::convertToUTC($stockAdjustmentStartDate) : null;
            if ($stockAdjustmentStartDateUtc) {
                $query->where('stock_adjustments.date', '>=', $stockAdjustmentStartDateUtc);
            }

            $stockAdjustmentEndDateUtc = $stockAdjustmentEndDate ? TimezoneHelper::convertToUTC($stockAdjustmentEndDate) : null;
            if ($stockAdjustmentEndDateUtc) {
                $query->where('stock_adjustments.date', '<=', $stockAdjustmentEndDateUtc);
            }

            if ($stockAdjustmentCode) {
                $query->where('stock_adjustments.code', $stockAdjustmentCode);
            }

            if ($stockAdjustmentCategoryId) {
                $query->where('stock_adjustments.category_id', $stockAdjustmentCategoryId);
            }

            if ($stockAdjustmentInWarehouseId) {
                $query->where('stock_adjustments.in_warehouse_id', $stockAdjustmentInWarehouseId);
            }

            if ($stockAdjustmentOutWarehouseId) {
                $query->where('stock_adjustments.out_warehouse_id', $stockAdjustmentOutWarehouseId);
            }

            if ($productUnitCode) {
                $query->where('product_units.code', 'like', '%'.$productUnitCode.'%');
            }

            if ($productUnitProductName) {
                $query->where('products.name', 'like', '%'.$productUnitProductName.'%');
            }

            if ($productUnitProductCategoryId) {
                $query->where('products.category_id', $productUnitProductCategoryId);
            }

            if ($productUnitProductBrandId) {
                $query->where('products.brand_id', $productUnitProductBrandId);
            }
        });

        $query->orderBy('stock_adjustments.date', 'desc')
            ->orderBy('stock_adjustment_in_items.id', 'asc');

        if ($execute) {
            $timer_start = microtime(true);
            $recordsCount = 0;

            try {
                $cacheParams = [
                    $withTrashed ? 'true' : 'false',
                    $companyId,
                    $branchId ?? '[null]',
                    empty($search) ? '[empty]' : $search,
                    empty($stockAdjustmentCode) ? '[empty]' : $stockAdjustmentCode,
                    $stockAdjustmentStartDate ?? '[null]',
                    $stockAdjustmentEndDate ?? '[null]',
                    $stockAdjustmentCategoryId ?? '[null]',
                    $stockAdjustmentInWarehouseId ?? '[null]',
                    $stockAdjustmentOutWarehouseId ?? '[null]',
                    empty($productUnitCode) ? '[empty]' : $productUnitCode,
                    empty($productUnitProductName) ? '[empty]' : $productUnitProductName,
                    $productUnitProductCategoryId ?? '[null]',
                    $productUnitProductBrandId ?? '[null]',
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ];

                $cacheKey = 'read_any_stock_adjustment_in_item_'.implode('_', $cacheParams);

                if ($execute->useCache) {
                    $cacheResult = $this->readFromCache($cacheKey);
                    if ($cacheResult !== Config::get('dcslab.ERROR_RETURN_VALUE')) {
                        return $cacheResult;
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

    public function read(StockAdjustmentInItem $stockAdjustmentInItem): StockAdjustmentInItem
    {
        return $stockAdjustmentInItem->load(self::LIST_EAGER_LOADS);
    }

    public function create(StockAdjustmentInItemCreateDTO $data): StockAdjustmentInItem
    {
        $timer_start = microtime(true);

        try {
            $stockAdjustmentInItem = new StockAdjustmentInItem();
            $stockAdjustmentInItem->company_id = $data->companyId;
            $stockAdjustmentInItem->branch_id = $data->branchId;
            $stockAdjustmentInItem->stock_adjustment_id = $data->stockAdjustmentId;
            $stockAdjustmentInItem->qty = $data->qty;
            $stockAdjustmentInItem->product_unit_id = $data->productUnitId;
            $stockAdjustmentInItem->product_unit_conversion_value = $data->productUnitConversionValue;
            $stockAdjustmentInItem->product_unit_qty_base = $data->qty * $data->productUnitConversionValue;
            $stockAdjustmentInItem->product_unit_cogs = $data->productUnitCogs;
            $stockAdjustmentInItem->product_unit_total_cogs = $data->qty * $data->productUnitCogs;
            $stockAdjustmentInItem->product_unit_base_unit_cogs = $stockAdjustmentInItem->product_unit_total_cogs / $stockAdjustmentInItem->product_unit_qty_base;
            $stockAdjustmentInItem->remarks = $data->remarks;
            $stockAdjustmentInItem->save();

            $this->stockTransactionActions->create(
                data: StockTransactionCreateDTO::fromStockAdjustmentInItem($stockAdjustmentInItem)
            );

            foreach ($data->serials as $serial) {
                $dto = StockAdjustmentInItemSerialCreateDTO::fromStockAdjustmentInItem($stockAdjustmentInItem, $serial['serial']);
                $this->stockAdjustmentInItemSerialActions->create($dto);
            }

            $this->flushCache();

            return $stockAdjustmentInItem;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(StockAdjustmentInItem $stockAdjustmentInItem, StockAdjustmentInItemUpdateDTO $data): StockAdjustmentInItem
    {
        $timer_start = microtime(true);

        try {
            $stockAdjustmentInItem->qty = $data->qty;
            $stockAdjustmentInItem->product_unit_id = $data->productUnitId;
            $stockAdjustmentInItem->product_unit_conversion_value = $data->productUnitConversionValue;
            $stockAdjustmentInItem->product_unit_qty_base = $data->qty * $data->productUnitConversionValue;
            $stockAdjustmentInItem->product_unit_cogs = $data->productUnitCogs;
            $stockAdjustmentInItem->product_unit_total_cogs = $data->qty * $data->productUnitCogs;
            $stockAdjustmentInItem->product_unit_base_unit_cogs = $stockAdjustmentInItem->product_unit_total_cogs / $stockAdjustmentInItem->product_unit_qty_base;
            $stockAdjustmentInItem->remarks = $data->remarks;
            $stockAdjustmentInItem->save();

            $stockTransaction = $stockAdjustmentInItem->stockTransaction;
            if (! $stockTransaction) {
                $dto = StockTransactionCreateDTO::fromStockAdjustmentInItem($stockAdjustmentInItem);
                $stockTransaction = $this->stockTransactionActions->create($dto);
            } else {
                $dto = StockTransactionUpdateDTO::fromStockAdjustmentInItem($stockAdjustmentInItem);
                $this->stockTransactionActions->update($stockTransaction, $dto);
            }

            foreach ($data->deleteSerialIds as $deleteId) {
                $stockAdjustmentInItemSerial = $stockAdjustmentInItem->serials()->findOrFail($deleteId);
                $this->stockAdjustmentInItemSerialActions->delete($stockAdjustmentInItemSerial);
            }

            foreach ($data->serials as $serial) {
                if ($serial['id']) {
                    $stockAdjustmentInItemSerial = $stockAdjustmentInItem->serials()->findOrFail($serial['id']);
                    $dto = StockAdjustmentInItemSerialUpdateDTO::fromStockAdjustmentInItemSerial($stockAdjustmentInItemSerial, $serial['serial']);

                    $this->stockAdjustmentInItemSerialActions->update($stockAdjustmentInItemSerial, $dto);
                } else {
                    $dto = StockAdjustmentInItemSerialCreateDTO::fromStockAdjustmentInItem($stockAdjustmentInItem, $serial['serial']);
                    $this->stockAdjustmentInItemSerialActions->create($dto);
                }
            }

            $this->flushCache();

            return $stockAdjustmentInItem;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(StockAdjustmentInItem $stockAdjustmentInItem): bool
    {
        $timer_start = microtime(true);

        try {
            $stockTransaction = $stockAdjustmentInItem->stockTransaction;
            if ($stockTransaction) {
                $this->stockTransactionActions->delete($stockTransaction);
            }

            $serials = $stockAdjustmentInItem->serials;
            foreach ($serials as $serial) {
                $this->stockAdjustmentInItemSerialActions->delete($serial);
            }

            $result = $stockAdjustmentInItem->delete();

            $this->flushCache();

            return $result;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }
}
