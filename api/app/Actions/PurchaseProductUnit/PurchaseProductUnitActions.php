<?php

namespace App\Actions\PurchaseProductUnit;

use App\DTOs\ExecuteDTO;
use App\Models\PurchaseProductUnit;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class PurchaseProductUnitActions
{
    use CacheHelper;
    use LoggerHelper;

    public function __construct()
    {
    }

    public function readAny(
        bool $withTrashed,
        int $companyId,
        ?int $branchId,
        ?string $search,

        ?int $purchaseId,
        ?int $warehouseId,
        ?int $productId,
        ?int $productUnitId,

        ?ExecuteDTO $execute
    ) {
        $query = PurchaseProductUnit::select('purchase_product_units.*')
            ->with([
                'company',
                'branch',
                'purchase',
                'warehouse',
                'product',
                'productUnit',
            ])
            ->join('companies', 'companies.id', '=', 'purchase_product_units.company_id')
            ->whereCompanyId('purchase_product_units', $companyId)
            ->withTrashed();

        $query->where(function ($query) use ($withTrashed, $search, $branchId, $purchaseId, $warehouseId, $productId, $productUnitId) {
            $query->withoutTrashed();
            if ($withTrashed) $query->withTrashed();

            if ($search) {
                $query->search($search);
            }

            if ($branchId) {
                $query->where('purchase_product_units.branch_id', $branchId);
            }

            if ($purchaseId) {
                $query->where('purchase_product_units.purchase_id', $purchaseId);
            }

            if ($warehouseId) {
                $query->where('purchase_product_units.warehouse_id', $warehouseId);
            }

            if ($productId) {
                $query->where('purchase_product_units.product_id', $productId);
            }

            if ($productUnitId) {
                $query->where('purchase_product_units.product_unit_id', $productUnitId);
            }
        });

        $query->orderBy('companies.name', 'asc')
            ->orderBy('purchase_product_units.id', 'asc');

        if ($execute) {
            $timer_start = microtime(true);
            $recordsCount = 0;

            try {
                $cacheParams = [
                    $withTrashed ? 'true' : 'false',
                    $companyId,
                    empty($search) ? '[empty]' : $search,
                    $branchId ?? '[null]',
                    $purchaseId ?? '[null]',
                    $warehouseId ?? '[null]',
                    $productId ?? '[null]',
                    $productUnitId ?? '[null]',
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ];

                $cacheKey = 'read_any_purchase_product_unit_'.implode('_', $cacheParams);

                if ($execute->useCache) {
                    $cacheResult = $this->readFromCache($cacheKey);
                    if ($cacheResult !== Config::get('dcslab.ERROR_RETURN_VALUE')) {
                        return $cacheResult;
                    }
                }

                if ($execute->pagination) {
                    $result = $query->paginate(
                        perPage: $execute->pagination->perPage,
                        columns: ['*'],
                        pageName: 'page',
                        page: $execute->pagination->page
                    );
                } else {
                    if ($execute->get?->limit) {
                        $query->limit($execute->get->limit);
                    }
                    $result = $query->get();
                }

                $recordsCount = $result->count();

                if ($execute->useCache) {
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

        return $query;
    }

    public function read(PurchaseProductUnit $purchaseProductUnit): PurchaseProductUnit
    {
        return $purchaseProductUnit->load([
            'company',
            'branch',
            'purchase',
            'warehouse',
            'product',
            'productUnit',
        ]);
    }

    public function create(array $data): PurchaseProductUnit
    {
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
            $purchaseProductUnit->product_price_includes_vat = $data['product_price_includes_vat'];
            $purchaseProductUnit->product_vat_base = $data['product_vat_base'];
            $purchaseProductUnit->product_vat = $data['product_vat'];
            $purchaseProductUnit->product_base_unit_final_price = $data['product_base_unit_final_price'];
            $purchaseProductUnit->is_received = $data['is_received'];
            $purchaseProductUnit->is_valid = $data['is_valid'];

            $purchaseProductUnit->save();

            $this->flushCache();

            return $purchaseProductUnit;
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
            $purchaseProductUnit->product_price_includes_vat = $data['product_price_includes_vat'];
            $purchaseProductUnit->product_vat_base = $data['product_vat_base'];
            $purchaseProductUnit->product_vat = $data['product_vat'];
            $purchaseProductUnit->product_base_unit_final_price = $data['product_base_unit_final_price'];
            $purchaseProductUnit->is_received = $data['is_received'];
            $purchaseProductUnit->is_valid = $data['is_valid'];
            $purchaseProductUnit->save();

            $this->flushCache();

            return $purchaseProductUnit->refresh();
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(PurchaseProductUnit $purchaseProductUnit): bool
    {
        $timer_start = microtime(true);

        $retval = false;

        try {
            $retval = $purchaseProductUnit->delete();

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
