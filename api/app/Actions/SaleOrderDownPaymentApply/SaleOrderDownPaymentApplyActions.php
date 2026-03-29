<?php

namespace App\Actions\SaleOrderDownPaymentApply;

use App\DTOs\ExecuteDTO;
use App\Models\Company;
use App\Models\SaleOrderDownPaymentApply;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class SaleOrderDownPaymentApplyActions
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
        ?int $saleOrderId,
        ?int $cashAccountId,

        ?ExecuteDTO $execute
    ) {
        $query = SaleOrderDownPaymentApply::select('sale_order_down_payment_applies.*')
            ->with(['company', 'branch', 'saleOrder', 'cashAccount'])
            ->join('companies', 'companies.id', '=', 'sale_order_down_payment_applies.company_id')
            ->whereCompanyId('sale_order_down_payment_applies', $companyId)
            ->withTrashed();

        $query->where(function ($query) use ($withTrashed, $search, $branchId, $saleOrderId, $cashAccountId) {
            $query->withoutTrashed();
            if ($withTrashed) $query->withTrashed();

            if ($search) {
                $query->search($search);
            }

            if ($branchId) {
                $query->where('sale_order_down_payment_applies.branch_id', $branchId);
            }

            if ($saleOrderId) {
                $query->where('sale_order_down_payment_applies.sale_order_id', $saleOrderId);
            }

            if ($cashAccountId) {
                $query->where('sale_order_down_payment_applies.cash_account_id', $cashAccountId);
            }
        });

        $query->orderBy('companies.name', 'asc')
            ->orderBy('sale_order_down_payment_applies.date', 'desc');

        if ($execute) {
            $timer_start = microtime(true);
            $recordsCount = 0;

            try {
                $cacheParams = [
                    $withTrashed ? 'true' : 'false',
                    $companyId,
                    empty($search) ? '[empty]' : $search,
                    $branchId ?? '[null]',
                    $saleOrderId ?? '[null]',
                    $cashAccountId ?? '[null]',
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ];

                $cacheKey = 'read_any_sale_order_down_payment_apply_'.implode('_', $cacheParams);

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

    public function read(SaleOrderDownPaymentApply $saleOrderDownPaymentApply): SaleOrderDownPaymentApply
    {
        return $saleOrderDownPaymentApply->load(['company', 'branch', 'saleOrder', 'cashAccount']);
    }

    public function create(array $data): SaleOrderDownPaymentApply
    {
        $timer_start = microtime(true);

        try {
            $saleOrderDownPaymentApply = new SaleOrderDownPaymentApply();
            $saleOrderDownPaymentApply->company_id = $data['company_id'];
            $saleOrderDownPaymentApply->branch_id = $data['branch_id'];
            $saleOrderDownPaymentApply->sale_order_id = $data['sale_order_id'];
            $saleOrderDownPaymentApply->code = $this->generateUniqueCode($data['company_id'], $data['code'], null);
            $saleOrderDownPaymentApply->date = $data['date'];
            $saleOrderDownPaymentApply->cash_account_id = $data['cash_account_id'];
            $saleOrderDownPaymentApply->amount = $data['amount'];
            $saleOrderDownPaymentApply->remarks = $data['remarks'];
            $saleOrderDownPaymentApply->save();

            $this->flushCache();

            return $saleOrderDownPaymentApply;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(SaleOrderDownPaymentApply $saleOrderDownPaymentApply, array $data): SaleOrderDownPaymentApply
    {
        $timer_start = microtime(true);

        try {
            $saleOrderDownPaymentApply->code = $this->generateUniqueCode($saleOrderDownPaymentApply->company_id, $data['code'], $saleOrderDownPaymentApply->id);
            $saleOrderDownPaymentApply->date = $data['date'];
            $saleOrderDownPaymentApply->cash_account_id = $data['cash_account_id'];
            $saleOrderDownPaymentApply->amount = $data['amount'];
            $saleOrderDownPaymentApply->remarks = $data['remarks'];
            $saleOrderDownPaymentApply->save();

            $this->flushCache();

            return $saleOrderDownPaymentApply->refresh();
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(SaleOrderDownPaymentApply $saleOrderDownPaymentApply): bool
    {
        $timer_start = microtime(true);

        $retval = false;

        try {
            $retval = $saleOrderDownPaymentApply->delete();

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
                $count = $company->saleOrderDownPaymentApplies()->withTrashed()->count() + 1 + $tryCount;
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
        $result = SaleOrderDownPaymentApply::whereCompanyId('sale_order_down_payment_applies', $companyId)->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0 ? true : false;
    }
}
