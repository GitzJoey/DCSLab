<?php

namespace App\Actions\SalesOrderDeliveryItemSerial;

use App\Actions\StockSerialTransaction\StockSerialTransactionActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\SalesOrderDeliveryItemSerialCreateDTO;
use App\DTOs\SalesOrderDeliveryItemSerialUpdateDTO;
use App\DTOs\StockSerialTransactionCreateDTO;
use App\DTOs\StockSerialTransactionUpdateDTO;
use App\Models\SalesOrderDeliveryItemSerial;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class SalesOrderDeliveryItemSerialActions
{
    use CacheHelper;
    use LoggerHelper;

    public function __construct(
        private readonly StockSerialTransactionActions $stockSerialTransactionActions,
    ) {
    }

    private const LIST_EAGER_LOADS = [
        'company',
        'branch',
        'salesOrderDelivery.customer',
        'salesOrderDelivery.salesOrder.customer',
        'salesOrderDelivery.warehouse',
        'salesOrderDeliveryItem.productUnit.unit',
        'salesOrderDeliveryItem.productUnit.product.category',
        'salesOrderDeliveryItem.productUnit.product.brand',
        'salesOrderDeliveryItem.productUnit.product.baseProductUnit.unit',
        'salesOrderDeliveryItem.productUnit.product.images',
        'salesOrderDeliveryItem.productUnit.product.mainImage',
    ];

    public function readAny(
        bool $withTrashed,
        int $companyId,
        ?int $branchId,
        ?string $search,
        ?int $salesOrderDeliveryId,
        ?int $salesOrderDeliveryItemId,
        ?ExecuteDTO $execute
    ) {
        $query = SalesOrderDeliveryItemSerial::select('sales_order_delivery_item_serials.*')
            ->with(self::LIST_EAGER_LOADS)
            ->join('companies', 'companies.id', '=', 'sales_order_delivery_item_serials.company_id')
            ->join('sales_order_deliveries', 'sales_order_deliveries.id', '=', 'sales_order_delivery_item_serials.sales_order_delivery_id')
            ->whereCompanyId('sales_order_delivery_item_serials', $companyId)
            ->whereBranchId('sales_order_delivery_item_serials', $branchId)
            ->withTrashed();

        $query->where(function ($query) use (
            $withTrashed,
            $search,
            $salesOrderDeliveryId,
            $salesOrderDeliveryItemId,
        ) {
            $query->withoutTrashed();
            if ($withTrashed) $query->withTrashed();

            if ($search) {
                $query->where('sales_order_delivery_item_serials.serial', 'like', '%'.$search.'%');
            }

            if ($salesOrderDeliveryId) {
                $query->where('sales_order_delivery_item_serials.sales_order_delivery_id', $salesOrderDeliveryId);
            }

            if ($salesOrderDeliveryItemId) {
                $query->where('sales_order_delivery_item_serials.sales_order_delivery_item_id', $salesOrderDeliveryItemId);
            }
        });

        $query->orderBy('sales_order_deliveries.date', 'desc')
            ->orderBy('sales_order_delivery_item_serials.id', 'asc');

        if ($execute) {
            $timer_start = microtime(true);
            $recordsCount = 0;

            try {
                $cacheParams = [
                    $withTrashed ? 'true' : 'false',
                    $companyId,
                    $branchId ?? '[null]',
                    empty($search) ? '[empty]' : $search,
                    $salesOrderDeliveryId ?? '[null]',
                    $salesOrderDeliveryItemId ?? '[null]',
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ];

                $cacheKey = 'read_any_sales_order_delivery_item_serial_'.implode('_', $cacheParams);

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

    public function read(SalesOrderDeliveryItemSerial $salesOrderDeliveryItemSerial): SalesOrderDeliveryItemSerial
    {
        return $salesOrderDeliveryItemSerial->load(self::LIST_EAGER_LOADS);
    }

    public function create(SalesOrderDeliveryItemSerialCreateDTO $data): SalesOrderDeliveryItemSerial
    {
        $timer_start = microtime(true);

        try {
            $salesOrderDeliveryItemSerial = new SalesOrderDeliveryItemSerial();
            $salesOrderDeliveryItemSerial->company_id = $data->companyId;
            $salesOrderDeliveryItemSerial->branch_id = $data->branchId;
            $salesOrderDeliveryItemSerial->sales_order_delivery_id = $data->salesOrderDeliveryId;
            $salesOrderDeliveryItemSerial->sales_order_delivery_item_id = $data->salesOrderDeliveryItemId;
            $salesOrderDeliveryItemSerial->serial = $data->serial;
            $salesOrderDeliveryItemSerial->save();

            $stockSerialTransactionCreateDTO = StockSerialTransactionCreateDTO::fromSalesOrderDeliveryItemSerial(
                salesOrderDeliveryItemSerial: $salesOrderDeliveryItemSerial,
                serial: $data->serial
            );
            $this->stockSerialTransactionActions->create($stockSerialTransactionCreateDTO);

            $this->flushCache();

            return $salesOrderDeliveryItemSerial;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(SalesOrderDeliveryItemSerial $salesOrderDeliveryItemSerial, SalesOrderDeliveryItemSerialUpdateDTO $data): SalesOrderDeliveryItemSerial
    {
        $timer_start = microtime(true);

        try {
            $salesOrderDeliveryItemSerial->serial = $data->serial;
            $salesOrderDeliveryItemSerial->save();

            $stockSerialTransaction = $salesOrderDeliveryItemSerial->stockSerialTransaction;
            if (! $stockSerialTransaction) {
                $dto = StockSerialTransactionCreateDTO::fromSalesOrderDeliveryItemSerial($salesOrderDeliveryItemSerial, $data->serial);
                $this->stockSerialTransactionActions->create($dto);
            } else {
                $dto = StockSerialTransactionUpdateDTO::fromSalesOrderDeliveryItemSerial($salesOrderDeliveryItemSerial, $data->serial);
                $this->stockSerialTransactionActions->update($stockSerialTransaction, $dto);
            }

            $this->flushCache();

            return $salesOrderDeliveryItemSerial;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(SalesOrderDeliveryItemSerial $salesOrderDeliveryItemSerial): bool
    {
        $timer_start = microtime(true);

        try {
            $stockSerialTransaction = $salesOrderDeliveryItemSerial->stockSerialTransaction;
            if ($stockSerialTransaction) {
                $this->stockSerialTransactionActions->delete($stockSerialTransaction);
            }

            $result = $salesOrderDeliveryItemSerial->delete();

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
