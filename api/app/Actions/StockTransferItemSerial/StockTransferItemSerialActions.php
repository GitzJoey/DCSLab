<?php

namespace App\Actions\StockTransferItemSerial;

use App\Actions\StockSerialTransaction\StockSerialTransactionActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\StockSerialTransactionCreateDTO;
use App\DTOs\StockSerialTransactionUpdateDTO;
use App\DTOs\StockTransferItemSerialCreateDTO;
use App\DTOs\StockTransferItemSerialUpdateDTO;
use App\Helpers\TimezoneHelper;
use App\Models\StockTransferItemSerial;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class StockTransferItemSerialActions
{
    use CacheHelper;
    use LoggerHelper;

    private const LIST_EAGER_LOADS = [
        'company',
        'branch',
        'stockTransfer.sourceWarehouse',
        'stockTransfer.destinationWarehouse',
        'stockTransferItem.company',
        'stockTransferItem.branch',
        'stockTransferItem.stockTransfer.sourceWarehouse',
        'stockTransferItem.stockTransfer.destinationWarehouse',
        'stockTransferItem.productUnit.unit',
        'stockTransferItem.productUnit.product.category',
        'stockTransferItem.productUnit.product.brand',
        'stockTransferItem.productUnit.product.baseProductUnit.unit',
        'stockTransferItem.productUnit.product.images',
        'stockTransferItem.productUnit.product.mainImage',
    ];

    protected StockSerialTransactionActions $stockSerialTransactionActions;

    public function __construct(StockSerialTransactionActions $stockSerialTransactionActions)
    {
        $this->stockSerialTransactionActions = $stockSerialTransactionActions;
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
        ?string $stockTransferItemCode,
        ?string $stockTransferItemProductName,
        ?int $stockTransferItemProductCategoryId,
        ?int $stockTransferItemProductBrandId,

        ?ExecuteDTO $execute
    ) {
        $query = StockTransferItemSerial::select('stock_transfer_item_serials.*')
            ->with(self::LIST_EAGER_LOADS)
            ->join('companies', 'companies.id', '=', 'stock_transfer_item_serials.company_id')
            ->join('stock_transfers', 'stock_transfers.id', '=', 'stock_transfer_item_serials.stock_transfer_id')
            ->join('stock_transfer_items', 'stock_transfer_items.id', '=', 'stock_transfer_item_serials.stock_transfer_item_id')
            ->join('items', 'items.id', '=', 'stock_transfer_items.product_unit_id')
            ->join('products', 'products.id', '=', 'items.product_id')
            ->join('product_categories', 'product_categories.id', '=', 'products.category_id')
            ->leftJoin('brands', 'brands.id', '=', 'products.brand_id')
            ->whereCompanyId('stock_transfer_item_serials', $companyId)
            ->whereBranchId('stock_transfer_item_serials', $branchId)
            ->withTrashed();

        $query->where(function ($query) use (
            $withTrashed,
            $search,
            $stockTransferCode,
            $stockTransferStartDate,
            $stockTransferEndDate,
            $stockTransferSourceWarehouseId,
            $stockTransferDestinationWarehouseId,
            $stockTransferItemCode,
            $stockTransferItemProductName,
            $stockTransferItemProductCategoryId,
            $stockTransferItemProductBrandId,
        ) {
            $query->withoutTrashed();
            if ($withTrashed) {
                $query->withTrashed();
            }

            if ($search) {
                $query->where('serial', 'like', '%'.$search.'%');
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

            if ($stockTransferItemCode) {
                $query->where('items.code', 'like', '%'.$stockTransferItemCode.'%');
            }

            if ($stockTransferItemProductName) {
                $query->where('products.name', 'like', '%'.$stockTransferItemProductName.'%');
            }

            if ($stockTransferItemProductCategoryId) {
                $query->where('products.category_id', $stockTransferItemProductCategoryId);
            }

            if ($stockTransferItemProductBrandId) {
                $query->where('products.brand_id', $stockTransferItemProductBrandId);
            }
        });

        $query->orderBy('stock_transfers.date', 'desc')
            ->orderBy('stock_transfer_item_serials.id', 'asc');

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
                    empty($stockTransferItemCode) ? '[empty]' : $stockTransferItemCode,
                    empty($stockTransferItemProductName) ? '[empty]' : $stockTransferItemProductName,
                    $stockTransferItemProductCategoryId ?? '[null]',
                    $stockTransferItemProductBrandId ?? '[null]',
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ];
                $cacheKey = 'read_any_stock_transfer_item_serial_'.implode('_', $cacheParams);

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

    public function read(StockTransferItemSerial $stockTransferItemSerial): StockTransferItemSerial
    {
        return $stockTransferItemSerial->load(self::LIST_EAGER_LOADS);
    }

    public function create(StockTransferItemSerialCreateDTO $data): StockTransferItemSerial
    {
        $timer_start = microtime(true);

        try {
            $stockTransferItemSerial = new StockTransferItemSerial();
            $stockTransferItemSerial->company_id = $data->companyId;
            $stockTransferItemSerial->branch_id = $data->branchId;
            $stockTransferItemSerial->stock_transfer_id = $data->stockTransferId;
            $stockTransferItemSerial->stock_transfer_item_id = $data->stockTransferItemId;
            $stockTransferItemSerial->serial = $data->serial;
            $stockTransferItemSerial->save();

            $stockSerialTransactionCreateDTO = StockSerialTransactionCreateDTO::fromStockTransferItemSerialSource(
                stockTransferItemSerial: $stockTransferItemSerial,
                serial: $data->serial
            );
            $this->stockSerialTransactionActions->create($stockSerialTransactionCreateDTO);
            $stockSerialTransactionCreateDTO = StockSerialTransactionCreateDTO::fromStockTransferItemSerialDestination(
                stockTransferItemSerial: $stockTransferItemSerial,
                serial: $data->serial
            );
            $this->stockSerialTransactionActions->create($stockSerialTransactionCreateDTO);

            $this->flushCache();

            return $stockTransferItemSerial;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(StockTransferItemSerial $stockTransferItemSerial, StockTransferItemSerialUpdateDTO $data): StockTransferItemSerial
    {
        $timer_start = microtime(true);

        try {
            $stockTransferItemSerial->serial = $data->serial;
            $stockTransferItemSerial->save();

            $stockSerialTransactionSource = $stockTransferItemSerial->stockSerialTransactionSource;
            if (! $stockSerialTransactionSource) {
                $dto = StockSerialTransactionCreateDTO::fromStockTransferItemSerialSource($stockTransferItemSerial, $data->serial);
                $this->stockSerialTransactionActions->create($dto);
            } else {
                $dto = StockSerialTransactionUpdateDTO::fromStockTransferItemSerialSource($stockTransferItemSerial, $data->serial);
                $this->stockSerialTransactionActions->update($stockSerialTransactionSource, $dto);
            }

            $stockSerialTransactionDestination = $stockTransferItemSerial->stockSerialTransactionDestination;
            if (! $stockSerialTransactionDestination) {
                $dto = StockSerialTransactionCreateDTO::fromStockTransferItemSerialDestination($stockTransferItemSerial, $data->serial);
                $this->stockSerialTransactionActions->create($dto);
            } else {
                $dto = StockSerialTransactionUpdateDTO::fromStockTransferItemSerialDestination($stockTransferItemSerial, $data->serial);
                $this->stockSerialTransactionActions->update($stockSerialTransactionDestination, $dto);
            }

            $this->flushCache();

            return $stockTransferItemSerial->refresh();
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(StockTransferItemSerial $stockTransferItemSerial): bool
    {
        $timer_start = microtime(true);

        $retval = false;

        try {
            $stockSerialTransactionSource = $stockTransferItemSerial->stockSerialTransactionSource;
            if ($stockSerialTransactionSource) {
                $this->stockSerialTransactionActions->delete($stockSerialTransactionSource);
            }

            $stockSerialTransactionDestination = $stockTransferItemSerial->stockSerialTransactionDestination;
            if ($stockSerialTransactionDestination) {
                $this->stockSerialTransactionActions->delete($stockSerialTransactionDestination);
            }

            $retval = $stockTransferItemSerial->delete();

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
