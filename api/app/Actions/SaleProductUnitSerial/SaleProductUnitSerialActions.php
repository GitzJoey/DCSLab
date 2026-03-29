<?php

namespace App\Actions\SaleProductUnitSerial;

use App\DTOs\ExecuteDTO;
use App\Models\Company;
use App\Models\SaleProductUnitSerial;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class SaleProductUnitSerialActions
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
        ?int $saleProductUnitId,

        ?ExecuteDTO $execute
    ) {
        $query = SaleProductUnitSerial::select('sale_product_unit_serials.*')
            ->with([
                'company',
                'branch',
                'sale',
                'saleProductUnit',
            ])
            ->join('companies', 'companies.id', '=', 'sale_product_unit_serials.company_id')
            ->whereCompanyId('sale_product_unit_serials', $companyId)
            ->withTrashed();

        $query->where(function ($query) use ($withTrashed, $search, $branchId, $saleId, $saleProductUnitId) {
            $query->withoutTrashed();
            if ($withTrashed) $query->withTrashed();

            if ($search) {
                $query->search($search);
            }

            if ($branchId) {
                $query->where('sale_product_unit_serials.branch_id', $branchId);
            }

            if ($saleId) {
                $query->where('sale_product_unit_serials.sale_id', $saleId);
            }

            if ($saleProductUnitId) {
                $query->where('sale_product_unit_serials.sale_product_unit_id', $saleProductUnitId);
            }
        });

        $query->orderBy('companies.name', 'asc')
            ->orderBy('sale_product_unit_serials.id', 'asc');

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
                    $saleProductUnitId ?? '[null]',
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ];

                $cacheKey = 'read_any_sale_product_unit_serial_'.implode('_', $cacheParams);

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

    public function read(SaleProductUnitSerial $saleProductUnitSerial): SaleProductUnitSerial
    {
        return $saleProductUnitSerial->load([
            'company',
            'branch',
            'sale',
            'saleProductUnit',
        ]);
    }

    public function create(array $data): SaleProductUnitSerial
    {
        $timer_start = microtime(true);

        try {
            $saleProductUnitSerial = new SaleProductUnitSerial();
            $saleProductUnitSerial->company_id = $data['company_id'];
            $saleProductUnitSerial->branch_id = $data['branch_id'];
            $saleProductUnitSerial->sale_id = $data['sale_id'];
            $saleProductUnitSerial->sale_product_unit_id = $data['sale_product_unit_id'];
            $saleProductUnitSerial->serial = $data['serial'];
            $saleProductUnitSerial->save();

            $this->flushCache();

            return $saleProductUnitSerial;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(SaleProductUnitSerial $saleProductUnitSerial, array $data): SaleProductUnitSerial
    {
        $timer_start = microtime(true);

        try {
            $saleProductUnitSerial->company_id = $data['company_id'];
            $saleProductUnitSerial->branch_id = $data['branch_id'];
            $saleProductUnitSerial->sale_id = $data['sale_id'];
            $saleProductUnitSerial->sale_product_unit_id = $data['sale_product_unit_id'];
            $saleProductUnitSerial->serial = $data['serial'];
            $saleProductUnitSerial->save();

            $this->flushCache();

            return $saleProductUnitSerial->refresh();
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(SaleProductUnitSerial $saleProductUnitSerial): bool
    {
        $timer_start = microtime(true);

        $retval = false;

        try {
            $retval = $saleProductUnitSerial->delete();

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
                $count = $company->saleProductUnitSerials()->withTrashed()->count() + 1 + $tryCount;
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
        $result = SaleProductUnitSerial::whereCompanyId('sale_product_unit_serials', $companyId)->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0 ? true : false;
    }
}
