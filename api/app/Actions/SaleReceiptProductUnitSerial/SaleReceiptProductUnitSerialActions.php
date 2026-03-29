<?php

namespace App\Actions\SaleReceiptProductUnitSerial;

use App\DTOs\ExecuteDTO;
use App\Models\Company;
use App\Models\SaleReceiptProductUnitSerial;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class SaleReceiptProductUnitSerialActions
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

        ?int $saleReceiptId,
        ?int $saleReceiptProductUnitId,

        ?ExecuteDTO $execute
    ) {
        $query = SaleReceiptProductUnitSerial::select('sale_receipt_product_unit_serials.*')
            ->with([
                'company',
                'branch',
                'saleReceipt',
                'saleReceiptProductUnit',
            ])
            ->join('companies', 'companies.id', '=', 'sale_receipt_product_unit_serials.company_id')
            ->whereCompanyId('sale_receipt_product_unit_serials', $companyId)
            ->withTrashed();

        $query->where(function ($query) use ($withTrashed, $search, $branchId, $saleReceiptId, $saleReceiptProductUnitId) {
            $query->withoutTrashed();
            if ($withTrashed) $query->withTrashed();

            if ($search) {
                $query->search($search);
            }

            if ($branchId) {
                $query->where('sale_receipt_product_unit_serials.branch_id', $branchId);
            }

            if ($saleReceiptId) {
                $query->where('sale_receipt_product_unit_serials.sale_receipt_id', $saleReceiptId);
            }

            if ($saleReceiptProductUnitId) {
                $query->where('sale_receipt_product_unit_serials.sale_receipt_product_unit_id', $saleReceiptProductUnitId);
            }
        });

        $query->orderBy('companies.name', 'asc')
            ->orderBy('sale_receipt_product_unit_serials.id', 'asc');

        if ($execute) {
            $timer_start = microtime(true);
            $recordsCount = 0;

            try {
                $cacheParams = [
                    $withTrashed ? 'true' : 'false',
                    $companyId,
                    empty($search) ? '[empty]' : $search,
                    $branchId ?? '[null]',
                    $saleReceiptId ?? '[null]',
                    $saleReceiptProductUnitId ?? '[null]',
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ];

                $cacheKey = 'read_any_sale_receipt_product_unit_serial_'.implode('_', $cacheParams);

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

    public function read(SaleReceiptProductUnitSerial $saleReceiptProductUnitSerial): SaleReceiptProductUnitSerial
    {
        return $saleReceiptProductUnitSerial->load([
            'company',
            'branch',
            'saleReceipt',
            'saleReceiptProductUnit',
        ]);
    }

    public function create(array $data): SaleReceiptProductUnitSerial
    {
        $timer_start = microtime(true);

        try {
            $saleReceiptProductUnitSerial = new SaleReceiptProductUnitSerial();
            $saleReceiptProductUnitSerial->company_id = $data['company_id'];
            $saleReceiptProductUnitSerial->branch_id = $data['branch_id'];
            $saleReceiptProductUnitSerial->sale_receipt_id = $data['sale_receipt_id'];
            $saleReceiptProductUnitSerial->sale_receipt_product_unit_id = $data['sale_receipt_product_unit_id'];
            $saleReceiptProductUnitSerial->serial = $data['serial'];
            $saleReceiptProductUnitSerial->save();

            $this->flushCache();

            return $saleReceiptProductUnitSerial;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(SaleReceiptProductUnitSerial $saleReceiptProductUnitSerial, array $data): SaleReceiptProductUnitSerial
    {
        $timer_start = microtime(true);

        try {
            $saleReceiptProductUnitSerial->company_id = $data['company_id'];
            $saleReceiptProductUnitSerial->branch_id = $data['branch_id'];
            $saleReceiptProductUnitSerial->sale_receipt_id = $data['sale_receipt_id'];
            $saleReceiptProductUnitSerial->sale_receipt_product_unit_id = $data['sale_receipt_product_unit_id'];
            $saleReceiptProductUnitSerial->serial = $data['serial'];
            $saleReceiptProductUnitSerial->save();

            $this->flushCache();

            return $saleReceiptProductUnitSerial->refresh();
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(SaleReceiptProductUnitSerial $saleReceiptProductUnitSerial): bool
    {
        $timer_start = microtime(true);

        $retval = false;

        try {
            $retval = $saleReceiptProductUnitSerial->delete();

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

    public function generateUniqueCode(int $companyId, string $code, ?int $exceptId): string
    {
        if ($code == config('dcslab.KEYWORDS.AUTO')) {
            $company = Company::find($companyId);

            $tryCount = 0;
            do {
                $count = $company->saleReceiptProductUnitSerials()->withTrashed()->count() + 1 + $tryCount;
                $code = 'WH'.str_pad($count, 3, '0', STR_PAD_LEFT);
                $tryCount++;
            } while (! $this->isUniqueCode($companyId, $code, $exceptId));

            return $code;
        } else {
            return $code;
        }
    }

    public function isUniqueCode(int $companyId, string $code, ?int $exceptId): bool
    {
        $result = SaleReceiptProductUnitSerial::whereCompanyId('sale_receipt_product_unit_serials', $companyId)->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0 ? true : false;
    }
}
