<?php

namespace App\Actions\StockAdjustmentOutItem;

use App\Actions\StockAdjustmentOutItemSerial\StockAdjustmentOutItemSerialActions;
use App\Actions\StockTransaction\StockTransactionActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\StockAdjustmentOutItemCreateDTO;
use App\DTOs\StockAdjustmentOutItemSerialCreateDTO;
use App\DTOs\StockAdjustmentOutItemSerialUpdateDTO;
use App\DTOs\StockAdjustmentOutItemUpdateDTO;
use App\DTOs\StockTransactionCreateDTO;
use App\DTOs\StockTransactionUpdateDTO;
use App\Helpers\TimezoneHelper;
use App\Models\StockAdjustmentOutItem;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class StockAdjustmentOutItemActions
{
    use CacheHelper;
    use LoggerHelper;

    private $stockAdjustmentOutItemSerialActions;

    private $stockTransactionActions;

    public function __construct(
        StockTransactionActions $stockTransactionActions,
        StockAdjustmentOutItemSerialActions $stockAdjustmentOutItemSerialActions,
    ) {
        $this->stockTransactionActions = $stockTransactionActions;
        $this->stockAdjustmentOutItemSerialActions = $stockAdjustmentOutItemSerialActions;
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
        ?string $stockAdjustmentProductUnitCode,
        ?string $stockAdjustmentProductUnitProductName,
        ?int $stockAdjustmentProductUnitProductCategoryId,
        ?int $stockAdjustmentProductUnitProductBrandId,

        ?ExecuteDTO $execute
    ) {
        $query = StockAdjustmentOutItem::select('stock_adjustment_out_items.*')
            ->with([
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
                'serials',
            ])
            ->join('companies', 'companies.id', '=', 'stock_adjustment_out_items.company_id')
            ->join('stock_adjustments', 'stock_adjustments.id', '=', 'stock_adjustment_out_items.stock_adjustment_id')
            ->join('product_units', 'product_units.id', '=', 'stock_adjustment_out_items.product_unit_id')
            ->join('products', 'products.id', '=', 'product_units.product_id')
            ->join('product_categories', 'product_categories.id', '=', 'products.category_id')
            ->leftJoin('brands', 'brands.id', '=', 'products.brand_id')
            ->whereCompanyId('stock_adjustment_out_items', $companyId)
            ->whereBranchId('stock_adjustment_out_items', $branchId)
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
            $stockAdjustmentProductUnitCode,
            $stockAdjustmentProductUnitProductName,
            $stockAdjustmentProductUnitProductCategoryId,
            $stockAdjustmentProductUnitProductBrandId,
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

            if ($stockAdjustmentProductUnitCode) {
                $query->where('product_units.code', 'like', '%'.$stockAdjustmentProductUnitCode.'%');
            }

            if ($stockAdjustmentProductUnitProductName) {
                $query->where('products.name', 'like', '%'.$stockAdjustmentProductUnitProductName.'%');
            }

            if ($stockAdjustmentProductUnitProductCategoryId) {
                $query->where('products.category_id', $stockAdjustmentProductUnitProductCategoryId);
            }

            if ($stockAdjustmentProductUnitProductBrandId) {
                $query->where('products.brand_id', $stockAdjustmentProductUnitProductBrandId);
            }
        });

        $query->orderBy('stock_adjustments.date', 'desc')
            ->orderBy('stock_adjustment_out_items.id', 'asc');

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
                    empty($stockAdjustmentProductUnitCode) ? '[empty]' : $stockAdjustmentProductUnitCode,
                    empty($stockAdjustmentProductUnitProductName) ? '[empty]' : $stockAdjustmentProductUnitProductName,
                    $stockAdjustmentProductUnitProductCategoryId ?? '[null]',
                    $stockAdjustmentProductUnitProductBrandId ?? '[null]',
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ];

                $cacheKey = 'read_any_stock_adjustment_out_item_'.implode('_', $cacheParams);

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

    public function read(StockAdjustmentOutItem $stockAdjustmentOutItem): StockAdjustmentOutItem
    {
        return $stockAdjustmentOutItem->load([
            'company',
            'branch',
            'stockAdjustment',
            'productUnit.unit',
            'productUnit.product.category',
            'productUnit.product.brand',
            'productUnit.product.images',
            'serials',
        ]);
    }

    public function create(StockAdjustmentOutItemCreateDTO $data): StockAdjustmentOutItem
    {
        $timer_start = microtime(true);

        try {
            $stockAdjustmentOutItem = new StockAdjustmentOutItem();
            $stockAdjustmentOutItem->company_id = $data->companyId;
            $stockAdjustmentOutItem->branch_id = $data->branchId;
            $stockAdjustmentOutItem->stock_adjustment_id = $data->stockAdjustmentId;
            $stockAdjustmentOutItem->qty = $data->qty;
            $stockAdjustmentOutItem->product_unit_id = $data->productUnitId;
            $stockAdjustmentOutItem->product_unit_conversion_value = $data->productUnitConversionValue;
            $stockAdjustmentOutItem->product_unit_qty_base = $data->qty * $data->productUnitConversionValue;
            $stockAdjustmentOutItem->remarks = $data->remarks;
            $stockAdjustmentOutItem->save();

            $this->stockTransactionActions->create(
                data: StockTransactionCreateDTO::fromStockAdjustmentOutItem($stockAdjustmentOutItem)
            );

            foreach ($data->serials as $serial) {
                $dto = StockAdjustmentOutItemSerialCreateDTO::fromStockAdjustmentOutItem($stockAdjustmentOutItem, $serial['serial']);
                $this->stockAdjustmentOutItemSerialActions->create($dto);
            }

            $this->flushCache();

            return $stockAdjustmentOutItem;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(StockAdjustmentOutItem $stockAdjustmentOutItem, StockAdjustmentOutItemUpdateDTO $data): StockAdjustmentOutItem
    {
        $timer_start = microtime(true);

        try {
            $stockAdjustmentOutItem->qty = $data->qty;
            $stockAdjustmentOutItem->product_unit_id = $data->productUnitId;
            $stockAdjustmentOutItem->product_unit_conversion_value = $data->productUnitConversionValue;
            $stockAdjustmentOutItem->product_unit_qty_base = $stockAdjustmentOutItem->qty * $stockAdjustmentOutItem->product_unit_conversion_value;
            $stockAdjustmentOutItem->remarks = $data->remarks;
            $stockAdjustmentOutItem->save();

            $stockTransaction = $stockAdjustmentOutItem->stockTransaction;
            if (! $stockTransaction) {
                $dto = StockTransactionCreateDTO::fromStockAdjustmentOutItem($stockAdjustmentOutItem);
                $stockTransaction = $this->stockTransactionActions->create($dto);
            } else {
                $dto = StockTransactionUpdateDTO::fromStockAdjustmentOutItem($stockAdjustmentOutItem);
                $this->stockTransactionActions->update($stockTransaction, $dto);
            }

            foreach ($data->deleteSerialIds as $deleteId) {
                $stockAdjustmentOutItemSerial = $stockAdjustmentOutItem->serials()->findOrFail($deleteId);
                $this->stockAdjustmentOutItemSerialActions->delete($stockAdjustmentOutItemSerial);
            }

            foreach ($data->serials as $serial) {
                if ($serial['id']) {
                    $stockAdjustmentOutItemSerial = $stockAdjustmentOutItem->serials()->findOrFail($serial['id']);
                    $dto = StockAdjustmentOutItemSerialUpdateDTO::fromStockAdjustmentOutItemSerial($stockAdjustmentOutItemSerial, $serial['serial']);
                    $this->stockAdjustmentOutItemSerialActions->update($stockAdjustmentOutItemSerial, $dto);
                } else {
                    $dto = StockAdjustmentOutItemSerialCreateDTO::fromStockAdjustmentOutItem($stockAdjustmentOutItem, $serial['serial']);
                    $this->stockAdjustmentOutItemSerialActions->create($dto);
                }
            }

            $this->flushCache();

            return $stockAdjustmentOutItem;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(StockAdjustmentOutItem $stockAdjustmentOutItem): bool
    {
        $timer_start = microtime(true);

        try {
            $stockTransaction = $stockAdjustmentOutItem->stockTransaction;
            if ($stockTransaction) {
                $this->stockTransactionActions->delete($stockTransaction);
            }

            $serials = $stockAdjustmentOutItem->serials;
            foreach ($serials as $serial) {
                $this->stockAdjustmentOutItemSerialActions->delete($serial);
            }

            $result = $stockAdjustmentOutItem->delete();

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
