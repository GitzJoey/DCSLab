<?php

namespace App\Actions\Supplier;

use App\Models\Company;
use App\Models\Supplier;
use App\Models\User;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SupplierActions
{
    use CacheHelper;
    use LoggerHelper;

    public function __construct()
    {
    }

    public function create(array $data): Supplier
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $supplier = new Supplier();
            $supplier->company_id = $data['company_id'];
            $supplier->user_id = $data['user_id'];
            $supplier->code = $this->generateUniqueCode($data['company_id'], $data['code'], null);
            $supplier->name = $data['name'];
            $supplier->address = $data['address'];
            $supplier->city = $data['city'];
            $supplier->payment_term_type = $data['payment_term_type'];
            $supplier->payment_term = $data['payment_term'];
            $supplier->taxable_enterprise = $data['taxable_enterprise'];
            $supplier->tax_id = $data['tax_id'];
            $supplier->status = $data['status'];
            $supplier->remarks = $data['remarks'];
            $supplier->save();

            DB::commit();

            $this->flushCache();

            return $supplier;
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
        User $user,
        ?bool $withTrashed,

        ?string $search,
        int $companyId,

        ?int $limit
    ) {
        $query = Supplier::select('suppliers.*')->withTrashed()
            ->with(['company', 'user'])
            ->join('companies', 'companies.id', '=', 'suppliers.company_id')
            ->where(function ($query) use ($withTrashed, $search, $companyId, $user) {
                if ($withTrashed == true) {
                    $query->withTrashed();
                } else {
                    $query->withoutTrashed();
                }

                if ($search) {
                    $query->search($search);
                }

                $query = $query->whereIn('id', $user->suppliers()->pluck('supplier_id'));

                $query->whereCompanyId($companyId);
            });

        $query->orderBy('companies.name', 'asc')
            ->orderBy('suppliers.name', 'asc');

        if ($limit) {
            $query->limit($limit);
        }

        return $query;
    }

    public function readAny(
        User $user,
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
            $cacheKey = 'readAny_'.$companyId.'-'.$user->id.'-'.$cacheSearch.'-'.$paginate.'-'.$page.'-'.$perPage;
            if ($useCache === true) {
                $cacheResult = $this->readFromCache($cacheKey);

                if (! is_null($cacheResult)) {
                    return $cacheResult;
                }
            }

            $result = null;

            $query = $this->readAnyQuery(
                user: $user,
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

    public function read(Supplier $supplier): Supplier
    {
        return $supplier->load('company', 'user')->first();
    }

    public function getAllActiveSupplier(
        User $user,
        ?bool $withTrashed,

        ?string $search,
        int $companyId,
        ?array $includeIds,

        ?int $limit
    ) {
        $timer_start = microtime(true);

        try {
            $query = $this->readAnyQuery(
                user: $user,
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

    public function update(Supplier $supplier, array $data): Supplier
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $supplier->code = $this->generateUniqueCode($supplier->company_id, $data['code'], $supplier->id);
            $supplier->name = $data['name'];
            $supplier->address = $data['address'];
            $supplier->city = $data['city'];
            $supplier->payment_term_type = $data['payment_term_type'];
            $supplier->payment_term = $data['payment_term'];
            $supplier->taxable_enterprise = $data['taxable_enterprise'];
            $supplier->tax_id = $data['tax_id'];
            $supplier->status = $data['status'];
            $supplier->remarks = $data['remarks'];
            $supplier->save();

            DB::commit();

            $this->flushCache();

            return $supplier->refresh();
        } catch (Exception $e) {
            DB::rollBack();
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(Supplier $supplier): bool
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        $retval = false;

        try {
            $retval = $supplier->delete();

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
                $count = $company->suppliers()->withTrashed()->count() + 1 + $tryCount;
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
        $result = Supplier::whereCompanyId($companyId)->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0 ? true : false;
    }

    public function isUniqueName(int $companyId, string $name, ?int $exceptId): bool
    {
        $result = Supplier::whereCompanyId($companyId)->where('name', '=', $name);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0 ? true : false;
    }
}
