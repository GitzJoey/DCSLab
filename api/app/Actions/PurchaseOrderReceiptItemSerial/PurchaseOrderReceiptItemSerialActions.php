<?php

namespace App\Actions\PurchaseOrderReceiptItemSerial;

use App\Actions\StockSerialTransaction\StockSerialTransactionActions;
use App\DTOs\PurchaseOrderReceiptItemSerialCreateDTO;
use App\DTOs\PurchaseOrderReceiptItemSerialUpdateDTO;
use App\DTOs\StockSerialTransactionCreateDTO;
use App\DTOs\StockSerialTransactionUpdateDTO;
use App\Models\PurchaseOrderReceiptItemSerial;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;

class PurchaseOrderReceiptItemSerialActions
{
    use CacheHelper;
    use LoggerHelper;

    public function __construct(
        private readonly StockSerialTransactionActions $stockSerialTransactionActions,
    ) {
    }

    public function create(PurchaseOrderReceiptItemSerialCreateDTO $data): PurchaseOrderReceiptItemSerial
    {
        $timer_start = microtime(true);

        try {
            $purchaseOrderReceiptItemSerial = new PurchaseOrderReceiptItemSerial();
            $purchaseOrderReceiptItemSerial->company_id = $data->companyId;
            $purchaseOrderReceiptItemSerial->branch_id = $data->branchId;
            $purchaseOrderReceiptItemSerial->purchase_order_receipt_id = $data->purchaseOrderReceiptId;
            $purchaseOrderReceiptItemSerial->purchase_order_receipt_item_id = $data->purchaseOrderReceiptItemId;
            $purchaseOrderReceiptItemSerial->serial = $data->serial;
            $purchaseOrderReceiptItemSerial->save();

            $stockSerialTransactionCreateDTO = StockSerialTransactionCreateDTO::fromPurchaseOrderReceiptItemSerial(
                purchaseOrderReceiptItemSerial: $purchaseOrderReceiptItemSerial,
                serial: $data->serial
            );
            $this->stockSerialTransactionActions->create($stockSerialTransactionCreateDTO);

            $this->flushCache();

            return $purchaseOrderReceiptItemSerial;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(PurchaseOrderReceiptItemSerial $purchaseOrderReceiptItemSerial, PurchaseOrderReceiptItemSerialUpdateDTO $data): PurchaseOrderReceiptItemSerial
    {
        $timer_start = microtime(true);

        try {
            $purchaseOrderReceiptItemSerial->serial = $data->serial;
            $purchaseOrderReceiptItemSerial->save();

            $stockSerialTransaction = $purchaseOrderReceiptItemSerial->stockSerialTransaction;
            if (! $stockSerialTransaction) {
                $dto = StockSerialTransactionCreateDTO::fromPurchaseOrderReceiptItemSerial($purchaseOrderReceiptItemSerial, $data->serial);
                $this->stockSerialTransactionActions->create($dto);
            } else {
                $dto = StockSerialTransactionUpdateDTO::fromPurchaseOrderReceiptItemSerial($purchaseOrderReceiptItemSerial, $data->serial);
                $this->stockSerialTransactionActions->update($stockSerialTransaction, $dto);
            }

            $this->flushCache();

            return $purchaseOrderReceiptItemSerial;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(PurchaseOrderReceiptItemSerial $purchaseOrderReceiptItemSerial): bool
    {
        $timer_start = microtime(true);

        try {
            $stockSerialTransaction = $purchaseOrderReceiptItemSerial->stockSerialTransaction;
            if ($stockSerialTransaction) {
                $this->stockSerialTransactionActions->delete($stockSerialTransaction);
            }

            $result = $purchaseOrderReceiptItemSerial->delete();

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
