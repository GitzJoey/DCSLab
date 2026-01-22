<?php

namespace App\Actions\PurchaseOrderDownPaymentApply;

use App\Models\Company;
use App\Models\PurchaseOrderDownPaymentApply;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PurchaseOrderDownPaymentApplyActions
{
    use CacheHelper;
    use LoggerHelper;

    public function __construct()
    {
    }

    public function create(array $data): PurchaseOrderDownPaymentApply
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $purchaseOrderDownPaymentApply = new PurchaseOrderDownPaymentApply();
            $purchaseOrderDownPaymentApply->company_id = $data['company_id'];
            $purchaseOrderDownPaymentApply->branch_id = $data['branch_id'];
            $purchaseOrderDownPaymentApply->purchase_order_id = $data['purchase_order_id'];
            $purchaseOrderDownPaymentApply->code = $this->generateUniqueCode($data['company_id'], $data['code'], null);
            $purchaseOrderDownPaymentApply->date = $data['date'];
            $purchaseOrderDownPaymentApply->cash_account_id = $data['cash_account_id'];
            $purchaseOrderDownPaymentApply->amount = $data['amount'];
            $purchaseOrderDownPaymentApply->remarks = $data['remarks'];
            $purchaseOrderDownPaymentApply->save();

            DB::commit();

            $this->flushCache();

            return $purchaseOrderDownPaymentApply;
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
        $query = PurchaseOrderDownPaymentApply::select('purchase_order_down_payment_applies.*')->withTrashed()
            ->with(['company'])
            ->join('companies', 'companies.id', '=', 'purchase_order_down_payment_applies.company_id')
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
            ->orderBy('purchase_order_down_payment_applies.date', 'dsc');

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

    public function read(PurchaseOrderDownPaymentApply $purchaseOrderDownPaymentApply): PurchaseOrderDownPaymentApply
    {
        return $purchaseOrderDownPaymentApply->load('company')->first();
    }

    public function getAllActivePurchaseOrderDownPaymentApply(
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

    public function update(PurchaseOrderDownPaymentApply $purchaseOrderDownPaymentApply, array $data): PurchaseOrderDownPaymentApply
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $purchaseOrderDownPaymentApply->code = $this->generateUniqueCode($purchaseOrderDownPaymentApply->company_id, $data['code'], $purchaseOrderDownPaymentApply->id);
            $purchaseOrderDownPaymentApply->date = $data['date'];
            $purchaseOrderDownPaymentApply->cash_account_id = $data['cash_account_id'];
            $purchaseOrderDownPaymentApply->amount = $data['amount'];
            $purchaseOrderDownPaymentApply->remarks = $data['remarks'];
            $purchaseOrderDownPaymentApply->save();

            DB::commit();

            $this->flushCache();

            return $purchaseOrderDownPaymentApply->refresh();
        } catch (Exception $e) {
            DB::rollBack();
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(PurchaseOrderDownPaymentApply $purchaseOrderDownPaymentApply): bool
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        $retval = false;

        try {
            $retval = $purchaseOrderDownPaymentApply->delete();

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
                $count = $company->purchaseOrderDownPaymentApplies()->withTrashed()->count() + 1 + $tryCount;
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
        $result = PurchaseOrderDownPaymentApply::whereCompanyId($companyId)->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0 ? true : false;
    }
}
