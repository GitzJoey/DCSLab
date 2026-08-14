<?php

namespace App\Actions\SalesReturnItemSerial;

use App\Actions\StockSerialTransaction\StockSerialTransactionActions;
use App\DTOs\SalesReturnItemSerialCreateDTO;
use App\DTOs\SalesReturnItemSerialUpdateDTO;
use App\DTOs\StockSerialTransactionCreateDTO;
use App\DTOs\StockSerialTransactionUpdateDTO;
use App\Models\SalesReturnItemSerial;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;

class SalesReturnItemSerialActions
{
    use CacheHelper;
    use LoggerHelper;

    public function __construct(
        private readonly StockSerialTransactionActions $stockSerialTransactionActions,
    ) {
    }

    public function create(SalesReturnItemSerialCreateDTO $data): SalesReturnItemSerial
    {
        $timer_start = microtime(true);

        try {
            $salesReturnItemSerial = new SalesReturnItemSerial();
            $salesReturnItemSerial->company_id = $data->companyId;
            $salesReturnItemSerial->branch_id = $data->branchId;
            $salesReturnItemSerial->sales_return_id = $data->salesReturnId;
            $salesReturnItemSerial->sales_return_item_id = $data->salesReturnItemId;
            $salesReturnItemSerial->serial = $data->serial;
            $salesReturnItemSerial->save();

            $stockSerialTransactionCreateDTO = StockSerialTransactionCreateDTO::fromSalesReturnItemSerial(
                salesReturnItemSerial: $salesReturnItemSerial,
                serial: $data->serial
            );
            $this->stockSerialTransactionActions->create($stockSerialTransactionCreateDTO);

            $this->flushCache();

            return $salesReturnItemSerial;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(SalesReturnItemSerial $salesReturnItemSerial, SalesReturnItemSerialUpdateDTO $data): SalesReturnItemSerial
    {
        $timer_start = microtime(true);

        try {
            $salesReturnItemSerial->serial = $data->serial;
            $salesReturnItemSerial->save();

            $stockSerialTransaction = $salesReturnItemSerial->stockSerialTransaction;
            if (! $stockSerialTransaction) {
                $dto = StockSerialTransactionCreateDTO::fromSalesReturnItemSerial($salesReturnItemSerial, $data->serial);
                $this->stockSerialTransactionActions->create($dto);
            } else {
                $dto = StockSerialTransactionUpdateDTO::fromSalesReturnItemSerial($salesReturnItemSerial, $data->serial);
                $this->stockSerialTransactionActions->update($stockSerialTransaction, $dto);
            }

            $this->flushCache();

            return $salesReturnItemSerial;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(SalesReturnItemSerial $salesReturnItemSerial): bool
    {
        $timer_start = microtime(true);

        try {
            $stockSerialTransaction = $salesReturnItemSerial->stockSerialTransaction;
            if ($stockSerialTransaction) {
                $this->stockSerialTransactionActions->delete($stockSerialTransaction);
            }

            $result = $salesReturnItemSerial->delete();

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
