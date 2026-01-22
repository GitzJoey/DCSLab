<?php

namespace App\Actions\PurchaseProductUnit;

use App\Models\PurchaseProductUnit;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PurchaseProductUnitActions
{
    use CacheHelper;
    use LoggerHelper;

    public function __construct()
    {
    }

    public function create(array $data): PurchaseProductUnit
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $purchaseProductUnit = new PurchaseProductUnit();
            $purchaseProductUnit->company_id = $data['company_id'];
            $purchaseProductUnit->branch_id = $data['branch_id'];
            $purchaseProductUnit->purchase_id = $data['purchase_id'];
            $purchaseProductUnit->warehouse_id = $data['warehouse_id'];
            $purchaseProductUnit->qty = $data['qty'];
            $purchaseProductUnit->product_id = $data['product_id'];
            $purchaseProductUnit->product_unit_id = $data['product_unit_id'];
            $purchaseProductUnit->product_unit_amount_per_unit = $data['product_unit_amount_per_unit'];
            $purchaseProductUnit->product_unit_amount_total = $data['product_unit_amount_total'];
            $purchaseProductUnit->product_unit_initial_price = $data['product_unit_initial_price'];
            $purchaseProductUnit->product_unit_discount_rate1 = $data['product_unit_discount_rate1'];
            $purchaseProductUnit->product_unit_discount_rate2 = $data['product_unit_discount_rate2'];
            $purchaseProductUnit->product_unit_discount_rate3 = $data['product_unit_discount_rate3'];
            $purchaseProductUnit->product_unit_discount_rate4 = $data['product_unit_discount_rate4'];
            $purchaseProductUnit->product_unit_discount_rate5 = $data['product_unit_discount_rate5'];
            $purchaseProductUnit->product_unit_discount_fixed1 = $data['product_unit_discount_fixed1'];
            $purchaseProductUnit->product_unit_discount_fixed2 = $data['product_unit_discount_fixed2'];
            $purchaseProductUnit->product_unit_discount_fixed3 = $data['product_unit_discount_fixed3'];
            $purchaseProductUnit->product_unit_discount_fixed4 = $data['product_unit_discount_fixed4'];
            $purchaseProductUnit->product_unit_discount_fixed5 = $data['product_unit_discount_fixed5'];
            $purchaseProductUnit->product_unit_net_price = $data['product_unit_net_price'];
            $purchaseProductUnit->product_unit_subtotal = $data['product_unit_subtotal'];
            $purchaseProductUnit->product_unit_subtotal_discount_rate = $data['product_unit_subtotal_discount_rate'];
            $purchaseProductUnit->product_unit_subtotal_discount_fixed = $data['product_unit_subtotal_discount_fixed'];
            $purchaseProductUnit->product_unit_total = $data['product_unit_total'];
            $purchaseProductUnit->product_unit_global_discount_rate = $data['product_unit_global_discount_rate'];
            $purchaseProductUnit->product_unit_global_discount_fixed = $data['product_unit_global_discount_fixed'];
            $purchaseProductUnit->product_unit_grand_total = $data['product_unit_grand_total'];
            $purchaseProductUnit->product_is_taxable = $data['product_is_taxable'];
            $purchaseProductUnit->product_vat_rate = $data['product_vat_rate'];
            $purchaseProductUnit->product_price_include_vat = $data['product_price_include_vat'];
            $purchaseProductUnit->product_vat_base = $data['product_vat_base'];
            $purchaseProductUnit->product_vat = $data['product_vat'];
            $purchaseProductUnit->product_unit_final_price = $data['product_unit_final_price'];
            $purchaseProductUnit->product_final_price_base_unit = $data['product_final_price_base_unit'];
            $purchaseProductUnit->is_received = $data['is_received'];
            $purchaseProductUnit->is_valid = $data['is_valid'];

            $purchaseProductUnit->save();

            DB::commit();

            $this->flushCache();

            return $purchaseProductUnit;
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
        $query = PurchaseProductUnit::select('purchase_order_product_units.*')->withTrashed()
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

    public function read(PurchaseProductUnit $purchaseProductUnit): PurchaseProductUnit
    {
        return $purchaseProductUnit->load('company')->first();
    }

    public function getAllActivePurchaseProductUnit(
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

    public function update(PurchaseProductUnit $purchaseProductUnit, array $data): PurchaseProductUnit
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $purchaseProductUnit->company_id = $data['company_id'];
            $purchaseProductUnit->branch_id = $data['branch_id'];
            $purchaseProductUnit->purchase_id = $data['purchase_id'];
            $purchaseProductUnit->warehouse_id = $data['warehouse_id'];
            $purchaseProductUnit->qty = $data['qty'];
            $purchaseProductUnit->product_id = $data['product_id'];
            $purchaseProductUnit->product_unit_id = $data['product_unit_id'];
            $purchaseProductUnit->product_unit_amount_per_unit = $data['product_unit_amount_per_unit'];
            $purchaseProductUnit->product_unit_amount_total = $data['product_unit_amount_total'];
            $purchaseProductUnit->product_unit_initial_price = $data['product_unit_initial_price'];
            $purchaseProductUnit->product_unit_discount_rate1 = $data['product_unit_discount_rate1'];
            $purchaseProductUnit->product_unit_discount_rate2 = $data['product_unit_discount_rate2'];
            $purchaseProductUnit->product_unit_discount_rate3 = $data['product_unit_discount_rate3'];
            $purchaseProductUnit->product_unit_discount_rate4 = $data['product_unit_discount_rate4'];
            $purchaseProductUnit->product_unit_discount_rate5 = $data['product_unit_discount_rate5'];
            $purchaseProductUnit->product_unit_discount_fixed1 = $data['product_unit_discount_fixed1'];
            $purchaseProductUnit->product_unit_discount_fixed2 = $data['product_unit_discount_fixed2'];
            $purchaseProductUnit->product_unit_discount_fixed3 = $data['product_unit_discount_fixed3'];
            $purchaseProductUnit->product_unit_discount_fixed4 = $data['product_unit_discount_fixed4'];
            $purchaseProductUnit->product_unit_discount_fixed5 = $data['product_unit_discount_fixed5'];
            $purchaseProductUnit->product_unit_net_price = $data['product_unit_net_price'];
            $purchaseProductUnit->product_unit_subtotal = $data['product_unit_subtotal'];
            $purchaseProductUnit->product_unit_subtotal_discount_rate = $data['product_unit_subtotal_discount_rate'];
            $purchaseProductUnit->product_unit_subtotal_discount_fixed = $data['product_unit_subtotal_discount_fixed'];
            $purchaseProductUnit->product_unit_total = $data['product_unit_total'];
            $purchaseProductUnit->product_unit_global_discount_rate = $data['product_unit_global_discount_rate'];
            $purchaseProductUnit->product_unit_global_discount_fixed = $data['product_unit_global_discount_fixed'];
            $purchaseProductUnit->product_unit_grand_total = $data['product_unit_grand_total'];
            $purchaseProductUnit->product_is_taxable = $data['product_is_taxable'];
            $purchaseProductUnit->product_vat_rate = $data['product_vat_rate'];
            $purchaseProductUnit->product_price_include_vat = $data['product_price_include_vat'];
            $purchaseProductUnit->product_vat_base = $data['product_vat_base'];
            $purchaseProductUnit->product_vat = $data['product_vat'];
            $purchaseProductUnit->product_unit_final_price = $data['product_unit_final_price'];
            $purchaseProductUnit->product_final_price_base_unit = $data['product_final_price_base_unit'];
            $purchaseProductUnit->is_received = $data['is_received'];
            $purchaseProductUnit->is_valid = $data['is_valid'];
            $purchaseProductUnit->save();

            DB::commit();

            $this->flushCache();

            return $purchaseProductUnit->refresh();
        } catch (Exception $e) {
            DB::rollBack();
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(PurchaseProductUnit $purchaseProductUnit): bool
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        $retval = false;

        try {
            $retval = $purchaseProductUnit->delete();

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
}
