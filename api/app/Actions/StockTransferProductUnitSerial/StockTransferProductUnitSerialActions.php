<?php

namespace App\Actions\StockTransferProductUnitSerial;

use App\Actions\StockSerialTransaction\StockSerialTransactionActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\StockSerialTransactionCreateDTO;
use App\DTOs\StockSerialTransactionUpdateDTO;
use App\DTOs\StockTransferProductUnitSerialCreateDTO;
use App\DTOs\StockTransferProductUnitSerialUpdateDTO;
use App\Helpers\TimezoneHelper;
use App\Models\StockTransferProductUnitSerial;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class StockTransferProductUnitSerialActions
{
    use CacheHelper;
    use LoggerHelper;

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
        ?string $stockTransferProductUnitCode,
        ?string $stockTransferProductUnitProductName,
        ?int $stockTransferProductUnitProductCategoryId,
        ?int $stockTransferProductUnitProductBrandId,

        ?ExecuteDTO $execute
    ) {
        $query = StockTransferProductUnitSerial::select('stock_transfer_product_unit_serials.*')
            ->with([
                'company',
                'branch',
                'stockTransfer',
                'stockTransferProductUnit.productUnit.unit',
                'stockTransferProductUnit.productUnit.product.images',
            ])
            ->join('companies', 'companies.id', '=', 'stock_transfer_product_unit_serials.company_id')
            ->join('stock_transfers', 'stock_transfers.id', '=', 'stock_transfer_product_unit_serials.stock_transfer_id')
            ->join('stock_transfer_product_units', 'stock_transfer_product_units.id', '=', 'stock_transfer_product_unit_serials.stock_transfer_product_unit_id')
            ->join('product_units', 'product_units.id', '=', 'stock_transfer_product_units.product_unit_id')
            ->join('products', 'products.id', '=', 'product_units.product_id')
            ->join('product_categories', 'product_categories.id', '=', 'products.category_id')
            ->leftJoin('brands', 'brands.id', '=', 'products.brand_id')
            ->whereCompanyId('stock_transfer_product_unit_serials', $companyId)
            ->whereBranchId('stock_transfer_product_unit_serials', $branchId)
            ->withTrashed();

        $query->where(function ($query) use (
            $withTrashed,
            $search,
            $stockTransferCode,
            $stockTransferStartDate,
            $stockTransferEndDate,
            $stockTransferSourceWarehouseId,
            $stockTransferDestinationWarehouseId,
            $stockTransferProductUnitCode,
            $stockTransferProductUnitProductName,
            $stockTransferProductUnitProductCategoryId,
            $stockTransferProductUnitProductBrandId,
        ) {
            $query->withoutTrashed();
            if ($withTrashed) {
                $query->withTrashed();
            }

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

            if ($stockTransferProductUnitCode) {
                $query->where('product_units.code', 'like', '%'.$stockTransferProductUnitCode.'%');
            }

            if ($stockTransferProductUnitProductName) {
                $query->where('products.name', 'like', '%'.$stockTransferProductUnitProductName.'%');
            }

            if ($stockTransferProductUnitProductCategoryId) {
                $query->where('products.category_id', $stockTransferProductUnitProductCategoryId);
            }

            if ($stockTransferProductUnitProductBrandId) {
                $query->where('products.brand_id', $stockTransferProductUnitProductBrandId);
            }
        });

        $query->orderBy('stock_transfers.date', 'desc')
            ->orderBy('stock_transfer_product_unit_serials.id', 'asc');

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
                    empty($stockTransferProductUnitCode) ? '[empty]' : $stockTransferProductUnitCode,
                    empty($stockTransferProductUnitProductName) ? '[empty]' : $stockTransferProductUnitProductName,
                    $stockTransferProductUnitProductCategoryId ?? '[null]',
                    $stockTransferProductUnitProductBrandId ?? '[null]',
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ];
                $cacheKey = 'read_any_stock_transfer_product_unit_serial_'.implode('_', $cacheParams);

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

    public function read(StockTransferProductUnitSerial $stockTransferProductUnitSerial): StockTransferProductUnitSerial
    {
        return $stockTransferProductUnitSerial->load([
            'company',
            'branch',
            'stockTransfer',
            'stockTransferProductUnit.stockTransfer',
            'stockTransferProductUnit.productUnit.unit',
            'stockTransferProductUnit.productUnit.product.images',
            'stockSerialTransactionSource',
            'stockSerialTransactionDestination',
        ]);
    }

    public function create(StockTransferProductUnitSerialCreateDTO $data): StockTransferProductUnitSerial
    {
        $timer_start = microtime(true);

        try {
            $stockTransferProductUnitSerial = new StockTransferProductUnitSerial();
            $stockTransferProductUnitSerial->company_id = $data->companyId;
            $stockTransferProductUnitSerial->branch_id = $data->branchId;
            $stockTransferProductUnitSerial->stock_transfer_id = $data->stockTransferId;
            $stockTransferProductUnitSerial->stock_transfer_product_unit_id = $data->stockTransferProductUnitId;
            $stockTransferProductUnitSerial->serial = $data->serial;
            $stockTransferProductUnitSerial->save();

            $stockSerialTransactionCreateDTO = StockSerialTransactionCreateDTO::fromStockTransferProductUnitSerialSource(
                stockTransferProductUnitSerial: $stockTransferProductUnitSerial,
                serial: $data->serial
            );
            $this->stockSerialTransactionActions->create($stockSerialTransactionCreateDTO);
            $stockSerialTransactionCreateDTO = StockSerialTransactionCreateDTO::fromStockTransferProductUnitSerialDestination(
                stockTransferProductUnitSerial: $stockTransferProductUnitSerial,
                serial: $data->serial
            );
            $this->stockSerialTransactionActions->create($stockSerialTransactionCreateDTO);

            $this->flushCache();

            return $stockTransferProductUnitSerial;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(StockTransferProductUnitSerial $stockTransferProductUnitSerial, StockTransferProductUnitSerialUpdateDTO $data): StockTransferProductUnitSerial
    {
        $timer_start = microtime(true);

        try {
            $stockTransferProductUnitSerial->serial = $data->serial;
            $stockTransferProductUnitSerial->save();

            $stockSerialTransactionSource = $stockTransferProductUnitSerial->stockSerialTransactionSource;
            if (! $stockSerialTransactionSource) {
                $dto = StockSerialTransactionCreateDTO::fromStockTransferProductUnitSerialSource($stockTransferProductUnitSerial, $data->serial);
                $this->stockSerialTransactionActions->create($dto);
            } else {
                $dto = StockSerialTransactionUpdateDTO::fromStockTransferProductUnitSerialSource($stockTransferProductUnitSerial, $data->serial);
                $this->stockSerialTransactionActions->update($stockSerialTransactionSource, $dto);
            }

            $stockSerialTransactionDestination = $stockTransferProductUnitSerial->stockSerialTransactionDestination;
            if (! $stockSerialTransactionDestination) {
                $dto = StockSerialTransactionCreateDTO::fromStockTransferProductUnitSerialDestination($stockTransferProductUnitSerial, $data->serial);
                $this->stockSerialTransactionActions->create($dto);
            } else {
                $dto = StockSerialTransactionUpdateDTO::fromStockTransferProductUnitSerialDestination($stockTransferProductUnitSerial, $data->serial);
                $this->stockSerialTransactionActions->update($stockSerialTransactionDestination, $dto);
            }

            $this->flushCache();

            return $stockTransferProductUnitSerial->refresh();
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(StockTransferProductUnitSerial $stockTransferProductUnitSerial): bool
    {
        $timer_start = microtime(true);

        $retval = false;

        try {
            $stockSerialTransactionSource = $stockTransferProductUnitSerial->stockSerialTransactionSource;
            if ($stockSerialTransactionSource) {
                $this->stockSerialTransactionActions->delete($stockSerialTransactionSource);
            }

            $stockSerialTransactionDestination = $stockTransferProductUnitSerial->stockSerialTransactionDestination;
            if ($stockSerialTransactionDestination) {
                $this->stockSerialTransactionActions->delete($stockSerialTransactionDestination);
            }

            $retval = $stockTransferProductUnitSerial->delete();

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
