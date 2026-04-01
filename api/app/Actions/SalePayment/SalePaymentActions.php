<?php

namespace App\Actions\SalePayment;

use App\DTOs\ExecuteDTO;
use App\Models\Company;
use App\Models\SalePayment;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class SalePaymentActions
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
        ?int $cashAccountId,

        ?ExecuteDTO $execute
    ) {
        $query = SalePayment::select('sale_payments.*')
            ->with([
                'company',
                'branch',
                'sale',
                'cashAccount',
            ])
            ->join('companies', 'companies.id', '=', 'sale_payments.company_id')
            ->whereCompanyId('sale_payments', $companyId)
            ->withTrashed();

        $query->where(function ($query) use ($withTrashed, $search, $branchId, $saleId, $cashAccountId) {
            $query->withoutTrashed();
            if ($withTrashed) $query->withTrashed();

            if ($search) {
                $query->search($search);
            }

            if ($branchId) {
                $query->where('sale_payments.branch_id', $branchId);
            }

            if ($saleId) {
                $query->where('sale_payments.sale_id', $saleId);
            }

            if ($cashAccountId) {
                $query->where('sale_payments.cash_account_id', $cashAccountId);
            }
        });

        $query->orderBy('companies.name', 'asc')
            ->orderBy('sale_payments.date', 'desc')
            ->orderBy('sale_payments.id', 'asc');

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
                    $cashAccountId ?? '[null]',
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ];

                $cacheKey = 'read_any_sale_payment_'.implode('_', $cacheParams);

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

    public function read(SalePayment $salePayment): SalePayment
    {
        return $salePayment->load([
            'company',
            'branch',
            'sale',
            'cashAccount',
        ]);
    }

    public function create(array $data): SalePayment
    {
        $timer_start = microtime(true);

        try {
            $salePayment = new SalePayment();
            $salePayment->company_id = $data['company_id'];
            $salePayment->branch_id = $data['branch_id'];
            $salePayment->sale_id = $data['sale_id'];
            $salePayment->code = $this->generateUniqueCode($data['company_id'], $data['code'], null);
            $salePayment->date = $data['date'];
            $salePayment->cash_account_id = $data['cash_account_id'];
            $salePayment->amount = $data['amount'];
            $salePayment->remarks = $data['remarks'];
            $salePayment->save();

            $this->flushCache();

            return $salePayment;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(SalePayment $salePayment, array $data): SalePayment
    {
        $timer_start = microtime(true);

        try {
            $salePayment->company_id = $data['company_id'];
            $salePayment->branch_id = $data['branch_id'];
            $salePayment->sale_id = $data['sale_id'];
            $salePayment->code = $this->generateUniqueCode($salePayment->company_id, $data['code'], $salePayment->id);
            $salePayment->date = $data['date'];
            $salePayment->cash_account_id = $data['cash_account_id'];
            $salePayment->amount = $data['amount'];
            $salePayment->remarks = $data['remarks'];
            $salePayment->save();

            $this->flushCache();

            return $salePayment->refresh();
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(SalePayment $salePayment): bool
    {
        $timer_start = microtime(true);

        $retval = false;

        try {
            $retval = $salePayment->delete();

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
                $count = $company->salePayments()->withTrashed()->count() + 1 + $tryCount;
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
        $result = SalePayment::whereCompanyId('sale_payments', $companyId)->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0 ? true : false;
    }
}
