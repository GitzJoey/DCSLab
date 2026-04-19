<?php

namespace App\Actions\PurchaseReceiptItemSerial;

use App\Actions\StockSerialTransaction\StockSerialTransactionActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\PurchaseReceiptItemSerialCreateDTO;
use App\DTOs\PurchaseReceiptItemSerialUpdateDTO;
use App\DTOs\StockSerialTransactionCreateDTO;
use App\DTOs\StockSerialTransactionUpdateDTO;
use App\Models\PurchaseReceiptItemSerial;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class PurchaseReceiptItemSerialActions
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
        'purchaseReceipt.purchase.supplier',
        'purchaseReceipt.warehouse',
        'purchaseReceiptItem.productUnit.unit',
        'purchaseReceiptItem.productUnit.product.category',
        'purchaseReceiptItem.productUnit.product.brand',
        'purchaseReceiptItem.productUnit.product.baseProductUnit.unit',
        'purchaseReceiptItem.productUnit.product.images',
        'purchaseReceiptItem.productUnit.product.mainImage',
    ];

    public function readAny(
        bool $withTrashed,
        int $companyId,
        ?int $branchId,
        ?string $search,
        ?int $purchaseReceiptId,
        ?int $purchaseReceiptItemId,
        ?ExecuteDTO $execute
    ) {
        $query = PurchaseReceiptItemSerial::select('purchase_receipt_item_serials.*')
            ->with(self::LIST_EAGER_LOADS)
            ->join('companies', 'companies.id', '=', 'purchase_receipt_item_serials.company_id')
            ->join('purchase_receipts', 'purchase_receipts.id', '=', 'purchase_receipt_item_serials.purchase_receipt_id')
            ->whereCompanyId('purchase_receipt_item_serials', $companyId)
            ->whereBranchId('purchase_receipt_item_serials', $branchId)
            ->withTrashed();

        $query->where(function ($query) use (
            $withTrashed,
            $search,
            $purchaseReceiptId,
            $purchaseReceiptItemId,
        ) {
            $query->withoutTrashed();
            if ($withTrashed) $query->withTrashed();

            if ($search) {
                $query->where('purchase_receipt_item_serials.serial', 'like', '%'.$search.'%');
            }

            if ($purchaseReceiptId) {
                $query->where('purchase_receipt_item_serials.purchase_receipt_id', $purchaseReceiptId);
            }

            if ($purchaseReceiptItemId) {
                $query->where('purchase_receipt_item_serials.purchase_receipt_item_id', $purchaseReceiptItemId);
            }
        });

        $query->orderBy('purchase_receipts.date', 'desc')
            ->orderBy('purchase_receipt_item_serials.id', 'asc');

        if ($execute) {
            $timer_start = microtime(true);
            $recordsCount = 0;

            try {
                $cacheParams = [
                    $withTrashed ? 'true' : 'false',
                    $companyId,
                    $branchId ?? '[null]',
                    empty($search) ? '[empty]' : $search,
                    $purchaseReceiptId ?? '[null]',
                    $purchaseReceiptItemId ?? '[null]',
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ];

                $cacheKey = 'read_any_purchase_receipt_item_serial_'.implode('_', $cacheParams);

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

    public function read(PurchaseReceiptItemSerial $purchaseReceiptItemSerial): PurchaseReceiptItemSerial
    {
        return $purchaseReceiptItemSerial->load(self::LIST_EAGER_LOADS);
    }

    public function create(PurchaseReceiptItemSerialCreateDTO $data): PurchaseReceiptItemSerial
    {
        $timer_start = microtime(true);

        try {
            $purchaseReceiptItemSerial = new PurchaseReceiptItemSerial();
            $purchaseReceiptItemSerial->company_id = $data->companyId;
            $purchaseReceiptItemSerial->branch_id = $data->branchId;
            $purchaseReceiptItemSerial->purchase_receipt_id = $data->purchaseReceiptId;
            $purchaseReceiptItemSerial->purchase_receipt_item_id = $data->purchaseReceiptItemId;
            $purchaseReceiptItemSerial->serial = $data->serial;
            $purchaseReceiptItemSerial->save();

            $stockSerialTransactionCreateDTO = StockSerialTransactionCreateDTO::fromPurchaseReceiptItemSerial(
                purchaseReceiptItemSerial: $purchaseReceiptItemSerial,
                serial: $data->serial
            );
            $this->stockSerialTransactionActions->create($stockSerialTransactionCreateDTO);

            $this->flushCache();

            return $purchaseReceiptItemSerial;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(PurchaseReceiptItemSerial $purchaseReceiptItemSerial, PurchaseReceiptItemSerialUpdateDTO $data): PurchaseReceiptItemSerial
    {
        $timer_start = microtime(true);

        try {
            $purchaseReceiptItemSerial->serial = $data->serial;
            $purchaseReceiptItemSerial->save();

            $stockSerialTransaction = $purchaseReceiptItemSerial->stockSerialTransaction;
            if (! $stockSerialTransaction) {
                $dto = StockSerialTransactionCreateDTO::fromPurchaseReceiptItemSerial($purchaseReceiptItemSerial, $data->serial);
                $this->stockSerialTransactionActions->create($dto);
            } else {
                $dto = StockSerialTransactionUpdateDTO::fromPurchaseReceiptItemSerial($purchaseReceiptItemSerial, $data->serial);
                $this->stockSerialTransactionActions->update($stockSerialTransaction, $dto);
            }

            $this->flushCache();

            return $purchaseReceiptItemSerial;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(PurchaseReceiptItemSerial $purchaseReceiptItemSerial): bool
    {
        $timer_start = microtime(true);

        try {
            $stockSerialTransaction = $purchaseReceiptItemSerial->stockSerialTransaction;
            if ($stockSerialTransaction) {
                $this->stockSerialTransactionActions->delete($stockSerialTransaction);
            }

            $result = $purchaseReceiptItemSerial->delete();

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
