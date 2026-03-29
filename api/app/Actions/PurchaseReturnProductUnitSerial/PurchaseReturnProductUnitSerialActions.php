<?php

namespace App\Actions\PurchaseReturnProductUnitSerial;

use App\DTOs\ExecuteDTO;
use App\Models\PurchaseReturnProductUnitSerial;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class PurchaseReturnProductUnitSerialActions
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

        ?int $purchaseId,
        ?int $purchaseReturnProductUnitId,

        ?ExecuteDTO $execute
    ) {
        $query = PurchaseReturnProductUnitSerial::select('purchase_return_product_unit_serials.*')
            ->with([
                'company',
                'branch',
                'purchase',
                'purchaseReturnProductUnit',
            ])
            ->join('companies', 'companies.id', '=', 'purchase_return_product_unit_serials.company_id')
            ->whereCompanyId('purchase_return_product_unit_serials', $companyId)
            ->withTrashed();

        $query->where(function ($query) use ($withTrashed, $search, $branchId, $purchaseId, $purchaseReturnProductUnitId) {
            $query->withoutTrashed();
            if ($withTrashed) $query->withTrashed();

            if ($search) {
                $query->search($search);
            }

            if ($branchId) {
                $query->where('purchase_return_product_unit_serials.branch_id', $branchId);
            }

            if ($purchaseId) {
                $query->where('purchase_return_product_unit_serials.purchase_id', $purchaseId);
            }

            if ($purchaseReturnProductUnitId) {
                $query->where('purchase_return_product_unit_serials.purchase_return_product_unit_id', $purchaseReturnProductUnitId);
            }
        });

        $query->orderBy('companies.name', 'asc')
            ->orderBy('purchase_return_product_unit_serials.id', 'asc');

        if ($execute) {
            $timer_start = microtime(true);
            $recordsCount = 0;

            try {
                $cacheParams = [
                    $withTrashed ? 'true' : 'false',
                    $companyId,
                    empty($search) ? '[empty]' : $search,
                    $branchId ?? '[null]',
                    $purchaseId ?? '[null]',
                    $purchaseReturnProductUnitId ?? '[null]',
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ];

                $cacheKey = 'read_any_purchase_return_product_unit_serial_'.implode('_', $cacheParams);

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

    public function read(PurchaseReturnProductUnitSerial $purchaseReturnProductUnitSerial): PurchaseReturnProductUnitSerial
    {
        return $purchaseReturnProductUnitSerial->load([
            'company',
            'branch',
            'purchase',
            'purchaseReturnProductUnit',
        ]);
    }

    public function create(array $data): PurchaseReturnProductUnitSerial
    {
        $timer_start = microtime(true);

        try {
            $purchaseReturnProductUnitSerial = new PurchaseReturnProductUnitSerial();
            $purchaseReturnProductUnitSerial->company_id = $data['company_id'];
            $purchaseReturnProductUnitSerial->branch_id = $data['branch_id'];
            $purchaseReturnProductUnitSerial->purchase_id = $data['purchase_id'];
            $purchaseReturnProductUnitSerial->purchase_return_product_unit_id = $data['purchase_return_product_unit_id'];
            $purchaseReturnProductUnitSerial->serial = $data['serial'];
            $purchaseReturnProductUnitSerial->save();

            $this->flushCache();

            return $purchaseReturnProductUnitSerial;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(PurchaseReturnProductUnitSerial $purchaseReturnProductUnitSerial, array $data): PurchaseReturnProductUnitSerial
    {
        $timer_start = microtime(true);

        try {
            $purchaseReturnProductUnitSerial->company_id = $data['company_id'];
            $purchaseReturnProductUnitSerial->branch_id = $data['branch_id'];
            $purchaseReturnProductUnitSerial->purchase_id = $data['purchase_id'];
            $purchaseReturnProductUnitSerial->purchase_return_product_unit_id = $data['purchase_return_product_unit_id'];
            $purchaseReturnProductUnitSerial->serial = $data['serial'];
            $purchaseReturnProductUnitSerial->save();

            $this->flushCache();

            return $purchaseReturnProductUnitSerial->refresh();
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(PurchaseReturnProductUnitSerial $purchaseReturnProductUnitSerial): bool
    {
        $timer_start = microtime(true);

        $retval = false;

        try {
            $retval = $purchaseReturnProductUnitSerial->delete();

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
