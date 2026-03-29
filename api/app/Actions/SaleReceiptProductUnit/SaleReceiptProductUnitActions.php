<?php

namespace App\Actions\SaleReceiptProductUnit;

use App\DTOs\ExecuteDTO;
use App\Models\Company;
use App\Models\SaleReceiptProductUnit;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class SaleReceiptProductUnitActions
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

        ?int $saleReceiptId,
        ?int $productId,
        ?int $productUnitId,

        ?ExecuteDTO $execute
    ) {
        $query = SaleReceiptProductUnit::select('sale_receipt_product_units.*')
            ->with([
                'company',
                'branch',
                'saleReceipt',
                'product',
                'productUnit',
            ])
            ->join('companies', 'companies.id', '=', 'sale_receipt_product_units.company_id')
            ->whereCompanyId('sale_receipt_product_units', $companyId)
            ->withTrashed();

        $query->where(function ($query) use ($withTrashed, $search, $branchId, $saleReceiptId, $productId, $productUnitId) {
            $query->withoutTrashed();
            if ($withTrashed) $query->withTrashed();

            if ($search) {
                $query->search($search);
            }

            if ($branchId) {
                $query->where('sale_receipt_product_units.branch_id', $branchId);
            }

            if ($saleReceiptId) {
                $query->where('sale_receipt_product_units.sale_receipt_id', $saleReceiptId);
            }

            if ($productId) {
                $query->where('sale_receipt_product_units.product_id', $productId);
            }

            if ($productUnitId) {
                $query->where('sale_receipt_product_units.product_unit_id', $productUnitId);
            }
        });

        $query->orderBy('companies.name', 'asc')
            ->orderBy('sale_receipt_product_units.id', 'asc');

        if ($execute) {
            $timer_start = microtime(true);
            $recordsCount = 0;

            try {
                $cacheParams = [
                    $withTrashed ? 'true' : 'false',
                    $companyId,
                    empty($search) ? '[empty]' : $search,
                    $branchId ?? '[null]',
                    $saleReceiptId ?? '[null]',
                    $productId ?? '[null]',
                    $productUnitId ?? '[null]',
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ];

                $cacheKey = 'read_any_sale_receipt_product_unit_'.implode('_', $cacheParams);

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

    public function read(SaleReceiptProductUnit $saleReceiptProductUnit): SaleReceiptProductUnit
    {
        return $saleReceiptProductUnit->load([
            'company',
            'branch',
            'saleReceipt',
            'product',
            'productUnit',
        ]);
    }

    public function create(array $data): SaleReceiptProductUnit
    {
        $timer_start = microtime(true);

        try {
            $saleReceiptProductUnit = new SaleReceiptProductUnit();
            $saleReceiptProductUnit->company_id = $data['company_id'];
            $saleReceiptProductUnit->branch_id = $data['branch_id'];
            $saleReceiptProductUnit->sale_receipt_id = $data['sale_receipt_id'];
            $saleReceiptProductUnit->qty = $data['qty'];
            $saleReceiptProductUnit->product_id = $data['product_id'];
            $saleReceiptProductUnit->product_unit_id = $data['product_unit_id'];
            $saleReceiptProductUnit->product_unit_amount_per_unit = $data['product_unit_amount_per_unit'];
            $saleReceiptProductUnit->product_unit_amount_total = $data['product_unit_amount_total'];
            $saleReceiptProductUnit->is_has_sale = $data['is_has_sale'];
            $saleReceiptProductUnit->save();

            $this->flushCache();

            return $saleReceiptProductUnit;
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
        $timer_start = microtime(true);

        try {
            $saleReceiptProductUnit->company_id = $data['company_id'];
            $saleReceiptProductUnit->branch_id = $data['branch_id'];
            $saleReceiptProductUnit->sale_receipt_id = $data['sale_receipt_id'];
            $saleReceiptProductUnit->qty = $data['qty'];
            $saleReceiptProductUnit->product_id = $data['product_id'];
            $saleReceiptProductUnit->product_unit_id = $data['product_unit_id'];
            $saleReceiptProductUnit->product_unit_amount_per_unit = $data['product_unit_amount_per_unit'];
            $saleReceiptProductUnit->product_unit_amount_total = $data['product_unit_amount_total'];
            $saleReceiptProductUnit->is_has_sale = $data['is_has_sale'];
            $saleReceiptProductUnit->save();

            $this->flushCache();

            return $saleReceiptProductUnit->refresh();
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(SaleReceiptProductUnit $saleReceiptProductUnit): bool
    {
        $timer_start = microtime(true);

        $retval = false;

        try {
            $retval = $saleReceiptProductUnit->delete();

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
        $result = SaleReceiptProductUnit::whereCompanyId('sale_receipt_product_units', $companyId)->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0 ? true : false;
    }
}
