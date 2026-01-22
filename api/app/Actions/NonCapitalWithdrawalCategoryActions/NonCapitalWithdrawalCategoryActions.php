<?php

namespace App\Actions\NonCapitalWithdrawalCategory;

use App\Models\Company;
use App\Models\NonCapitalWithdrawalCategory;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class NonCapitalWithdrawalCategoryActions
{
    use CacheHelper;
    use LoggerHelper;

    public function __construct()
    {
    }

    public function isUniqueName(int $companyId, string $name, ?int $exceptId = null): bool
    {
        $query = NonCapitalWithdrawalCategory::whereCompanyId($companyId)->whereName($name);

        if ($exceptId) {
            $query->where('id', '<>', $exceptId);
        }

        return $query->doesntExist();
    }

    public function create(array $data): NonCapitalWithdrawalCategory
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $nonCapitalWithdrawalCategory = new NonCapitalWithdrawalCategory();
            $nonCapitalWithdrawalCategory->company_id = $data['company_id'];
            $nonCapitalWithdrawalCategory->code = $this->generateUniqueCode($data['company_id'], $data['code'], null);
            $nonCapitalWithdrawalCategory->name = $data['name'];
            $nonCapitalWithdrawalCategory->save();

            DB::commit();

            $this->flushCache();

            return $nonCapitalWithdrawalCategory;
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
        $query = NonCapitalWithdrawalCategory::with('company')->withTrashed()
            ->withAggregate('company', 'name')
            ->where(function ($query) use ($withTrashed, $search, $companyId) {
                if ($withTrashed == true) {
                    $query = $query->withTrashed();
                } else {
                    $query = $query->withoutTrashed();
                }

                if ($search) {
                    $query->search($search);
                }

                $query->whereCompanyId($companyId);
            });

        $query->orderBy('company_name', 'asc')
            ->orderBy('name', 'asc');

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

    public function read(NonCapitalWithdrawalCategory $nonCapitalWithdrawalCategory): NonCapitalWithdrawalCategory
    {
        return $nonCapitalWithdrawalCategory->with('company')->first();
    }

    public function getAllActiveNonCapitalWithdrawalCategory(
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

    public function update(NonCapitalWithdrawalCategory $nonCapitalWithdrawalCategory, array $data): NonCapitalWithdrawalCategory
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $nonCapitalWithdrawalCategory->code = $this->generateUniqueCode($nonCapitalWithdrawalCategory->company_id, $data['code'], $nonCapitalWithdrawalCategory->id);
            $nonCapitalWithdrawalCategory->name = $data['name'];
            $nonCapitalWithdrawalCategory->save();

            DB::commit();

            $this->flushCache();

            return $nonCapitalWithdrawalCategory->refresh();
        } catch (Exception $e) {
            DB::rollBack();
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(NonCapitalWithdrawalCategory $nonCapitalWithdrawalCategory): bool
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        $retval = false;

        try {
            $retval = $nonCapitalWithdrawalCategory->delete();

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
                $count = $company->nonCapitalWithdrawalCategories()->withTrashed()->count() + 1 + $tryCount;
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
        $result = NonCapitalWithdrawalCategory::whereCompanyId($companyId)->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0 ? true : false;
    }
}
