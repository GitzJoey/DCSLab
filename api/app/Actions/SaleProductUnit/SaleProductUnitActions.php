<?php

namespace App\Actions\SaleProductUnit;

use App\DTOs\ExecuteDTO;
use App\Models\Company;
use App\Models\SaleProductUnit;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class SaleProductUnitActions
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

        ?int $saleId,
        ?int $warehouseId,
        ?int $productId,
        ?int $productUnitId,

        ?ExecuteDTO $execute
    ) {
        $query = SaleProductUnit::select('sale_product_units.*')
            ->with([
                'company',
                'branch',
                'sale',
                'warehouse',
                'product',
                'productUnit',
            ])
            ->join('companies', 'companies.id', '=', 'sale_product_units.company_id')
            ->whereCompanyId('sale_product_units', $companyId)
            ->withTrashed();

        $query->where(function ($query) use ($withTrashed, $search, $branchId, $saleId, $warehouseId, $productId, $productUnitId) {
            $query->withoutTrashed();
            if ($withTrashed) $query->withTrashed();

            if ($search) {
                $query->search($search);
            }

            if ($branchId) {
                $query->where('sale_product_units.branch_id', $branchId);
            }

            if ($saleId) {
                $query->where('sale_product_units.sale_id', $saleId);
            }

            if ($warehouseId) {
                $query->where('sale_product_units.warehouse_id', $warehouseId);
            }

            if ($productId) {
                $query->where('sale_product_units.product_id', $productId);
            }

            if ($productUnitId) {
                $query->where('sale_product_units.product_unit_id', $productUnitId);
            }
        });

        $query->orderBy('companies.name', 'asc')
            ->orderBy('sale_product_units.id', 'asc');

        if ($execute) {
            $timer_start = microtime(true);
            $recordsCount = 0;

            try {
                $cacheParams = [
                    $withTrashed ? 'true' : 'false',
                    $companyId,
                    empty($search) ? '[empty]' : $search,
                    $branchId ?? '[null]',
                    $saleId ?? '[null]',
                    $warehouseId ?? '[null]',
                    $productId ?? '[null]',
                    $productUnitId ?? '[null]',
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ];

                $cacheKey = 'read_any_sale_product_unit_'.implode('_', $cacheParams);

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

    public function read(SaleProductUnit $saleProductUnit): SaleProductUnit
    {
        return $saleProductUnit->load([
            'company',
            'branch',
            'sale',
            'warehouse',
            'product',
            'productUnit',
        ]);
    }

    public function create(array $data): SaleProductUnit
    {
        $timer_start = microtime(true);

        try {
            $saleProductUnit = new SaleProductUnit();
            $saleProductUnit->company_id = $data['company_id'];
            $saleProductUnit->branch_id = $data['branch_id'];
            $saleProductUnit->sale_id = $data['sale_id'];
            $saleProductUnit->warehouse_id = $data['warehouse_id'];
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

            $this->flushCache();

            return $saleProductUnit;
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
        $timer_start = microtime(true);

        try {
            $saleProductUnit->company_id = $data['company_id'];
            $saleProductUnit->branch_id = $data['branch_id'];
            $saleProductUnit->sale_id = $data['sale_id'];
            $saleProductUnit->warehouse_id = $data['warehouse_id'];
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

            $this->flushCache();

            return $saleProductUnit->refresh();
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(SaleProductUnit $saleProductUnit): bool
    {
        $timer_start = microtime(true);

        $retval = false;

        try {
            $retval = $saleProductUnit->delete();

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
        $result = SaleProductUnit::whereCompanyId('sale_product_units', $companyId)->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0 ? true : false;
    }
}
