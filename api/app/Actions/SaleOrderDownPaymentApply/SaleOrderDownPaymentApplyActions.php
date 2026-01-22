<?php

namespace App\Actions\SaleOrderDownPaymentApply;

use App\Models\Company;
use App\Models\SaleOrderDownPaymentApply;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SaleOrderDownPaymentApplyActions
{
    use CacheHelper;
    use LoggerHelper;

    public function __construct()
    {
    }

    public function create(array $data): SaleOrderDownPaymentApply
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $saleOrderDownPaymentApply = new SaleOrderDownPaymentApply();
            $saleOrderDownPaymentApply->company_id = $data['company_id'];
            $saleOrderDownPaymentApply->branch_id = $data['branch_id'];
            $saleOrderDownPaymentApply->sales_order_id = $data['sales_order_id'];
            $saleOrderDownPaymentApply->code = $this->generateUniqueCode($data['company_id'], $data['code'], null);
            $saleOrderDownPaymentApply->date = $data['date'];
            $saleOrderDownPaymentApply->cash_account_id = $data['cash_account_id'];
            $saleOrderDownPaymentApply->amount = $data['amount'];
            $saleOrderDownPaymentApply->remarks = $data['remarks'];
            $saleOrderDownPaymentApply->save();

            DB::commit();

            $this->flushCache();

            return $saleOrderDownPaymentApply;
        } catch (Exception $e) {
            DB::rollBack();
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    private function readAnyQuery(
        ?bool $withTrashed,

        ?string $search,
        int $companyId,

        ?int $limit
    ) {
        $query = SaleOrderDownPaymentApply::select('sale_order_down_payment_applies.*')->withTrashed()
            ->with(['company'])
            ->join('companies', 'companies.id', '=', 'sale_order_down_payment_applies.company_id')
            ->where(function ($query) use ($withTrashed, $search, $companyId) {
                if ($withTrashed == true) {
                    $query->withTrashed();
                } else {
                    $query->withoutTrashed();
                }

                if ($search) {
                    $query->search($search);
                }

                $query->whereCompanyId($companyId);
            });

        $query->orderBy('companies.name', 'asc')
            ->orderBy('sale_order_down_payment_applies.date', 'dsc');

        if ($limit) {
            $query->limit($limit);
        }

        return $query;
    }

    public function readAny(
        ?bool $useCache,
        ?bool $withTrashed,

        ?string $search,
        int $companyId,

        bool $paginate,
        ?int $page,
        ?int $perPage,
        ?int $limit
    ): Paginator|Collection {
        $timer_start = microtime(true);
        $recordsCount = 0;

        try {
            $cacheSearch = empty($search) ? '[empty]' : $search;
            $cacheKey = 'readAny_'.$companyId.'-'.$cacheSearch.'-'.$paginate.'-'.$page.'-'.$perPage;
            if ($useCache === true) {
                $cacheResult = $this->readFromCache($cacheKey);

                if (! is_null($cacheResult)) {
                    return $cacheResult;
                }
            }

            $result = null;

            $query = $this->readAnyQuery(
                withTrashed: $withTrashed,
                search: $search,
                companyId: $companyId,
                limit: $paginate ? null : $limit
            );

            if ($paginate) {
                $result = $query->paginate(perPage: $perPage, page: $page);
            } else {
                $result = $query->get();
            }

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

    public function read(SaleOrderDownPaymentApply $saleOrderDownPaymentApply): SaleOrderDownPaymentApply
    {
        return $saleOrderDownPaymentApply->load('company')->first();
    }

    public function getAllActiveSaleOrderDownPaymentApply(
        ?array $with,
        ?bool $withTrashed,

        ?string $search,
        int $companyId,
        ?array $includeIds,

        ?int $limit
    ) {
        $timer_start = microtime(true);

        try {
            $query = $this->readAnyQuery(
                withTrashed: $withTrashed,

                search: $search,
                companyId: $companyId,

                limit: $limit
            );

            if ($includeIds) {
                $query = $query->orWhereIn('id', $includeIds);

                $orders = $query->getQuery()->orders;
                $query->reorder();
                $query->orderByRaw('FIELD(id, '.implode(',', $includeIds).') desc');
                if (! empty($orders)) {
                    foreach ($orders as $order) {
                        $query->orderBy($order['column'], $order['direction']);
                    }
                }
            }

            return $query->get();
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
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $saleOrderDownPaymentApply->code = $this->generateUniqueCode($saleOrderDownPaymentApply->company_id, $data['code'], $saleOrderDownPaymentApply->id);
            $saleOrderDownPaymentApply->date = $data['date'];
            $saleOrderDownPaymentApply->cash_account_id = $data['cash_account_id'];
            $saleOrderDownPaymentApply->amount = $data['amount'];
            $saleOrderDownPaymentApply->remarks = $data['remarks'];
            $saleOrderDownPaymentApply->save();

            DB::commit();

            $this->flushCache();

            return $saleOrderDownPaymentApply->refresh();
        } catch (Exception $e) {
            DB::rollBack();
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(SaleOrderDownPaymentApply $saleOrderDownPaymentApply): bool
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        $retval = false;

        try {
            $retval = $saleOrderDownPaymentApply->delete();

            DB::commit();

            $this->flushCache();

            return $retval;
        } catch (Exception $e) {
            DB::rollBack();
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
        $result = SaleOrderDownPaymentApply::whereCompanyId($companyId)->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0 ? true : false;
    }
}
