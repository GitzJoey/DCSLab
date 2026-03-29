<?php

namespace App\Actions\PurchaseOrderProductUnit;

use App\Models\PurchaseOrderProductUnit;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;

class PurchaseOrderProductUnitActions
{
    use CacheHelper;
    use LoggerHelper;

    public function __construct()
    {
    }

    public function readAny(
        ?bool $useCache,
        ?bool $withTrashed,
        ?string $search,
        int $companyId,
        ?int $branchId,
        ?int $purchaseOrderId,
        ?int $productId,
        ?int $productUnitId,
        bool $paginate,
        ?int $page,
        ?int $perPage,
        ?int $limit
    ): Paginator|Collection {
        $timer_start = microtime(true);
        $recordsCount = 0;

        try {
            $cacheSearch = empty($search) ? '[empty]' : $search;
            $cacheKey = implode('-', [
                'readAny_'.$companyId,
                $cacheSearch,
                $branchId ?? '[null]',
                $purchaseOrderId ?? '[null]',
                $productId ?? '[null]',
                $productUnitId ?? '[null]',
                $paginate ? 'true' : 'false',
                $page ?? '[null]',
                $perPage ?? '[null]',
                $limit ?? '[null]',
            ]);
            if ($useCache === true) {
                $cacheResult = $this->readFromCache($cacheKey);

                if (! is_null($cacheResult)) {
                    return $cacheResult;
                }
            }

            $query = PurchaseOrderProductUnit::select('purchase_order_product_units.*')->withTrashed()
                ->with([
                    'company',
                    'branch',
                    'purchaseOrder',
                    'product',
                    'productUnit',
                    'discounts',
                ])
                ->join('companies', 'companies.id', '=', 'purchase_order_product_units.company_id')
                ->where(function ($query) use ($withTrashed, $search, $companyId, $branchId, $purchaseOrderId, $productId, $productUnitId) {
                    if ($withTrashed == true) {
                        $query->withTrashed();
                    } else {
                        $query->withoutTrashed();
                    }

                    if ($search) {
                        $query->search($search);
                    }

                    if ($branchId) {
                        $query->where('purchase_order_product_units.branch_id', $branchId);
                    }

                    if ($purchaseOrderId) {
                        $query->where('purchase_order_product_units.purchase_order_id', $purchaseOrderId);
                    }

                    if ($productId) {
                        $query->where('purchase_order_product_units.product_id', $productId);
                    }

                    if ($productUnitId) {
                        $query->where('purchase_order_product_units.product_unit_id', $productUnitId);
                    }

                    $query->whereCompanyId('purchase_order_product_units', $companyId);
                })
                ->orderBy('companies.name', 'asc')
                ->orderBy('purchase_order_product_units.id', 'asc');

            if (! $paginate && $limit) {
                $query->limit($limit);
            }

            $result = $paginate
                ? $query->paginate(perPage: $perPage, page: $page)
                : $query->get();

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
        return $purchaseOrderProductUnit->load([
            'company',
            'branch',
            'purchaseOrder',
            'product',
            'productUnit',
            'discounts',
        ]);
    }

    public function create(array $data): PurchaseOrderProductUnit
    {
        $timer_start = microtime(true);

        try {
            $purchaseOrderProductUnit = new PurchaseOrderProductUnit();
            $this->fillPurchaseOrderProductUnit($purchaseOrderProductUnit, $data);
            $purchaseOrderProductUnit->save();
            $this->syncDiscounts($purchaseOrderProductUnit, $data['discounts']);

            $this->flushCache();

            return $purchaseOrderProductUnit->load([
                'company',
                'branch',
                'purchaseOrder',
                'product',
                'productUnit',
                'discounts',
            ]);
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    private function fillPurchaseOrderProductUnit(PurchaseOrderProductUnit $purchaseOrderProductUnit, array $data): void
    {
        $purchaseOrderProductUnit->company_id = $data['company_id'];
        $purchaseOrderProductUnit->branch_id = $data['branch_id'];
        $purchaseOrderProductUnit->purchase_order_id = $data['purchase_order_id'];
        $purchaseOrderProductUnit->qty = $data['qty'];
        $purchaseOrderProductUnit->product_id = $data['product_id'];
        $purchaseOrderProductUnit->product_unit_id = $data['product_unit_id'];
        $purchaseOrderProductUnit->product_unit_amount_per_unit = $data['product_unit_amount_per_unit'];
        $purchaseOrderProductUnit->product_unit_amount_total = $data['product_unit_amount_total'];
        $purchaseOrderProductUnit->product_unit_initial_price = $data['product_unit_initial_price'];
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
        $purchaseOrderProductUnit->product_is_price_include_vat = $data['product_price_include_vat'];
        $purchaseOrderProductUnit->product_vat_base = $data['product_vat_base'];
        $purchaseOrderProductUnit->product_vat = $data['product_vat'];
        $purchaseOrderProductUnit->product_unit_final_price = $data['product_unit_final_price'];
        $purchaseOrderProductUnit->product_final_price_base_unit = $data['product_final_price_base_unit'];
        $purchaseOrderProductUnit->remarks = $data['remarks'];
    }

    private function syncDiscounts(PurchaseOrderProductUnit $purchaseOrderProductUnit, array $discounts): void
    {
        $sequences = collect($discounts)
            ->pluck('sequence')
            ->map(fn ($sequence) => (int) $sequence)
            ->values();

        if ($sequences->isEmpty()) {
            $purchaseOrderProductUnit->discounts()->delete();

            return;
        }

        $purchaseOrderProductUnit->discounts()
            ->whereNotIn('sequence', $sequences->all())
            ->delete();

        $existingDiscounts = $purchaseOrderProductUnit->discounts()
            ->withTrashed()
            ->get()
            ->keyBy('sequence');

        foreach ($discounts as $discount) {
            $purchaseOrderProductUnitDiscount = $existingDiscounts->get((int) $discount['sequence']);

            if (is_null($purchaseOrderProductUnitDiscount)) {
                $purchaseOrderProductUnitDiscount = $purchaseOrderProductUnit->discounts()->make();
            } elseif ($purchaseOrderProductUnitDiscount->trashed()) {
                $purchaseOrderProductUnitDiscount->restore();
            }

            $purchaseOrderProductUnitDiscount->company_id = $purchaseOrderProductUnit->company_id;
            $purchaseOrderProductUnitDiscount->branch_id = $purchaseOrderProductUnit->branch_id;
            $purchaseOrderProductUnitDiscount->sequence = $discount['sequence'];
            $purchaseOrderProductUnitDiscount->rate = $discount['rate'];
            $purchaseOrderProductUnitDiscount->fixed = $discount['fixed'];
            $purchaseOrderProductUnitDiscount->save();
        }
    }

    public function update(PurchaseOrderProductUnit $purchaseOrderProductUnit, array $data): PurchaseOrderProductUnit
    {
        $timer_start = microtime(true);

        try {
            $this->fillPurchaseOrderProductUnit($purchaseOrderProductUnit, $data);
            $purchaseOrderProductUnit->save();
            $this->syncDiscounts($purchaseOrderProductUnit, $data['discounts']);

            $this->flushCache();

            return $purchaseOrderProductUnit->refresh()->load([
                'company',
                'branch',
                'purchaseOrder',
                'product',
                'productUnit',
                'discounts',
            ]);
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(PurchaseOrderProductUnit $purchaseOrderProductUnit): bool
    {
        $timer_start = microtime(true);

        $retval = false;

        try {
            $purchaseOrderProductUnit->discounts()->delete();
            $retval = $purchaseOrderProductUnit->delete();

            $this->flushCache();

            return $retval;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }
}
