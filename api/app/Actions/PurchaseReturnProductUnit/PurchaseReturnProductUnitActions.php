<?php

namespace App\Actions\PurchaseReturnProductUnit;

use App\Models\Company;
use App\Models\PurchaseReturnProductUnit;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PurchaseReturnProductUnitActions
{
    use CacheHelper;
    use LoggerHelper;

    public function __construct()
    {
    }

    public function create(array $data): PurchaseReturnProductUnit
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $purchaseReturnProductUnit = new PurchaseReturnProductUnit();
            $purchaseReturnProductUnit->company_id = $data['company_id'];
            $purchaseReturnProductUnit->branch_id = $data['branch_id'];
            $purchaseReturnProductUnit->purchase_order_id = $data['purchase_order_id'];
            $purchaseReturnProductUnit->qty = $data['qty'];
            $purchaseReturnProductUnit->product_id = $data['product_id'];
            $purchaseReturnProductUnit->product_unit_id = $data['product_unit_id'];
            $purchaseReturnProductUnit->product_unit_amount_per_unit = $data['product_unit_amount_per_unit'];
            $purchaseReturnProductUnit->product_unit_amount_total = $data['product_unit_amount_total'];
            $purchaseReturnProductUnit->product_unit_initial_price = $data['product_unit_initial_price'];
            $purchaseReturnProductUnit->product_unit_discount_rate1 = $data['product_unit_discount_rate1'];
            $purchaseReturnProductUnit->product_unit_discount_rate2 = $data['product_unit_discount_rate2'];
            $purchaseReturnProductUnit->product_unit_discount_rate3 = $data['product_unit_discount_rate3'];
            $purchaseReturnProductUnit->product_unit_discount_rate4 = $data['product_unit_discount_rate4'];
            $purchaseReturnProductUnit->product_unit_discount_rate5 = $data['product_unit_discount_rate5'];
            $purchaseReturnProductUnit->product_unit_discount_fixed1 = $data['product_unit_discount_fixed1'];
            $purchaseReturnProductUnit->product_unit_discount_fixed2 = $data['product_unit_discount_fixed2'];
            $purchaseReturnProductUnit->product_unit_discount_fixed3 = $data['product_unit_discount_fixed3'];
            $purchaseReturnProductUnit->product_unit_discount_fixed4 = $data['product_unit_discount_fixed4'];
            $purchaseReturnProductUnit->product_unit_discount_fixed5 = $data['product_unit_discount_fixed5'];
            $purchaseReturnProductUnit->product_unit_net_price = $data['product_unit_net_price'];
            $purchaseReturnProductUnit->product_unit_subtotal = $data['product_unit_subtotal'];
            $purchaseReturnProductUnit->product_unit_subtotal_discount_rate = $data['product_unit_subtotal_discount_rate'];
            $purchaseReturnProductUnit->product_unit_subtotal_discount_fixed = $data['product_unit_subtotal_discount_fixed'];
            $purchaseReturnProductUnit->product_unit_total = $data['product_unit_total'];
            $purchaseReturnProductUnit->product_unit_global_discount_rate = $data['product_unit_global_discount_rate'];
            $purchaseReturnProductUnit->product_unit_global_discount_fixed = $data['product_unit_global_discount_fixed'];
            $purchaseReturnProductUnit->product_unit_grand_total = $data['product_unit_grand_total'];
            $purchaseReturnProductUnit->product_is_taxable = $data['product_is_taxable'];
            $purchaseReturnProductUnit->product_vat_rate = $data['product_vat_rate'];
            $purchaseReturnProductUnit->product_price_include_vat = $data['product_price_include_vat'];
            $purchaseReturnProductUnit->product_vat_base = $data['product_vat_base'];
            $purchaseReturnProductUnit->product_vat = $data['product_vat'];
            $purchaseReturnProductUnit->product_unit_final_price = $data['product_unit_final_price'];
            $purchaseReturnProductUnit->is_received = $data['is_received'];
            $purchaseReturnProductUnit->is_valid = $data['is_valid'];
            $purchaseReturnProductUnit->save();

            DB::commit();

            $this->flushCache();

            return $purchaseReturnProductUnit;
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
        $query = PurchaseReturnProductUnit::select('purchase_order_product_units.*')->withTrashed()
            ->with(['company'])
            ->join('companies', 'companies.id', '=', 'purchase_order_product_units.company_id')
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
            ->orderBy('purchase_order_product_units.id', 'asc');

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

    public function read(PurchaseReturnProductUnit $purchaseReturnProductUnit): PurchaseReturnProductUnit
    {
        return $purchaseReturnProductUnit->load('company')->first();
    }

    public function getAllActivePurchaseReturnProductUnit(
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

    public function update(PurchaseReturnProductUnit $purchaseReturnProductUnit, array $data): PurchaseReturnProductUnit
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $purchaseReturnProductUnit->company_id = $data['company_id'];
            $purchaseReturnProductUnit->branch_id = $data['branch_id'];
            $purchaseReturnProductUnit->purchase_order_id = $data['purchase_order_id'];
            $purchaseReturnProductUnit->qty = $data['qty'];
            $purchaseReturnProductUnit->product_id = $data['product_id'];
            $purchaseReturnProductUnit->product_unit_id = $data['product_unit_id'];
            $purchaseReturnProductUnit->product_unit_amount_per_unit = $data['product_unit_amount_per_unit'];
            $purchaseReturnProductUnit->product_unit_amount_total = $data['product_unit_amount_total'];
            $purchaseReturnProductUnit->product_unit_initial_price = $data['product_unit_initial_price'];
            $purchaseReturnProductUnit->product_unit_discount_rate1 = $data['product_unit_discount_rate1'];
            $purchaseReturnProductUnit->product_unit_discount_rate2 = $data['product_unit_discount_rate2'];
            $purchaseReturnProductUnit->product_unit_discount_rate3 = $data['product_unit_discount_rate3'];
            $purchaseReturnProductUnit->product_unit_discount_rate4 = $data['product_unit_discount_rate4'];
            $purchaseReturnProductUnit->product_unit_discount_rate5 = $data['product_unit_discount_rate5'];
            $purchaseReturnProductUnit->product_unit_discount_fixed1 = $data['product_unit_discount_fixed1'];
            $purchaseReturnProductUnit->product_unit_discount_fixed2 = $data['product_unit_discount_fixed2'];
            $purchaseReturnProductUnit->product_unit_discount_fixed3 = $data['product_unit_discount_fixed3'];
            $purchaseReturnProductUnit->product_unit_discount_fixed4 = $data['product_unit_discount_fixed4'];
            $purchaseReturnProductUnit->product_unit_discount_fixed5 = $data['product_unit_discount_fixed5'];
            $purchaseReturnProductUnit->product_unit_net_price = $data['product_unit_net_price'];
            $purchaseReturnProductUnit->product_unit_subtotal = $data['product_unit_subtotal'];
            $purchaseReturnProductUnit->product_unit_subtotal_discount_rate = $data['product_unit_subtotal_discount_rate'];
            $purchaseReturnProductUnit->product_unit_subtotal_discount_fixed = $data['product_unit_subtotal_discount_fixed'];
            $purchaseReturnProductUnit->product_unit_total = $data['product_unit_total'];
            $purchaseReturnProductUnit->product_unit_global_discount_rate = $data['product_unit_global_discount_rate'];
            $purchaseReturnProductUnit->product_unit_global_discount_fixed = $data['product_unit_global_discount_fixed'];
            $purchaseReturnProductUnit->product_unit_grand_total = $data['product_unit_grand_total'];
            $purchaseReturnProductUnit->product_is_taxable = $data['product_is_taxable'];
            $purchaseReturnProductUnit->product_vat_rate = $data['product_vat_rate'];
            $purchaseReturnProductUnit->product_price_include_vat = $data['product_price_include_vat'];
            $purchaseReturnProductUnit->product_vat_base = $data['product_vat_base'];
            $purchaseReturnProductUnit->product_vat = $data['product_vat'];
            $purchaseReturnProductUnit->product_unit_final_price = $data['product_unit_final_price'];
            $purchaseReturnProductUnit->is_received = $data['is_received'];
            $purchaseReturnProductUnit->is_valid = $data['is_valid'];
            $purchaseReturnProductUnit->save();

            DB::commit();

            $this->flushCache();

            return $purchaseReturnProductUnit->refresh();
        } catch (Exception $e) {
            DB::rollBack();
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(PurchaseReturnProductUnit $purchaseReturnProductUnit): bool
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        $retval = false;

        try {
            $retval = $purchaseReturnProductUnit->delete();

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
                $count = $company->purchaseReturnProductUnits()->withTrashed()->count() + 1 + $tryCount;
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
        $result = PurchaseReturnProductUnit::whereCompanyId($companyId)->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0 ? true : false;
    }
}
