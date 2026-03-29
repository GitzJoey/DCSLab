<?php

namespace App\Actions\PurchaseReceiptProductUnit;

use App\DTOs\ExecuteDTO;
use App\Models\Company;
use App\Models\PurchaseReceiptProductUnit;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class PurchaseReceiptProductUnitActions
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

        ?int $purchaseReceiptId,
        ?int $purchaseId,
        ?int $productId,
        ?int $productUnitId,

        ?ExecuteDTO $execute
    ) {
        $query = PurchaseReceiptProductUnit::select('purchase_receipt_product_units.*')
            ->with([
                'company',
                'branch',
                'purchaseReceipt',
                'purchase',
                'product',
                'productUnit',
            ])
            ->join('companies', 'companies.id', '=', 'purchase_receipt_product_units.company_id')
            ->whereCompanyId('purchase_receipt_product_units', $companyId)
            ->withTrashed();

        $query->where(function ($query) use ($withTrashed, $search, $branchId, $purchaseReceiptId, $purchaseId, $productId, $productUnitId) {
            $query->withoutTrashed();
            if ($withTrashed) $query->withTrashed();

            if ($search) {
                $query->search($search);
            }

            if ($branchId) {
                $query->where('purchase_receipt_product_units.branch_id', $branchId);
            }

            if ($purchaseReceiptId) {
                $query->where('purchase_receipt_product_units.purchase_receipt_id', $purchaseReceiptId);
            }

            if ($purchaseId) {
                $query->where('purchase_receipt_product_units.purchase_id', $purchaseId);
            }

            if ($productId) {
                $query->where('purchase_receipt_product_units.product_id', $productId);
            }

            if ($productUnitId) {
                $query->where('purchase_receipt_product_units.product_unit_id', $productUnitId);
            }
        });

        $query->orderBy('companies.name', 'asc')
            ->orderBy('purchase_receipt_product_units.id', 'asc');

        if ($execute) {
            $timer_start = microtime(true);
            $recordsCount = 0;

            try {
                $cacheParams = [
                    $withTrashed ? 'true' : 'false',
                    $companyId,
                    empty($search) ? '[empty]' : $search,
                    $branchId ?? '[null]',
                    $purchaseReceiptId ?? '[null]',
                    $purchaseId ?? '[null]',
                    $productId ?? '[null]',
                    $productUnitId ?? '[null]',
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ];

                $cacheKey = 'read_any_purchase_receipt_product_unit_'.implode('_', $cacheParams);

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

    public function read(PurchaseReceiptProductUnit $purchaseReceiptProductUnit): PurchaseReceiptProductUnit
    {
        return $purchaseReceiptProductUnit->load([
            'company',
            'branch',
            'purchaseReceipt',
            'purchase',
            'product',
            'productUnit',
        ]);
    }

    public function create(array $data): PurchaseReceiptProductUnit
    {
        $timer_start = microtime(true);

        try {
            $purchaseReceiptProductUnit = new PurchaseReceiptProductUnit();
            $purchaseReceiptProductUnit->company_id = $data['company_id'];
            $purchaseReceiptProductUnit->branch_id = $data['branch_id'];
            $purchaseReceiptProductUnit->purchase_receipt_id = $data['purchase_receipt_id'];
            $purchaseReceiptProductUnit->purchase_id = $data['purchase_id'];
            $purchaseReceiptProductUnit->qty = $data['qty'];
            $purchaseReceiptProductUnit->product_id = $data['product_id'];
            $purchaseReceiptProductUnit->product_unit_id = $data['product_unit_id'];
            $purchaseReceiptProductUnit->product_unit_amount_per_unit = $data['product_unit_amount_per_unit'];
            $purchaseReceiptProductUnit->product_unit_amount_total = $data['product_unit_amount_total'];
            $purchaseReceiptProductUnit->is_has_purchase = $data['is_has_purchase'];
            $purchaseReceiptProductUnit->save();

            $this->flushCache();

            return $purchaseReceiptProductUnit;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(PurchaseReceiptProductUnit $purchaseReceiptProductUnit, array $data): PurchaseReceiptProductUnit
    {
        $timer_start = microtime(true);

        try {
            $purchaseReceiptProductUnit->company_id = $data['company_id'];
            $purchaseReceiptProductUnit->branch_id = $data['branch_id'];
            $purchaseReceiptProductUnit->purchase_receipt_id = $data['purchase_receipt_id'];
            $purchaseReceiptProductUnit->purchase_id = $data['purchase_id'];
            $purchaseReceiptProductUnit->qty = $data['qty'];
            $purchaseReceiptProductUnit->product_id = $data['product_id'];
            $purchaseReceiptProductUnit->product_unit_id = $data['product_unit_id'];
            $purchaseReceiptProductUnit->product_unit_amount_per_unit = $data['product_unit_amount_per_unit'];
            $purchaseReceiptProductUnit->product_unit_amount_total = $data['product_unit_amount_total'];
            $purchaseReceiptProductUnit->is_has_purchase = $data['is_has_purchase'];
            $purchaseReceiptProductUnit->save();

            $this->flushCache();

            return $purchaseReceiptProductUnit->refresh();
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(PurchaseReceiptProductUnit $purchaseReceiptProductUnit): bool
    {
        $timer_start = microtime(true);

        $retval = false;

        try {
            $retval = $purchaseReceiptProductUnit->delete();

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
                $count = $company->purchaseReceiptProductUnits()->withTrashed()->count() + 1 + $tryCount;
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
        $result = PurchaseReceiptProductUnit::whereCompanyId('purchase_receipt_product_units', $companyId)->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0 ? true : false;
    }
}
