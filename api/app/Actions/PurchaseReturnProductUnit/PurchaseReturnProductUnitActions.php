<?php

namespace App\Actions\PurchaseReturnProductUnit;

use App\DTOs\ExecuteDTO;
use App\Models\Company;
use App\Models\PurchaseReturnProductUnit;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class PurchaseReturnProductUnitActions
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
        $query = PurchaseReturnProductUnit::select('purchase_return_product_units.*')
            ->with([
                'company',
                'branch',
                'purchase',
                'warehouse',
                'product',
                'productUnit',
            ])
            ->join('companies', 'companies.id', '=', 'purchase_return_product_units.company_id')
            ->whereCompanyId('purchase_return_product_units', $companyId)
            ->withTrashed();

        $query->where(function ($query) use ($withTrashed, $search, $branchId, $purchaseId, $warehouseId, $productId, $productUnitId) {
            $query->withoutTrashed();
            if ($withTrashed) $query->withTrashed();

            if ($search) {
                $query->search($search);
            }

            if ($branchId) {
                $query->where('purchase_return_product_units.branch_id', $branchId);
            }

            if ($purchaseId) {
                $query->where('purchase_return_product_units.purchase_id', $purchaseId);
            }

            if ($warehouseId) {
                $query->where('purchase_return_product_units.warehouse_id', $warehouseId);
            }

            if ($productId) {
                $query->where('purchase_return_product_units.product_id', $productId);
            }

            if ($productUnitId) {
                $query->where('purchase_return_product_units.product_unit_id', $productUnitId);
            }
        });

        $query->orderBy('companies.name', 'asc')
            ->orderBy('purchase_return_product_units.id', 'asc');

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

                $cacheKey = 'read_any_purchase_return_product_unit_'.implode('_', $cacheParams);

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

    public function read(PurchaseReturnProductUnit $purchaseReturnProductUnit): PurchaseReturnProductUnit
    {
        return $purchaseReturnProductUnit->load([
            'company',
            'branch',
            'purchase',
            'warehouse',
            'product',
            'productUnit',
        ]);
    }

    public function create(array $data): PurchaseReturnProductUnit
    {
        $timer_start = microtime(true);

        try {
            $purchaseReturnProductUnit = new PurchaseReturnProductUnit();
            $purchaseReturnProductUnit->company_id = $data['company_id'];
            $purchaseReturnProductUnit->branch_id = $data['branch_id'];
            $purchaseReturnProductUnit->purchase_id = $data['purchase_id'];
            $purchaseReturnProductUnit->warehouse_id = $data['warehouse_id'];
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
            $purchaseReturnProductUnit->product_price_includes_vat = $data['product_price_includes_vat'];
            $purchaseReturnProductUnit->product_vat_base = $data['product_vat_base'];
            $purchaseReturnProductUnit->product_vat = $data['product_vat'];
            $purchaseReturnProductUnit->product_base_unit_final_price = $data['product_base_unit_final_price'];
            $purchaseReturnProductUnit->is_sent = $data['is_sent'];
            $purchaseReturnProductUnit->is_valid = $data['is_valid'];
            $purchaseReturnProductUnit->save();

            $this->flushCache();

            return $purchaseReturnProductUnit;
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
        $timer_start = microtime(true);

        try {
            $purchaseReturnProductUnit->company_id = $data['company_id'];
            $purchaseReturnProductUnit->branch_id = $data['branch_id'];
            $purchaseReturnProductUnit->purchase_id = $data['purchase_id'];
            $purchaseReturnProductUnit->warehouse_id = $data['warehouse_id'];
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
            $purchaseReturnProductUnit->product_price_includes_vat = $data['product_price_includes_vat'];
            $purchaseReturnProductUnit->product_vat_base = $data['product_vat_base'];
            $purchaseReturnProductUnit->product_vat = $data['product_vat'];
            $purchaseReturnProductUnit->product_base_unit_final_price = $data['product_base_unit_final_price'];
            $purchaseReturnProductUnit->is_sent = $data['is_sent'];
            $purchaseReturnProductUnit->is_valid = $data['is_valid'];
            $purchaseReturnProductUnit->save();

            $this->flushCache();

            return $purchaseReturnProductUnit->refresh();
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(PurchaseReturnProductUnit $purchaseReturnProductUnit): bool
    {
        $timer_start = microtime(true);

        $retval = false;

        try {
            $retval = $purchaseReturnProductUnit->delete();

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
        $result = PurchaseReturnProductUnit::whereCompanyId('purchase_return_product_units', $companyId)->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0 ? true : false;
    }
}
