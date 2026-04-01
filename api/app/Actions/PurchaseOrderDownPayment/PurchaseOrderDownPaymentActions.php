<?php

namespace App\Actions\PurchaseOrderDownPayment;

use App\Models\Company;
use App\Models\PurchaseOrderDownPayment;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;

class PurchaseOrderDownPaymentActions
{
    use CacheHelper;
    use LoggerHelper;

    public function __construct()
    {
    }

    public function readAny(
        ?bool $useCache,
        ?bool $withTrashed,
        ?string $search,
        int $companyId,
        ?int $branchId,

        ?int $purchaseOrderId,
        ?int $cashAccountId,

        bool $paginate,
        ?int $page,
        ?int $perPage,
        ?int $limit
    ): Paginator|Collection {
        $timer_start = microtime(true);
        $recordsCount = 0;

        try {
            $cacheSearch = empty($search) ? '[empty]' : $search;
            $cacheKey = implode('-', [
                'readAny_'.$companyId,
                $cacheSearch,
                $branchId ?? '[null]',
                $purchaseOrderId ?? '[null]',
                $cashAccountId ?? '[null]',
                $paginate ? 'true' : 'false',
                $page ?? '[null]',
                $perPage ?? '[null]',
                $limit ?? '[null]',
            ]);
            if ($useCache === true) {
                $cacheResult = $this->readFromCache($cacheKey);

                if (! is_null($cacheResult)) {
                    return $cacheResult;
                }
            }

            $query = PurchaseOrderDownPayment::select('purchase_order_down_payments.*')->withTrashed()
                ->with(['company'])
                ->join('companies', 'companies.id', '=', 'purchase_order_down_payments.company_id')
                ->where(function ($query) use ($withTrashed, $search, $companyId, $branchId, $purchaseOrderId, $cashAccountId) {
                    if ($withTrashed == true) {
                        $query = $query->withTrashed();
                    } else {
                        $query = $query->withoutTrashed();
                    }

                    if ($search) {
                        $query->search($search);
                    }

                    if ($branchId) {
                        $query->where('purchase_order_down_payments.branch_id', $branchId);
                    }

                    if ($purchaseOrderId) {
                        $query->where('purchase_order_down_payments.purchase_order_id', $purchaseOrderId);
                    }

                    if ($cashAccountId) {
                        $query->where('purchase_order_down_payments.cash_account_id', $cashAccountId);
                    }

                    $query->whereCompanyId('purchase_order_down_payments', $companyId);
                })
                ->orderBy('companies.name', 'asc')
                ->orderBy('purchase_order_down_payments.date', 'desc');

            if (! $paginate && $limit) {
                $query->limit($limit);
            }

            $result = $paginate
                ? $query->paginate(perPage: $perPage, page: $page)
                : $query->get();

            $recordsCount = $result->count();

            if ($useCache === true) {
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

    public function read(PurchaseOrderDownPayment $purchaseOrderDownPayment): PurchaseOrderDownPayment
    {
        return $purchaseOrderDownPayment->load('company')->first();
    }

    public function create(array $data): PurchaseOrderDownPayment
    {
        $timer_start = microtime(true);

        try {
            $purchaseOrderDownPayment = new PurchaseOrderDownPayment();
            $purchaseOrderDownPayment->company_id = $data['company_id'];
            $purchaseOrderDownPayment->branch_id = $data['branch_id'];
            $purchaseOrderDownPayment->purchase_order_id = $data['purchase_order_id'];
            $purchaseOrderDownPayment->code = $this->generateUniqueCode($data['company_id'], $data['code'], null);
            $purchaseOrderDownPayment->date = $data['date'];
            $purchaseOrderDownPayment->cash_account_id = $data['cash_account_id'];
            $purchaseOrderDownPayment->amount = $data['amount'];
            $purchaseOrderDownPayment->remarks = $data['remarks'];
            $purchaseOrderDownPayment->save();

            $this->flushCache();

            return $purchaseOrderDownPayment;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(PurchaseOrderDownPayment $purchaseOrderDownPayment, array $data): PurchaseOrderDownPayment
    {
        $timer_start = microtime(true);

        try {
            $purchaseOrderDownPayment->cash_account_id = $data['cash_account_id'];
            $purchaseOrderDownPayment->code = $this->generateUniqueCode($purchaseOrderDownPayment->company_id, $data['code'], $purchaseOrderDownPayment->id);
            $purchaseOrderDownPayment->date = $data['date'];
            $purchaseOrderDownPayment->cash_account_id = $data['cash_account_id'];
            $purchaseOrderDownPayment->amount = $data['amount'];
            $purchaseOrderDownPayment->remarks = $data['remarks'];
            $purchaseOrderDownPayment->save();

            $this->flushCache();

            return $purchaseOrderDownPayment->refresh();
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(PurchaseOrderDownPayment $purchaseOrderDownPayment): bool
    {
        $timer_start = microtime(true);

        $retval = false;

        try {
            $retval = $purchaseOrderDownPayment->delete();

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
                $count = $company->purchaseOrderDownPayments()->withTrashed()->count() + 1 + $tryCount;
                $code = 'WH'.str_pad($count, 3, '0', STR_PAD_LEFT);
                $tryCount++;
            } while (! $this->isUniqueCode($companyId, $code, $exceptId));

            return $code;
        }

        return $code;
    }

    public function isUniqueCode(int $companyId, string $code, ?int $exceptId): bool
    {
        $result = PurchaseOrderDownPayment::whereCompanyId('purchase_order_down_payments', $companyId)->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0;
    }
}
