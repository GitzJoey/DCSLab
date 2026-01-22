<?php

namespace App\Actions\PurchaseOrderProductUnit;

use App\Models\PurchaseOrderProductUnit;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PurchaseOrderProductUnitActions
{
    use CacheHelper;
    use LoggerHelper;

    public function __construct()
    {
    }

    public function create(array $data): PurchaseOrderProductUnit
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $purchaseOrderProductUnit = new PurchaseOrderProductUnit();
            $purchaseOrderProductUnit->company_id = $data['company_id'];
            $purchaseOrderProductUnit->branch_id = $data['branch_id'];
            $purchaseOrderProductUnit->purchase_order_id = $data['purchase_order_id'];
            $purchaseOrderProductUnit->qty = $data['qty'];
            $purchaseOrderProductUnit->product_id = $data['product_id'];
            $purchaseOrderProductUnit->product_unit_id = $data['product_unit_id'];
            $purchaseOrderProductUnit->product_unit_amount_per_unit = $data['product_unit_amount_per_unit'];
            $purchaseOrderProductUnit->product_unit_amount_total = $data['product_unit_amount_total'];
            $purchaseOrderProductUnit->product_unit_initial_price = $data['product_unit_initial_price'];
            $purchaseOrderProductUnit->product_unit_discount_rate1 = $data['product_unit_discount_rate1'];
            $purchaseOrderProductUnit->product_unit_discount_rate2 = $data['product_unit_discount_rate2'];
            $purchaseOrderProductUnit->product_unit_discount_rate3 = $data['product_unit_discount_rate3'];
            $purchaseOrderProductUnit->product_unit_discount_rate4 = $data['product_unit_discount_rate4'];
            $purchaseOrderProductUnit->product_unit_discount_rate5 = $data['product_unit_discount_rate5'];
            $purchaseOrderProductUnit->product_unit_discount_fixed1 = $data['product_unit_discount_fixed1'];
            $purchaseOrderProductUnit->product_unit_discount_fixed2 = $data['product_unit_discount_fixed2'];
            $purchaseOrderProductUnit->product_unit_discount_fixed3 = $data['product_unit_discount_fixed3'];
            $purchaseOrderProductUnit->product_unit_discount_fixed4 = $data['product_unit_discount_fixed4'];
            $purchaseOrderProductUnit->product_unit_discount_fixed5 = $data['product_unit_discount_fixed5'];
            $purchaseOrderProductUnit->product_unit_net_price = $data['product_unit_net_price'];
            $purchaseOrderProductUnit->product_unit_subtotal = $data['product_unit_subtotal'];
            $purchaseOrderProductUnit->product_unit_subtotal_discount_rate = $data['product_unit_subtotal_discount_rate'];
            $purchaseOrderProductUnit->product_unit_subtotal_discount_fixed = $data['product_unit_subtotal_discount_fixed'];
            $purchaseOrderProductUnit->product_unit_total = $data['product_unit_total'];
            $purchaseOrderProductUnit->product_unit_global_discount_rate = $data['product_unit_global_discount_rate'];
            $purchaseOrderProductUnit->product_unit_global_discount_fixed = $data['product_unit_global_discount_fixed'];
            $purchaseOrderProductUnit->product_unit_grand_total = $data['product_unit_grand_total'];
            $purchaseOrderProductUnit->product_is_taxable = $data['product_is_taxable'];
            $purchaseOrderProductUnit->product_vat_rate = $data['product_vat_rate'];
            $purchaseOrderProductUnit->product_price_include_vat = $data['product_price_include_vat'];
            $purchaseOrderProductUnit->product_vat_base = $data['product_vat_base'];
            $purchaseOrderProductUnit->product_vat = $data['product_vat'];
            $purchaseOrderProductUnit->product_unit_final_price = $data['product_unit_final_price'];
            $purchaseOrderProductUnit->is_received = $data['is_received'];
            $purchaseOrderProductUnit->is_valid = $data['is_valid'];

            $purchaseOrderProductUnit->save();

            DB::commit();

            $this->flushCache();

            return $purchaseOrderProductUnit;
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
        $query = PurchaseOrderProductUnit::select('purchase_order_product_units.*')->withTrashed()
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

    public function read(PurchaseOrderProductUnit $purchaseOrderProductUnit): PurchaseOrderProductUnit
    {
        return $purchaseOrderProductUnit->load('company')->first();
    }

    public function getAllActivePurchaseOrderProductUnit(
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

    public function update(PurchaseOrderProductUnit $purchaseOrderProductUnit, array $data): PurchaseOrderProductUnit
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $purchaseOrderProductUnit->company_id = $data['company_id'];
            $purchaseOrderProductUnit->branch_id = $data['branch_id'];
            $purchaseOrderProductUnit->purchase_order_id = $data['purchase_order_id'];
            $purchaseOrderProductUnit->qty = $data['qty'];
            $purchaseOrderProductUnit->product_id = $data['product_id'];
            $purchaseOrderProductUnit->product_unit_id = $data['product_unit_id'];
            $purchaseOrderProductUnit->product_unit_amount_per_unit = $data['product_unit_amount_per_unit'];
            $purchaseOrderProductUnit->product_unit_amount_total = $data['product_unit_amount_total'];
            $purchaseOrderProductUnit->product_unit_initial_price = $data['product_unit_initial_price'];
            $purchaseOrderProductUnit->product_unit_discount_rate1 = $data['product_unit_discount_rate1'];
            $purchaseOrderProductUnit->product_unit_discount_rate2 = $data['product_unit_discount_rate2'];
            $purchaseOrderProductUnit->product_unit_discount_rate3 = $data['product_unit_discount_rate3'];
            $purchaseOrderProductUnit->product_unit_discount_rate4 = $data['product_unit_discount_rate4'];
            $purchaseOrderProductUnit->product_unit_discount_rate5 = $data['product_unit_discount_rate5'];
            $purchaseOrderProductUnit->product_unit_discount_fixed1 = $data['product_unit_discount_fixed1'];
            $purchaseOrderProductUnit->product_unit_discount_fixed2 = $data['product_unit_discount_fixed2'];
            $purchaseOrderProductUnit->product_unit_discount_fixed3 = $data['product_unit_discount_fixed3'];
            $purchaseOrderProductUnit->product_unit_discount_fixed4 = $data['product_unit_discount_fixed4'];
            $purchaseOrderProductUnit->product_unit_discount_fixed5 = $data['product_unit_discount_fixed5'];
            $purchaseOrderProductUnit->product_unit_net_price = $data['product_unit_net_price'];
            $purchaseOrderProductUnit->product_unit_subtotal = $data['product_unit_subtotal'];
            $purchaseOrderProductUnit->product_unit_subtotal_discount_rate = $data['product_unit_subtotal_discount_rate'];
            $purchaseOrderProductUnit->product_unit_subtotal_discount_fixed = $data['product_unit_subtotal_discount_fixed'];
            $purchaseOrderProductUnit->product_unit_total = $data['product_unit_total'];
            $purchaseOrderProductUnit->product_unit_global_discount_rate = $data['product_unit_global_discount_rate'];
            $purchaseOrderProductUnit->product_unit_global_discount_fixed = $data['product_unit_global_discount_fixed'];
            $purchaseOrderProductUnit->product_unit_grand_total = $data['product_unit_grand_total'];
            $purchaseOrderProductUnit->product_is_taxable = $data['product_is_taxable'];
            $purchaseOrderProductUnit->product_vat_rate = $data['product_vat_rate'];
            $purchaseOrderProductUnit->product_price_include_vat = $data['product_price_include_vat'];
            $purchaseOrderProductUnit->product_vat_base = $data['product_vat_base'];
            $purchaseOrderProductUnit->product_vat = $data['product_vat'];
            $purchaseOrderProductUnit->product_unit_final_price = $data['product_unit_final_price'];
            $purchaseOrderProductUnit->is_received = $data['is_received'];
            $purchaseOrderProductUnit->is_valid = $data['is_valid'];
            $purchaseOrderProductUnit->save();

            DB::commit();

            $this->flushCache();

            return $purchaseOrderProductUnit->refresh();
        } catch (Exception $e) {
            DB::rollBack();
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(PurchaseOrderProductUnit $purchaseOrderProductUnit): bool
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        $retval = false;

        try {
            $retval = $purchaseOrderProductUnit->delete();

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
