<?php

namespace App\Actions\SaleReceipt;

use App\DTOs\ExecuteDTO;
use App\Models\Company;
use App\Models\SaleReceipt;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class SaleReceiptActions
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
        ?int $saleId,
        ?int $warehouseId,

        ?ExecuteDTO $execute
    ) {
        $query = SaleReceipt::select('sale_receipts.*')
            ->with(['company', 'branch', 'sale', 'warehouse'])
            ->join('companies', 'companies.id', '=', 'sale_receipts.company_id')
            ->whereCompanyId('sale_receipts', $companyId)
            ->withTrashed();

        $query->where(function ($query) use ($withTrashed, $search, $branchId, $saleId, $warehouseId) {
            $query->withoutTrashed();
            if ($withTrashed) $query->withTrashed();

            if ($search) {
                $query->search($search);
            }

            if ($branchId) {
                $query->where('sale_receipts.branch_id', $branchId);
            }

            if ($saleId) {
                $query->where('sale_receipts.sale_id', $saleId);
            }

            if ($warehouseId) {
                $query->where('sale_receipts.warehouse_id', $warehouseId);
            }
        });

        $query->orderBy('companies.name', 'asc')
            ->orderBy('sale_receipts.id', 'asc');

        if ($execute) {
            $timer_start = microtime(true);
            $recordsCount = 0;

            try {
                $cacheParams = [
                    $withTrashed ? 'true' : 'false',
                    $companyId,
                    empty($search) ? '[empty]' : $search,
                    $branchId ?? '[null]',
                    $saleId ?? '[null]',
                    $warehouseId ?? '[null]',
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ];

                $cacheKey = 'read_any_sale_receipt_'.implode('_', $cacheParams);

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

    public function read(SaleReceipt $saleReceipt): SaleReceipt
    {
        return $saleReceipt->load(['company', 'branch', 'sale', 'warehouse']);
    }

    public function create(array $data): SaleReceipt
    {
        $timer_start = microtime(true);

        try {
            $saleReceipt = new SaleReceipt();
            $saleReceipt->company_id = $data['company_id'];
            $saleReceipt->branch_id = $data['branch_id'];
            $saleReceipt->code = $this->generateUniqueCode($data['company_id'], $data['code'], null);
            $saleReceipt->sale_id = $data['sale_id'];
            $saleReceipt->warehouse_id = $data['warehouse_id'];
            $saleReceipt->save();

            $this->flushCache();

            return $saleReceipt;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(SaleReceipt $saleReceipt, array $data): SaleReceipt
    {
        $timer_start = microtime(true);

        try {
            $saleReceipt->code = $this->generateUniqueCode($saleReceipt->company_id, $data['code'], $saleReceipt->id);
            $saleReceipt->sale_id = $data['sale_id'];
            $saleReceipt->warehouse_id = $data['warehouse_id'];
            $saleReceipt->save();

            $this->flushCache();

            return $saleReceipt->refresh();
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(SaleReceipt $saleReceipt): bool
    {
        $timer_start = microtime(true);

        $retval = false;

        try {
            $retval = $saleReceipt->delete();

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
                $count = $company->saleReceipts()->withTrashed()->count() + 1 + $tryCount;
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
        $result = SaleReceipt::whereCompanyId('sale_receipts', $companyId)->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0 ? true : false;
    }
}
