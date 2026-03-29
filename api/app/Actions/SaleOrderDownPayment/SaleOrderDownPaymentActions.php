<?php

namespace App\Actions\SaleOrderDownPayment;

use App\DTOs\ExecuteDTO;
use App\Models\Company;
use App\Models\SaleOrderDownPayment;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class SaleOrderDownPaymentActions
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
        ?int $salesOrderId,
        ?int $cashAccountId,

        ?ExecuteDTO $execute
    ) {
        $query = SaleOrderDownPayment::select('sale_order_down_payments.*')
            ->with(['company', 'branch', 'salesOrder', 'cashAccount'])
            ->join('companies', 'companies.id', '=', 'sale_order_down_payments.company_id')
            ->whereCompanyId('sale_order_down_payments', $companyId)
            ->withTrashed();

        $query->where(function ($query) use ($withTrashed, $search, $branchId, $salesOrderId, $cashAccountId) {
            $query->withoutTrashed();
            if ($withTrashed) $query->withTrashed();

            if ($search) {
                $query->search($search);
            }

            if ($branchId) {
                $query->where('sale_order_down_payments.branch_id', $branchId);
            }

            if ($salesOrderId) {
                $query->where('sale_order_down_payments.sale_order_id', $salesOrderId);
            }

            if ($cashAccountId) {
                $query->where('sale_order_down_payments.cash_account_id', $cashAccountId);
            }
        });

        $query->orderBy('companies.name', 'asc')
            ->orderBy('sale_order_down_payments.date', 'desc');

        if ($execute) {
            $timer_start = microtime(true);
            $recordsCount = 0;

            try {
                $cacheParams = [
                    $withTrashed ? 'true' : 'false',
                    $companyId,
                    empty($search) ? '[empty]' : $search,
                    $branchId ?? '[null]',
                    $salesOrderId ?? '[null]',
                    $cashAccountId ?? '[null]',
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ];

                $cacheKey = 'read_any_sale_order_down_payment_'.implode('_', $cacheParams);

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

    public function read(SaleOrderDownPayment $saleOrderDownPayment): SaleOrderDownPayment
    {
        return $saleOrderDownPayment->load(['company', 'branch', 'salesOrder', 'cashAccount']);
    }

    public function create(array $data): SaleOrderDownPayment
    {
        $timer_start = microtime(true);

        try {
            $saleOrderDownPayment = new SaleOrderDownPayment();
            $saleOrderDownPayment->company_id = $data['company_id'];
            $saleOrderDownPayment->branch_id = $data['branch_id'];
            $saleOrderDownPayment->sale_order_id = $data['sale_order_id'];
            $saleOrderDownPayment->code = $this->generateUniqueCode($data['company_id'], $data['code'], null);
            $saleOrderDownPayment->date = $data['date'];
            $saleOrderDownPayment->cash_account_id = $data['cash_account_id'];
            $saleOrderDownPayment->amount = $data['amount'];
            $saleOrderDownPayment->remarks = $data['remarks'];
            $saleOrderDownPayment->save();

            $this->flushCache();

            return $saleOrderDownPayment;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(SaleOrderDownPayment $saleOrderDownPayment, array $data): SaleOrderDownPayment
    {
        $timer_start = microtime(true);

        try {
            $saleOrderDownPayment->code = $this->generateUniqueCode($saleOrderDownPayment->company_id, $data['code'], $saleOrderDownPayment->id);
            $saleOrderDownPayment->date = $data['date'];
            $saleOrderDownPayment->cash_account_id = $data['cash_account_id'];
            $saleOrderDownPayment->amount = $data['amount'];
            $saleOrderDownPayment->remarks = $data['remarks'];
            $saleOrderDownPayment->save();

            $this->flushCache();

            return $saleOrderDownPayment->refresh();
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(SaleOrderDownPayment $saleOrderDownPayment): bool
    {
        $timer_start = microtime(true);

        $retval = false;

        try {
            $retval = $saleOrderDownPayment->delete();

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
                $count = $company->saleOrderDownPayments()->withTrashed()->count() + 1 + $tryCount;
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
        $result = SaleOrderDownPayment::whereCompanyId('sale_order_down_payments', $companyId)->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0 ? true : false;
    }
}
