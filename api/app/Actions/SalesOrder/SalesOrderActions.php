<?php

namespace App\Actions\SalesOrder;

use App\Models\Company;
use App\Models\SalesOrder;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SalesOrderActions
{
    use CacheHelper;
    use LoggerHelper;

    public function __construct()
    {
    }

    public function create(array $data): SalesOrder
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $salesOrder = new SalesOrder();
            $salesOrder->company_id = $data['company_id'];
            $salesOrder->branch_id = $data['branch_id'];
            $salesOrder->code = $this->generateUniqueCode($data['company_id'], $data['code'], null);
            $salesOrder->customer_id = $data['customer_id'];
            $salesOrder->customer_address_id = $data['customer_address_id'];
            $salesOrder->remarks = $data['remarks'];
            $salesOrder->is_has_invoice = $data['is_has_invoice'];
            $salesOrder->is_sent = $data['is_sent'];
            $salesOrder->total = $data['total'];
            $salesOrder->global_discount_rate = $data['global_discount_rate'];
            $salesOrder->global_discount_fixed = $data['global_discount_fixed'];
            $salesOrder->grand_total = $data['grand_total'];
            $salesOrder->down_payment = $data['down_payment'];
            $salesOrder->down_payment_due_days = $data['down_payment_due_days'];
            $salesOrder->down_payment_applied = $data['down_payment_applied'];
            $salesOrder->down_payment_remaining = $data['down_payment_remaining'];
            $salesOrder->is_down_payment_paid_off = $data['is_down_payment_paid_off'];
            $salesOrder->save();

            DB::commit();

            $this->flushCache();

            return $salesOrder;
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
        $query = SalesOrder::select('sales_orders.*')->withTrashed()
            ->with(['company'])
            ->join('companies', 'companies.id', '=', 'sales_orders.company_id')
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
            ->orderBy('sales_orders.remarks', 'asc');

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

    public function read(SalesOrder $salesOrder): SalesOrder
    {
        return $salesOrder->load('company')->first();
    }

    public function getAllActiveSalesOrder(
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

    public function update(SalesOrder $salesOrder, array $data): SalesOrder
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $salesOrder->code = $this->generateUniqueCode($salesOrder->company_id, $data['code'], $salesOrder->id);
            $salesOrder->customer_id = $data['customer_id'];
            $salesOrder->customer_address_id = $data['customer_address_id'];
            $salesOrder->remarks = $data['remarks'];
            $salesOrder->is_has_invoice = $data['is_has_invoice'];
            $salesOrder->is_sent = $data['is_sent'];
            $salesOrder->total = $data['total'];
            $salesOrder->global_discount_rate = $data['global_discount_rate'];
            $salesOrder->global_discount_fixed = $data['global_discount_fixed'];
            $salesOrder->grand_total = $data['grand_total'];
            $salesOrder->down_payment = $data['down_payment'];
            $salesOrder->down_payment_due_days = $data['down_payment_due_days'];
            $salesOrder->down_payment_applied = $data['down_payment_applied'];
            $salesOrder->down_payment_remaining = $data['down_payment_remaining'];
            $salesOrder->is_down_payment_paid_off = $data['is_down_payment_paid_off'];
            $salesOrder->remarks = $data['remarks'];
            $salesOrder->save();

            DB::commit();

            $this->flushCache();

            return $salesOrder->refresh();
        } catch (Exception $e) {
            DB::rollBack();
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(SalesOrder $salesOrder): bool
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        $retval = false;

        try {
            $retval = $salesOrder->delete();

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
                $count = $company->salesOrders()->withTrashed()->count() + 1 + $tryCount;
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
        $result = SalesOrder::whereCompanyId($companyId)->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0 ? true : false;
    }
}
