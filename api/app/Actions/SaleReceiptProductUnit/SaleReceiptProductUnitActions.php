<?php

namespace App\Actions\SaleReceiptProductUnit;

use App\Models\Company;
use App\Models\SaleReceiptProductUnit;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SaleReceiptProductUnitActions
{
    use CacheHelper;
    use LoggerHelper;

    public function __construct()
    {
    }

    public function create(array $data): SaleReceiptProductUnit
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $saleReceiptProductUnit = new SaleReceiptProductUnit();
            $saleReceiptProductUnit->company_id = $data['company_id'];
            $saleReceiptProductUnit->sale_id = $data['sale_id'];
            $saleReceiptProductUnit->sale_receipt_id = $data['sale_receipt_id'];
            $saleReceiptProductUnit->qty = $data['qty'];
            $saleReceiptProductUnit->product_id = $data['product_id'];
            $saleReceiptProductUnit->product_unit_id = $data['product_unit_id'];
            $saleReceiptProductUnit->product_unit_amount_per_unit = $data['product_unit_amount_per_unit'];
            $saleReceiptProductUnit->product_unit_amount_total = $data['product_unit_amount_total'];
            $saleReceiptProductUnit->is_has_sale = $data['is_has_sale'];
            $saleReceiptProductUnit->save();

            DB::commit();

            $this->flushCache();

            return $saleReceiptProductUnit;
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
        $query = SaleReceiptProductUnit::select('sale_receipt_product_units.*')->withTrashed()
            ->with(['company'])
            ->join('companies', 'companies.id', '=', 'sale_receipt_product_units.company_id')
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
            ->orderBy('sale_receipt_product_units.id', 'asc');

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

    public function read(SaleReceiptProductUnit $saleReceiptProductUnit): SaleReceiptProductUnit
    {
        return $saleReceiptProductUnit->load('company')->first();
    }

    public function getAllActiveSaleReceiptProductUnit(
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

    public function update(SaleReceiptProductUnit $saleReceiptProductUnit, array $data): SaleReceiptProductUnit
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $saleReceiptProductUnit->company_id = $data['company_id'];
            $saleReceiptProductUnit->sale_id = $data['sale_id'];
            $saleReceiptProductUnit->sale_receipt_id = $data['sale_receipt_id'];
            $saleReceiptProductUnit->qty = $data['qty'];
            $saleReceiptProductUnit->product_id = $data['product_id'];
            $saleReceiptProductUnit->product_unit_id = $data['product_unit_id'];
            $saleReceiptProductUnit->product_unit_amount_per_unit = $data['product_unit_amount_per_unit'];
            $saleReceiptProductUnit->product_unit_amount_total = $data['product_unit_amount_total'];
            $saleReceiptProductUnit->is_has_sale = $data['is_has_sale'];
            $saleReceiptProductUnit->save();

            DB::commit();

            $this->flushCache();

            return $saleReceiptProductUnit->refresh();
        } catch (Exception $e) {
            DB::rollBack();
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(SaleReceiptProductUnit $saleReceiptProductUnit): bool
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        $retval = false;

        try {
            $retval = $saleReceiptProductUnit->delete();

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
                $count = $company->saleReceiptProductUnits()->withTrashed()->count() + 1 + $tryCount;
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
        $result = SaleReceiptProductUnit::whereCompanyId($companyId)->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0 ? true : false;
    }
}
