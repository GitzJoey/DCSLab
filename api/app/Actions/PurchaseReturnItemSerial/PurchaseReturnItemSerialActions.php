<?php

namespace App\Actions\PurchaseReturnItemSerial;

use App\Actions\StockSerialTransaction\StockSerialTransactionActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\PurchaseReturnItemSerialCreateDTO;
use App\DTOs\PurchaseReturnItemSerialUpdateDTO;
use App\DTOs\StockSerialTransactionCreateDTO;
use App\DTOs\StockSerialTransactionUpdateDTO;
use App\Models\PurchaseReturnItemSerial;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class PurchaseReturnItemSerialActions
{
    use CacheHelper;
    use LoggerHelper;

    private const LIST_EAGER_LOADS = [
        'company',
        'branch',
        'purchaseReturn.supplier',
        'purchaseReturn.warehouse',
        'purchaseReturnItem.productUnit.unit',
        'purchaseReturnItem.productUnit.product.category',
        'purchaseReturnItem.productUnit.product.brand',
        'purchaseReturnItem.productUnit.product.baseProductUnit.unit',
        'purchaseReturnItem.productUnit.product.images',
        'purchaseReturnItem.productUnit.product.mainImage',
    ];

    public function __construct(
        private readonly StockSerialTransactionActions $stockSerialTransactionActions,
    ) {
    }

    public function readAny(
        bool $withTrashed,
        int $companyId,
        ?int $branchId,
        ?string $search,
        ?int $purchaseReturnId,
        ?int $purchaseReturnItemId,
        ?ExecuteDTO $execute
    ) {
        $query = PurchaseReturnItemSerial::select('purchase_return_item_serials.*')
            ->with(self::LIST_EAGER_LOADS)
            ->join('companies', 'companies.id', '=', 'purchase_return_item_serials.company_id')
            ->join('purchase_returns', 'purchase_returns.id', '=', 'purchase_return_item_serials.purchase_return_id')
            ->whereCompanyId('purchase_return_item_serials', $companyId)
            ->whereBranchId('purchase_return_item_serials', $branchId)
            ->withTrashed();

        $query->where(function ($query) use (
            $withTrashed,
            $search,
            $purchaseReturnId,
            $purchaseReturnItemId,
        ) {
            $query->withoutTrashed();
            if ($withTrashed) {
                $query->withTrashed();
            }

            if ($search) {
                $query->where('purchase_return_item_serials.serial', 'like', '%'.$search.'%');
            }

            if ($purchaseReturnId) {
                $query->where('purchase_return_item_serials.purchase_return_id', $purchaseReturnId);
            }

            if ($purchaseReturnItemId) {
                $query->where('purchase_return_item_serials.purchase_return_item_id', $purchaseReturnItemId);
            }
        });

        $query->orderBy('purchase_returns.date', 'desc')
            ->orderBy('purchase_return_item_serials.id', 'asc');

        if ($execute) {
            $timer_start = microtime(true);
            $recordsCount = 0;

            try {
                $cacheParams = [
                    $withTrashed ? 'true' : 'false',
                    $companyId,
                    $branchId ?? '[null]',
                    empty($search) ? '[empty]' : $search,
                    $purchaseReturnId ?? '[null]',
                    $purchaseReturnItemId ?? '[null]',
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ];

                $cacheKey = 'read_any_purchase_return_item_serial_'.implode('_', $cacheParams);

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

    public function read(PurchaseReturnItemSerial $purchaseReturnItemSerial): PurchaseReturnItemSerial
    {
        return $purchaseReturnItemSerial->load(self::LIST_EAGER_LOADS);
    }

    public function create(PurchaseReturnItemSerialCreateDTO $data): PurchaseReturnItemSerial
    {
        $timer_start = microtime(true);

        try {
            $purchaseReturnItemSerial = new PurchaseReturnItemSerial();
            $purchaseReturnItemSerial->company_id = $data->companyId;
            $purchaseReturnItemSerial->branch_id = $data->branchId;
            $purchaseReturnItemSerial->purchase_return_id = $data->purchaseReturnId;
            $purchaseReturnItemSerial->purchase_return_item_id = $data->purchaseReturnItemId;
            $purchaseReturnItemSerial->serial = $data->serial;
            $purchaseReturnItemSerial->save();

            $stockSerialTransactionCreateDTO = StockSerialTransactionCreateDTO::fromPurchaseReturnItemSerial(
                purchaseReturnItemSerial: $purchaseReturnItemSerial,
                serial: $data->serial
            );
            $this->stockSerialTransactionActions->create($stockSerialTransactionCreateDTO);

            $this->flushCache();

            return $purchaseReturnItemSerial;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(PurchaseReturnItemSerial $purchaseReturnItemSerial, PurchaseReturnItemSerialUpdateDTO $data): PurchaseReturnItemSerial
    {
        $timer_start = microtime(true);

        try {
            $purchaseReturnItemSerial->serial = $data->serial;
            $purchaseReturnItemSerial->save();

            $stockSerialTransaction = $purchaseReturnItemSerial->stockSerialTransaction;
            if (! $stockSerialTransaction) {
                $dto = StockSerialTransactionCreateDTO::fromPurchaseReturnItemSerial($purchaseReturnItemSerial, $data->serial);
                $this->stockSerialTransactionActions->create($dto);
            } else {
                $dto = StockSerialTransactionUpdateDTO::fromPurchaseReturnItemSerial($purchaseReturnItemSerial, $data->serial);
                $this->stockSerialTransactionActions->update($stockSerialTransaction, $dto);
            }

            $this->flushCache();

            return $purchaseReturnItemSerial;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(PurchaseReturnItemSerial $purchaseReturnItemSerial): bool
    {
        $timer_start = microtime(true);

        try {
            $stockSerialTransaction = $purchaseReturnItemSerial->stockSerialTransaction;
            if ($stockSerialTransaction) {
                $this->stockSerialTransactionActions->delete($stockSerialTransaction);
            }

            $result = $purchaseReturnItemSerial->delete();

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
