<?php

namespace App\Actions\StockTransferItem;

use App\Actions\StockTransaction\StockTransactionActions;
use App\Actions\StockTransferItemSerial\StockTransferItemSerialActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\StockTransactionCreateDTO;
use App\DTOs\StockTransactionUpdateDTO;
use App\DTOs\StockTransferItemCreateDTO;
use App\DTOs\StockTransferItemSerialCreateDTO;
use App\DTOs\StockTransferItemSerialUpdateDTO;
use App\DTOs\StockTransferItemUpdateDTO;
use App\Helpers\TimezoneHelper;
use App\Models\Company;
use App\Models\StockTransferItem;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class StockTransferItemActions
{
    use CacheHelper;
    use LoggerHelper;

    private $stockTransferItemSerialActions;

    private $stockTransactionActions;

    public function __construct(
        StockTransferItemSerialActions $stockTransferItemSerialActions,
        StockTransactionActions $stockTransactionActions,
    ) {
        $this->stockTransferItemSerialActions = $stockTransferItemSerialActions;
        $this->stockTransactionActions = $stockTransactionActions;
    }

    public function readAny(
        bool $withTrashed,
        int $companyId,
        ?int $branchId,
        ?string $search,

        ?string $stockTransferCode,
        ?string $stockTransferStartDate,
        ?string $stockTransferEndDate,
        ?int $stockTransferSourceWarehouseId,
        ?int $stockTransferDestinationWarehouseId,
        ?string $productUnitCode,
        ?string $productUnitProductName,
        ?int $productUnitProductCategoryId,
        ?int $productUnitProductBrandId,

        ?ExecuteDTO $execute
    ) {
        $query = StockTransferItem::select('stock_transfer_items.*')
            ->with([
                'company',
                'branch',
                'stockTransfer.sourceWarehouse',
                'stockTransfer.destinationWarehouse',
                'productUnit.unit',
                'productUnit.product.category',
                'productUnit.product.brand',
                'productUnit.product.baseProductUnit.unit',
                'productUnit.product.images',
                'serials',
            ])
            ->join('companies', 'companies.id', '=', 'stock_transfer_items.company_id')
            ->join('stock_transfers', 'stock_transfers.id', '=', 'stock_transfer_items.stock_transfer_id')
            ->join('items', 'items.id', '=', 'stock_transfer_items.product_unit_id')
            ->join('products', 'products.id', '=', 'items.product_id')
            ->join('product_categories', 'product_categories.id', '=', 'products.category_id')
            ->leftJoin('brands', 'brands.id', '=', 'products.brand_id')
            ->whereCompanyId('stock_transfer_items', $companyId)
            ->whereBranchId('stock_transfer_items', $branchId)
            ->withTrashed();

        $query->where(function ($query) use (
            $withTrashed,
            $search,
            $stockTransferCode,
            $stockTransferStartDate,
            $stockTransferEndDate,
            $stockTransferSourceWarehouseId,
            $stockTransferDestinationWarehouseId,
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

            $stockTransferStartDateUtc = $stockTransferStartDate ? TimezoneHelper::convertToUTC($stockTransferStartDate) : null;
            if ($stockTransferStartDateUtc) {
                $query->where('stock_transfers.date', '>=', $stockTransferStartDateUtc);
            }

            $stockTransferEndDateUtc = $stockTransferEndDate ? TimezoneHelper::convertToUTC($stockTransferEndDate) : null;
            if ($stockTransferEndDateUtc) {
                $query->where('stock_transfers.date', '<=', $stockTransferEndDateUtc);
            }

            if ($stockTransferCode) {
                $query->where('stock_transfers.code', $stockTransferCode);
            }

            if ($stockTransferSourceWarehouseId) {
                $query->where('stock_transfers.source_warehouse_id', $stockTransferSourceWarehouseId);
            }

            if ($stockTransferDestinationWarehouseId) {
                $query->where('stock_transfers.destination_warehouse_id', $stockTransferDestinationWarehouseId);
            }

            if ($productUnitCode) {
                $query->where('items.code', 'like', '%'.$productUnitCode.'%');
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

        $query->orderBy('stock_transfers.date', 'desc')
            ->orderBy('stock_transfer_items.id', 'asc');

        if ($execute) {
            $timer_start = microtime(true);
            $recordsCount = 0;

            try {
                $cacheParams = [
                    $withTrashed ? 'true' : 'false',
                    $companyId,
                    $branchId ?? '[null]',
                    empty($search) ? '[empty]' : $search,
                    empty($stockTransferCode) ? '[empty]' : $stockTransferCode,
                    $stockTransferStartDate ?? '[null]',
                    $stockTransferEndDate ?? '[null]',
                    $stockTransferSourceWarehouseId ?? '[null]',
                    $stockTransferDestinationWarehouseId ?? '[null]',
                    empty($productUnitCode) ? '[empty]' : $productUnitCode,
                    empty($productUnitProductName) ? '[empty]' : $productUnitProductName,
                    $productUnitProductCategoryId ?? '[null]',
                    $productUnitProductBrandId ?? '[null]',
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ];
                $cacheKey = 'read_any_stock_transfer_item_'.implode('_', $cacheParams);

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

    public function read(StockTransferItem $stockTransferItem): StockTransferItem
    {
        return $stockTransferItem->load([
            'company',
            'branch',
            'stockTransfer',
            'productUnit.unit',
            'productUnit.product.category',
            'productUnit.product.brand',
            'productUnit.product.images',
            'serials',
        ]);
    }

    public function create(StockTransferItemCreateDTO $data): StockTransferItem
    {
        $timer_start = microtime(true);

        try {
            $stockTransferItem = new StockTransferItem();
            $stockTransferItem->company_id = $data->companyId;
            $stockTransferItem->branch_id = $data->branchId;
            $stockTransferItem->stock_transfer_id = $data->stockTransferId;
            $stockTransferItem->qty = $data->qty;
            $stockTransferItem->product_unit_id = $data->productUnitId;
            $stockTransferItem->product_unit_conversion_value = $data->productUnitConversionValue;
            $stockTransferItem->product_unit_qty_base = $data->qty * $data->productUnitConversionValue;
            $stockTransferItem->remarks = $data->remarks;
            $stockTransferItem->save();

            foreach ($data->serials as $serial) {
                $dto = StockTransferItemSerialCreateDTO::fromStockTransferItem($stockTransferItem, $serial['serial']);
                $this->stockTransferItemSerialActions->create($dto);
            }

            $this->stockTransactionActions->create(
                data: StockTransactionCreateDTO::fromStockTransferItemSource($stockTransferItem)
            );
            $this->stockTransactionActions->create(
                data: StockTransactionCreateDTO::fromStockTransferItemDestination($stockTransferItem)
            );

            $this->flushCache();

            return $stockTransferItem;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(StockTransferItem $stockTransferItem, StockTransferItemUpdateDTO $data): StockTransferItem
    {
        $timer_start = microtime(true);

        try {
            $stockTransferItem->qty = $data->qty;
            $stockTransferItem->product_unit_id = $data->productUnitId;
            $stockTransferItem->product_unit_conversion_value = $data->productUnitConversionValue;
            $stockTransferItem->product_unit_qty_base = $data->qty * $data->productUnitConversionValue;
            $stockTransferItem->remarks = $data->remarks;
            $stockTransferItem->save();

            foreach ($data->deleteSerialIds as $deleteId) {
                $stockTransferItemSerial = $stockTransferItem->serials()->findOrFail($deleteId);
                $this->stockTransferItemSerialActions->delete($stockTransferItemSerial);
            }

            foreach ($data->serials as $serial) {
                if ($serial['id']) {
                    $stockTransferItemSerial = $stockTransferItem->serials()->findOrFail($serial['id']);
                    $dto = StockTransferItemSerialUpdateDTO::fromStockTransferItemSerial($stockTransferItemSerial, $serial['serial']);
                    $this->stockTransferItemSerialActions->update($stockTransferItemSerial, $dto);
                } else {
                    $dto = StockTransferItemSerialCreateDTO::fromStockTransferItem($stockTransferItem, $serial['serial']);
                    $this->stockTransferItemSerialActions->create($dto);
                }
            }

            $stockTransactionSource = $stockTransferItem->sourceStockTransaction;
            if (! $stockTransactionSource) {
                $dto = StockTransactionCreateDTO::fromStockTransferItemSource($stockTransferItem);
                $this->stockTransactionActions->create($dto);
            } else {
                $dto = StockTransactionUpdateDTO::fromStockTransferItemSource($stockTransferItem);
                $this->stockTransactionActions->update($stockTransactionSource, $dto);
            }

            $stockTransactionDestination = $stockTransferItem->destinationStockTransaction;
            if (! $stockTransactionDestination) {
                $dto = StockTransactionCreateDTO::fromStockTransferItemDestination($stockTransferItem);
                $this->stockTransactionActions->create($dto);
            } else {
                $dto = StockTransactionUpdateDTO::fromStockTransferItemDestination($stockTransferItem);
                $this->stockTransactionActions->update($stockTransactionDestination, $dto);
            }

            $this->flushCache();

            return $stockTransferItem->refresh();
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(StockTransferItem $stockTransferItem): bool
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        $retval = false;

        try {
            $stockTransferItemSerials = $stockTransferItem->serials()->get();
            foreach ($stockTransferItemSerials as $stockTransferItemSerial) {
                $this->stockTransferItemSerialActions->delete($stockTransferItemSerial);
            }

            $stockTransactionSource = $stockTransferItem->sourceStockTransaction;
            if ($stockTransactionSource) {
                $this->stockTransactionActions->delete($stockTransactionSource);
            }

            $stockTransactionDestination = $stockTransferItem->destinationStockTransaction;
            if ($stockTransactionDestination) {
                $this->stockTransactionActions->delete($stockTransactionDestination);
            }

            $retval = $stockTransferItem->delete();

            DB::commit();

            $this->flushCache();

            return $retval;
        } catch (Exception $e) {
            DB::rollBack();
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function generateUniqueCode(int $companyId, string $code, ?int $exceptId): string
    {
        if ($code == config('dcslab.KEYWORDS.AUTO')) {
            $company = Company::find($companyId);

            $tryCount = 0;
            do {
                $count = $company->stockTransferItems()->withTrashed()->count() + 1 + $tryCount;
                $code = 'WH'.str_pad($count, 3, '0', STR_PAD_LEFT);
                $tryCount++;
            } while (! $this->isUniqueCode($companyId, $code, $exceptId));

            return $code;
        } else {
            return $code;
        }
    }

    public function isUniqueCode(int $companyId, string $code, ?int $exceptId): bool
    {
        $result = StockTransferItem::whereCompanyId('stock_transfer_items', $companyId)->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0 ? true : false;
    }
}
