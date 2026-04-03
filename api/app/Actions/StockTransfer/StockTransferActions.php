<?php

namespace App\Actions\StockTransfer;

use App\Actions\StockTransferItem\StockTransferItemActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\StockTransferCreateDTO;
use App\DTOs\StockTransferItemCreateDTO;
use App\DTOs\StockTransferItemUpdateDTO;
use App\DTOs\StockTransferUpdateDTO;
use App\Helpers\TimezoneHelper;
use App\Models\Company;
use App\Models\StockTransfer;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class StockTransferActions
{
    use CacheHelper;
    use LoggerHelper;

    private $stockTransferItemActions;

    public function __construct(
        StockTransferItemActions $stockTransferItemActions,
    ) {
        $this->stockTransferItemActions = $stockTransferItemActions;
    }

    public function readAny(
        bool $withTrashed,
        int $companyId,
        ?int $branchId,

        ?string $search,
        ?string $startDate,
        ?string $endDate,
        ?int $sourceWarehouseId,
        ?int $destinationWarehouseId,

        ?ExecuteDTO $execute
    ) {
        $query = StockTransfer::select('stock_transfers.*')
            ->with(['company', 'branch', 'sourceWarehouse', 'destinationWarehouse'])
            ->when($execute?->pagination, function ($query) {
                $query->with([
                    'stockTransferItems.productUnit.unit',
                    'stockTransferItems.productUnit.product.images',
                    'stockTransferItems.productUnit.product.baseProductUnit.unit',
                    'stockTransferItems.serials',
                ]);
            })
            ->join('companies', 'companies.id', '=', 'stock_transfers.company_id')
            ->whereCompanyId('stock_transfers', $companyId)
            ->whereBranchId('stock_transfers', $branchId)
            ->withTrashed();

        $query->where(function ($query) use (
            $withTrashed,
            $search,
            $startDate,
            $endDate,
            $sourceWarehouseId,
            $destinationWarehouseId
        ) {
            $query->withoutTrashed();
            if ($withTrashed) $query->withTrashed();

            if ($search) {
                $query->search($search);
            }

            if ($startDate) {
                $query->where('stock_transfers.date', '>=', TimezoneHelper::convertToUTC($startDate));
            }

            if ($endDate) {
                $query->where('stock_transfers.date', '<=', TimezoneHelper::convertToUTC($endDate));
            }

            if ($sourceWarehouseId) {
                $query->where('stock_transfers.source_warehouse_id', $sourceWarehouseId);
            }

            if ($destinationWarehouseId) {
                $query->where('stock_transfers.destination_warehouse_id', $destinationWarehouseId);
            }
        });

        $query->orderBy('stock_transfers.date', 'desc');

        if ($execute) {
            $timer_start = microtime(true);
            $recordsCount = 0;

            try {
                $cacheParams = [
                    $withTrashed ? 'true' : 'false',
                    $companyId,
                    $branchId ?? '[null]',
                    empty($search) ? '[empty]' : $search,
                    $startDate ?? '[null]',
                    $endDate ?? '[null]',
                    $sourceWarehouseId ?? '[null]',
                    $destinationWarehouseId ?? '[null]',
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ];

                $cacheKey = 'read_any_stock_transfer_'.implode('_', $cacheParams);

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

    public function read(StockTransfer $stockTransfer): StockTransfer
    {
        return $stockTransfer->load([
            'company',
            'branch',
            'sourceWarehouse',
            'destinationWarehouse',
            'stockTransferItems.productUnit',
            'stockTransferItems.productUnit.unit',
            'stockTransferItems.productUnit.product.images',
            'stockTransferItems.productUnit.product.baseProductUnit.unit',
            'stockTransferItems.serials',
        ]);
    }

    public function create(StockTransferCreateDTO $data): StockTransfer
    {
        $timer_start = microtime(true);

        try {
            $stockTransfer = new StockTransfer();
            $stockTransfer->company_id = $data->companyId;
            $stockTransfer->branch_id = $data->branchId;
            $stockTransfer->code = $this->generateUniqueCode($data->companyId, $data->code, null);
            $stockTransfer->date = $this->generateDate($data->date);
            $stockTransfer->source_warehouse_id = $data->sourceWarehouseId;
            $stockTransfer->destination_warehouse_id = $data->destinationWarehouseId;
            $stockTransfer->remarks = $data->remarks;
            $stockTransfer->is_posted = $data->isPosted;
            $stockTransfer->save();

            $this->saveItems($stockTransfer, $data->items);

            $this->flushCache();

            return $stockTransfer;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    private function saveItems(StockTransfer $stockTransfer, array $items): void
    {
        foreach ($items as $item) {
            $data = new StockTransferItemCreateDTO(
                companyId: $stockTransfer->company_id,
                branchId: $stockTransfer->branch_id,
                stockTransferId: $stockTransfer->id,
                qty: $item['qty'],
                productUnitId: $item['product_unit_id'],
                productUnitConversionValue: $item['product_unit_conversion_value'],
                remarks: $item['remarks'],
                serials: $item['serials'],
            );
            $this->stockTransferItemActions->create($data);
        }
    }

    public function update(StockTransfer $stockTransfer, StockTransferUpdateDTO $data): StockTransfer
    {
        $timer_start = microtime(true);

        try {
            $stockTransfer->company_id = $data->companyId;
            $stockTransfer->branch_id = $data->branchId;
            $stockTransfer->code = $this->generateUniqueCode($data->companyId, $data->code, $stockTransfer->id);
            $stockTransfer->date = $this->generateDate($data->date);
            $stockTransfer->source_warehouse_id = $data->sourceWarehouseId;
            $stockTransfer->destination_warehouse_id = $data->destinationWarehouseId;
            $stockTransfer->remarks = $data->remarks;
            $stockTransfer->is_posted = $data->isPosted;
            $stockTransfer->save();

            $this->updateItems($stockTransfer, $data->deleteItemIds, $data->items);

            $this->flushCache();

            return $stockTransfer->refresh();
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    private function updateItems(StockTransfer $stockTransfer, array $deleteIds, array $items): void
    {
        foreach ($deleteIds as $deleteId) {
            $stockTransferItem = $stockTransfer->stockTransferItems()->findOrFail($deleteId);
            $this->stockTransferItemActions->delete($stockTransferItem);
        }

        foreach ($items as $item) {
            if ($item['id']) {
                $stockTransferItem = $stockTransfer->stockTransferItems()->findOrFail($item['id']);
                $data = new StockTransferItemUpdateDTO(
                    qty: $item['qty'],
                    productUnitId: $item['product_unit_id'],
                    productUnitConversionValue: $item['product_unit_conversion_value'],
                    remarks: $item['remarks'],
                    deleteSerialIds: $item['delete_serial_ids'],
                    serials: $item['serials'],
                );
                $this->stockTransferItemActions->update($stockTransferItem, $data);
            } else {
                $data = new StockTransferItemCreateDTO(
                    companyId: $stockTransfer->company_id,
                    branchId: $stockTransfer->branch_id,
                    stockTransferId: $stockTransfer->id,
                    qty: $item['qty'],
                    productUnitId: $item['product_unit_id'],
                    productUnitConversionValue: $item['product_unit_conversion_value'],
                    remarks: $item['remarks'],
                    serials: $item['serials'],
                );
                $this->stockTransferItemActions->create($data);
            }
        }
    }

    public function delete(StockTransfer $stockTransfer): bool
    {
        $timer_start = microtime(true);

        $retval = false;

        try {
            $retval = $stockTransfer->delete();

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

    public function generateDate(string $date): string
    {
        if ($date == config('dcslab.KEYWORDS.AUTO')) {
            $nowLocal = now(TimezoneHelper::getUserTimezone())->toDateTimeString();

            return TimezoneHelper::convertToUTC($nowLocal);
        }

        return TimezoneHelper::convertToUTC($date);
    }

    public function generateUniqueCode(int $companyId, string $code, ?int $exceptId): string
    {
        if ($code == config('dcslab.KEYWORDS.AUTO')) {
            $company = Company::find($companyId);

            $tryCount = 0;
            do {
                $count = $company->stockTransfers()->withTrashed()->count() + 1 + $tryCount;
                $code = 'ST'.str_pad($count, 3, '0', STR_PAD_LEFT);
                $tryCount++;
            } while (! $this->isUniqueCode($companyId, $code, $exceptId));

            return $code;
        } else {
            return $code;
        }
    }

    public function isUniqueCode(int $companyId, string $code, ?int $exceptId): bool
    {
        $result = StockTransfer::whereCompanyId('stock_transfers', $companyId)->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0 ? true : false;
    }
}
