<?php

namespace App\Actions\PurchaseAdditionalCost;

use App\Models\Company;
use App\Models\PurchaseAdditionalCost;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PurchaseAdditionalCostActions
{
    use CacheHelper;
    use LoggerHelper;

    public function __construct()
    {
    }

    public function create(array $data): PurchaseAdditionalCost
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $purchaseAdditionalCost = new PurchaseAdditionalCost();
            $purchaseAdditionalCost->company_id = $data['company_id'];
            $purchaseAdditionalCost->branch_id = $data['branch_id'];
            $purchaseAdditionalCost->purchase_id = $data['purchase_id'];
            $purchaseAdditionalCost->code = $this->generateUniqueCode($data['company_id'], $data['code'], null);
            $purchaseAdditionalCost->date = $data['date'];
            $purchaseAdditionalCost->category_id = $data['category_id'];
            $purchaseAdditionalCost->amount = $data['amount'];
            $purchaseAdditionalCost->remarks = $data['remarks'];
            $purchaseAdditionalCost->save();

            DB::commit();

            $this->flushCache();

            return $purchaseAdditionalCost;
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
        $query = PurchaseAdditionalCost::select('purchase_additional_costs.*')->withTrashed()
            ->with(['company'])
            ->join('companies', 'companies.id', '=', 'purchase_additional_costs.company_id')
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
            ->orderBy('purchase_additional_costs.date', 'dsc');

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

    public function read(PurchaseAdditionalCost $purchaseAdditionalCost): PurchaseAdditionalCost
    {
        return $purchaseAdditionalCost->load('company')->first();
    }

    public function getAllActivePurchaseAdditionalCost(
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

    public function update(PurchaseAdditionalCost $purchaseAdditionalCost, array $data): PurchaseAdditionalCost
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $purchaseAdditionalCost->company_id = $data['company_id'];
            $purchaseAdditionalCost->branch_id = $data['branch_id'];
            $purchaseAdditionalCost->purchase_id = $data['purchase_id'];
            $purchaseAdditionalCost->code = $this->generateUniqueCode($purchaseAdditionalCost->company_id, $data['code'], $purchaseAdditionalCost->id);
            $purchaseAdditionalCost->date = $data['date'];
            $purchaseAdditionalCost->category_id = $data['category_id'];
            $purchaseAdditionalCost->amount = $data['amount'];
            $purchaseAdditionalCost->remarks = $data['remarks'];
            $purchaseAdditionalCost->save();

            DB::commit();

            $this->flushCache();

            return $purchaseAdditionalCost->refresh();
        } catch (Exception $e) {
            DB::rollBack();
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(PurchaseAdditionalCost $purchaseAdditionalCost): bool
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        $retval = false;

        try {
            $retval = $purchaseAdditionalCost->delete();

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
                $count = $company->purchaseAdditionalCosts()->withTrashed()->count() + 1 + $tryCount;
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
        $result = PurchaseAdditionalCost::whereCompanyId($companyId)->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0 ? true : false;
    }
}
