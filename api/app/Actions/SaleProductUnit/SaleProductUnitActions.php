<?php

namespace App\Actions\SaleProductUnit;

use App\Models\Company;
use App\Models\SaleProductUnit;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SaleProductUnitActions
{
    use CacheHelper;
    use LoggerHelper;

    public function __construct()
    {
    }

    public function create(array $data): SaleProductUnit
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $saleProductUnit = new SaleProductUnit();
            $saleProductUnit->company_id = $data['company_id'];
            $saleProductUnit->branch_id = $data['branch_id'];
            $saleProductUnit->sale_order_id = $data['sale_order_id'];
            $saleProductUnit->qty = $data['qty'];
            $saleProductUnit->product_id = $data['product_id'];
            $saleProductUnit->product_unit_id = $data['product_unit_id'];
            $saleProductUnit->product_unit_amount_per_unit = $data['product_unit_amount_per_unit'];
            $saleProductUnit->product_unit_amount_total = $data['product_unit_amount_total'];
            $saleProductUnit->product_unit_initial_price = $data['product_unit_initial_price'];
            $saleProductUnit->product_unit_discount_rate1 = $data['product_unit_discount_rate1'];
            $saleProductUnit->product_unit_discount_rate2 = $data['product_unit_discount_rate2'];
            $saleProductUnit->product_unit_discount_rate3 = $data['product_unit_discount_rate3'];
            $saleProductUnit->product_unit_discount_rate4 = $data['product_unit_discount_rate4'];
            $saleProductUnit->product_unit_discount_rate5 = $data['product_unit_discount_rate5'];
            $saleProductUnit->product_unit_discount_fixed1 = $data['product_unit_discount_fixed1'];
            $saleProductUnit->product_unit_discount_fixed2 = $data['product_unit_discount_fixed2'];
            $saleProductUnit->product_unit_discount_fixed3 = $data['product_unit_discount_fixed3'];
            $saleProductUnit->product_unit_discount_fixed4 = $data['product_unit_discount_fixed4'];
            $saleProductUnit->product_unit_discount_fixed5 = $data['product_unit_discount_fixed5'];
            $saleProductUnit->product_unit_net_price = $data['product_unit_net_price'];
            $saleProductUnit->product_unit_subtotal = $data['product_unit_subtotal'];
            $saleProductUnit->product_unit_subtotal_discount_rate = $data['product_unit_subtotal_discount_rate'];
            $saleProductUnit->product_unit_subtotal_discount_fixed = $data['product_unit_subtotal_discount_fixed'];
            $saleProductUnit->product_unit_total = $data['product_unit_total'];

            $saleProductUnit->product_is_taxable = $data['product_is_taxable'];
            $saleProductUnit->product_vat_rate = $data['product_vat_rate'];
            $saleProductUnit->product_price_include_vat = $data['product_price_include_vat'];
            $saleProductUnit->product_vat_base = $data['product_vat_base'];
            $saleProductUnit->product_vat = $data['product_vat'];

            $saleProductUnit->product_unit_final_price = $data['product_unit_final_price'];
            $saleProductUnit->is_received = $data['is_received'];
            $saleProductUnit->is_valid = $data['is_valid'];

            $saleProductUnit->save();

            DB::commit();

            $this->flushCache();

            return $saleProductUnit;
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
        $query = SaleProductUnit::select('sale_product_units.*')->withTrashed()
            ->with(['company'])
            ->join('companies', 'companies.id', '=', 'sale_product_units.company_id')
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
            ->orderBy('sale_product_units.id', 'asc');

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

    public function read(SaleProductUnit $saleProductUnit): SaleProductUnit
    {
        return $saleProductUnit->load('company')->first();
    }

    public function getAllActiveSaleProductUnit(
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

    public function update(SaleProductUnit $saleProductUnit, array $data): SaleProductUnit
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $saleProductUnit->sale_order_id = $data['sale_order_id'];
            $saleProductUnit->qty = $data['qty'];
            $saleProductUnit->product_id = $data['product_id'];
            $saleProductUnit->product_unit_id = $data['product_unit_id'];
            $saleProductUnit->product_unit_amount_per_unit = $data['product_unit_amount_per_unit'];
            $saleProductUnit->product_unit_amount_total = $data['product_unit_amount_total'];
            $saleProductUnit->product_unit_initial_price = $data['product_unit_initial_price'];
            $saleProductUnit->product_unit_discount_rate1 = $data['product_unit_discount_rate1'];
            $saleProductUnit->product_unit_discount_rate2 = $data['product_unit_discount_rate2'];
            $saleProductUnit->product_unit_discount_rate3 = $data['product_unit_discount_rate3'];
            $saleProductUnit->product_unit_discount_rate4 = $data['product_unit_discount_rate4'];
            $saleProductUnit->product_unit_discount_rate5 = $data['product_unit_discount_rate5'];
            $saleProductUnit->product_unit_discount_fixed1 = $data['product_unit_discount_fixed1'];
            $saleProductUnit->product_unit_discount_fixed2 = $data['product_unit_discount_fixed2'];
            $saleProductUnit->product_unit_discount_fixed3 = $data['product_unit_discount_fixed3'];
            $saleProductUnit->product_unit_discount_fixed4 = $data['product_unit_discount_fixed4'];
            $saleProductUnit->product_unit_discount_fixed5 = $data['product_unit_discount_fixed5'];
            $saleProductUnit->product_unit_net_price = $data['product_unit_net_price'];
            $saleProductUnit->product_unit_subtotal = $data['product_unit_subtotal'];
            $saleProductUnit->product_unit_subtotal_discount_rate = $data['product_unit_subtotal_discount_rate'];
            $saleProductUnit->product_unit_subtotal_discount_fixed = $data['product_unit_subtotal_discount_fixed'];
            $saleProductUnit->product_unit_total = $data['product_unit_total'];

            $saleProductUnit->product_is_taxable = $data['product_is_taxable'];
            $saleProductUnit->product_vat_rate = $data['product_vat_rate'];
            $saleProductUnit->product_price_include_vat = $data['product_price_include_vat'];
            $saleProductUnit->product_vat_base = $data['product_vat_base'];
            $saleProductUnit->product_vat = $data['product_vat'];

            $saleProductUnit->product_unit_final_price = $data['product_unit_final_price'];
            $saleProductUnit->is_received = $data['is_received'];
            $saleProductUnit->is_valid = $data['is_valid'];

            $saleProductUnit->save();

            DB::commit();

            $this->flushCache();

            return $saleProductUnit->refresh();
        } catch (Exception $e) {
            DB::rollBack();
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(SaleProductUnit $saleProductUnit): bool
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        $retval = false;

        try {
            $retval = $saleProductUnit->delete();

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
                $count = $company->saleProductUnits()->withTrashed()->count() + 1 + $tryCount;
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
        $result = SaleProductUnit::whereCompanyId($companyId)->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0 ? true : false;
    }
}
