<?php

namespace App\Actions\PurchaseProductUnitSerial;

use App\DTOs\ExecuteDTO;
use App\Models\PurchaseProductUnitSerial;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class PurchaseProductUnitSerialActions
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
        ?int $purchaseProductUnitId,

        ?ExecuteDTO $execute
    ) {
        $query = PurchaseProductUnitSerial::select('purchase_product_unit_serials.*')
            ->with([
                'company',
                'branch',
                'purchase',
                'purchaseProductUnit',
            ])
            ->join('companies', 'companies.id', '=', 'purchase_product_unit_serials.company_id')
            ->whereCompanyId('purchase_product_unit_serials', $companyId)
            ->withTrashed();

        $query->where(function ($query) use ($withTrashed, $search, $branchId, $purchaseId, $purchaseProductUnitId) {
            $query->withoutTrashed();
            if ($withTrashed) $query->withTrashed();

            if ($search) {
                $query->search($search);
            }

            if ($branchId) {
                $query->where('purchase_product_unit_serials.branch_id', $branchId);
            }

            if ($purchaseId) {
                $query->where('purchase_product_unit_serials.purchase_id', $purchaseId);
            }

            if ($purchaseProductUnitId) {
                $query->where('purchase_product_unit_serials.purchase_product_unit_id', $purchaseProductUnitId);
            }
        });

        $query->orderBy('companies.name', 'asc')
            ->orderBy('purchase_product_unit_serials.id', 'asc');

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
                    $purchaseProductUnitId ?? '[null]',
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ];

                $cacheKey = 'read_any_purchase_product_unit_serial_'.implode('_', $cacheParams);

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

    public function read(PurchaseProductUnitSerial $purchaseProductUnitSerial): PurchaseProductUnitSerial
    {
        return $purchaseProductUnitSerial->load([
            'company',
            'branch',
            'purchase',
            'purchaseProductUnit',
        ]);
    }

    public function create(array $data): PurchaseProductUnitSerial
    {
        $timer_start = microtime(true);

        try {
            $purchaseProductUnitSerial = new PurchaseProductUnitSerial();
            $purchaseProductUnitSerial->company_id = $data['company_id'];
            $purchaseProductUnitSerial->branch_id = $data['branch_id'];
            $purchaseProductUnitSerial->purchase_id = $data['purchase_id'];
            $purchaseProductUnitSerial->purchase_product_unit_id = $data['purchase_product_unit_id'];
            $purchaseProductUnitSerial->serial = $data['serial'];
            $purchaseProductUnitSerial->save();

            $this->flushCache();

            return $purchaseProductUnitSerial;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(PurchaseProductUnitSerial $purchaseProductUnitSerial, array $data): PurchaseProductUnitSerial
    {
        $timer_start = microtime(true);

        try {
            $purchaseProductUnitSerial->company_id = $data['company_id'];
            $purchaseProductUnitSerial->branch_id = $data['branch_id'];
            $purchaseProductUnitSerial->purchase_id = $data['purchase_id'];
            $purchaseProductUnitSerial->purchase_product_unit_id = $data['purchase_product_unit_id'];
            $purchaseProductUnitSerial->serial = $data['serial'];
            $purchaseProductUnitSerial->save();

            $this->flushCache();

            return $purchaseProductUnitSerial->refresh();
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(PurchaseProductUnitSerial $purchaseProductUnitSerial): bool
    {
        $timer_start = microtime(true);

        $retval = false;

        try {
            $retval = $purchaseProductUnitSerial->delete();

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
