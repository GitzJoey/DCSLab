<?php

namespace App\Actions\PurchaseReceiptProductUnitSerial;

use App\DTOs\ExecuteDTO;
use App\Models\PurchaseReceiptProductUnitSerial;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class PurchaseReceiptProductUnitSerialActions
{
    use CacheHelper;
    use LoggerHelper;

    public function __construct()
    {
    }

    public function readAny(
        bool $withTrashed,
        int $companyId,
        ?int $branchId,
        ?string $search,

        ?int $purchaseReceiptId,
        ?int $purchaseReceiptProductUnitId,

        ?ExecuteDTO $execute
    ) {
        $query = PurchaseReceiptProductUnitSerial::select('purchase_receipt_product_unit_serials.*')
            ->with([
                'company',
                'branch',
                'purchaseReceipt',
                'purchaseReceiptProductUnit',
            ])
            ->join('companies', 'companies.id', '=', 'purchase_receipt_product_unit_serials.company_id')
            ->whereCompanyId('purchase_receipt_product_unit_serials', $companyId)
            ->withTrashed();

        $query->where(function ($query) use ($withTrashed, $search, $branchId, $purchaseReceiptId, $purchaseReceiptProductUnitId) {
            $query->withoutTrashed();
            if ($withTrashed) $query->withTrashed();

            if ($search) {
                $query->search($search);
            }

            if ($branchId) {
                $query->where('purchase_receipt_product_unit_serials.branch_id', $branchId);
            }

            if ($purchaseReceiptId) {
                $query->where('purchase_receipt_product_unit_serials.purchase_receipt_id', $purchaseReceiptId);
            }

            if ($purchaseReceiptProductUnitId) {
                $query->where('purchase_receipt_product_unit_serials.purchase_receipt_product_unit_id', $purchaseReceiptProductUnitId);
            }
        });

        $query->orderBy('companies.name', 'asc')
            ->orderBy('purchase_receipt_product_unit_serials.id', 'asc');

        if ($execute) {
            $timer_start = microtime(true);
            $recordsCount = 0;

            try {
                $cacheParams = [
                    $withTrashed ? 'true' : 'false',
                    $companyId,
                    empty($search) ? '[empty]' : $search,
                    $branchId ?? '[null]',
                    $purchaseReceiptId ?? '[null]',
                    $purchaseReceiptProductUnitId ?? '[null]',
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ];

                $cacheKey = 'read_any_purchase_receipt_product_unit_serial_'.implode('_', $cacheParams);

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

    public function read(PurchaseReceiptProductUnitSerial $purchaseReceiptProductUnitSerial): PurchaseReceiptProductUnitSerial
    {
        return $purchaseReceiptProductUnitSerial->load([
            'company',
            'branch',
            'purchaseReceipt',
            'purchaseReceiptProductUnit',
        ]);
    }

    public function create(array $data): PurchaseReceiptProductUnitSerial
    {
        $timer_start = microtime(true);

        try {
            $purchaseReceiptProductUnitSerial = new PurchaseReceiptProductUnitSerial();
            $purchaseReceiptProductUnitSerial->company_id = $data['company_id'];
            $purchaseReceiptProductUnitSerial->branch_id = $data['branch_id'];
            $purchaseReceiptProductUnitSerial->purchase_receipt_id = $data['purchase_receipt_id'];
            $purchaseReceiptProductUnitSerial->purchase_receipt_product_unit_id = $data['purchase_receipt_product_unit_id'];
            $purchaseReceiptProductUnitSerial->serial = $data['serial'];
            $purchaseReceiptProductUnitSerial->save();

            $this->flushCache();

            return $purchaseReceiptProductUnitSerial;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(PurchaseReceiptProductUnitSerial $purchaseReceiptProductUnitSerial, array $data): PurchaseReceiptProductUnitSerial
    {
        $timer_start = microtime(true);

        try {
            $purchaseReceiptProductUnitSerial->company_id = $data['company_id'];
            $purchaseReceiptProductUnitSerial->branch_id = $data['branch_id'];
            $purchaseReceiptProductUnitSerial->purchase_receipt_id = $data['purchase_receipt_id'];
            $purchaseReceiptProductUnitSerial->purchase_receipt_product_unit_id = $data['purchase_receipt_product_unit_id'];
            $purchaseReceiptProductUnitSerial->serial = $data['serial'];
            $purchaseReceiptProductUnitSerial->save();

            $this->flushCache();

            return $purchaseReceiptProductUnitSerial->refresh();
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(PurchaseReceiptProductUnitSerial $purchaseReceiptProductUnitSerial): bool
    {
        $timer_start = microtime(true);

        $retval = false;

        try {
            $retval = $purchaseReceiptProductUnitSerial->delete();

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
