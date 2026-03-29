<?php

namespace App\Actions\Purchase;

use App\DTOs\ExecuteDTO;
use App\Models\Company;
use App\Models\Purchase;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class PurchaseActions
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

        ?string $startDate,
        ?string $endDate,
        ?int $warehouseId,
        ?int $supplierId,
        ?int $purchaseOrderId,
        ?bool $isPosted,
        ?bool $isPaidOff,
        ?bool $isValid,

        ?ExecuteDTO $execute
    ) {
        $query = Purchase::select('purchases.*')
            ->with(['company', 'branch', 'warehouse', 'supplier', 'purchaseOrder'])
            ->join('companies', 'companies.id', '=', 'purchases.company_id')
            ->whereCompanyId('purchases', $companyId)
            ->whereBranchId('purchases', $branchId)
            ->withTrashed();

        $query->where(function ($query) use (
            $withTrashed,
            $search,
            $startDate,
            $endDate,
            $warehouseId,
            $supplierId,
            $purchaseOrderId,
            $isPosted,
            $isPaidOff,
            $isValid,
        ) {
            $query->withoutTrashed();
            if ($withTrashed) $query->withTrashed();

            if ($search) {
                $query->search($search);
            }

            if ($startDate) {
                $query->where('purchases.date', '>=', $startDate);
            }

            if ($endDate) {
                $query->where('purchases.date', '<=', $endDate);
            }

            if ($warehouseId) {
                $query->where('purchases.warehouse_id', $warehouseId);
            }

            if ($supplierId) {
                $query->where('purchases.supplier_id', $supplierId);
            }

            if ($purchaseOrderId) {
                $query->where('purchases.purchase_order_id', $purchaseOrderId);
            }

            if (! is_null($isPosted)) {
                $query->where('purchases.is_posted', $isPosted);
            }

            if (! is_null($isPaidOff)) {
                $query->where('purchases.is_paid_off', $isPaidOff);
            }

            if (! is_null($isValid)) {
                $query->where('purchases.is_valid', $isValid);
            }
        });

        $query->orderBy('purchases.date', 'desc')
            ->orderBy('purchases.id', 'asc');

        if ($execute) {
            $timer_start = microtime(true);
            $recordsCount = 0;

            try {
                $cacheParams = [
                    $withTrashed ? 'true' : 'false',
                    $companyId,
                    $branchId ?? '[null]',
                    empty($search) ? '[empty]' : $search,
                    $startDate ?? '[null]',
                    $endDate ?? '[null]',
                    $warehouseId ?? '[null]',
                    $supplierId ?? '[null]',
                    $purchaseOrderId ?? '[null]',
                    is_null($isPosted) ? '[null]' : ($isPosted ? 'true' : 'false'),
                    is_null($isPaidOff) ? '[null]' : ($isPaidOff ? 'true' : 'false'),
                    is_null($isValid) ? '[null]' : ($isValid ? 'true' : 'false'),
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ];

                $cacheKey = 'read_any_purchase_'.implode('_', $cacheParams);

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
                        page: $execute->pagination->page,
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

    public function read(Purchase $purchase): Purchase
    {
        return $purchase->load([
            'company',
            'branch',
            'warehouse',
            'supplier',
            'purchaseOrder',
        ]);
    }

    public function create(array $data): Purchase
    {
        $timer_start = microtime(true);

        try {
            $purchase = new Purchase();
            $purchase->company_id = $data['company_id'];
            $purchase->branch_id = $data['branch_id'];
            $purchase->code = $this->generateUniqueCode($data['company_id'], $data['code'], null);
            $purchase->date = $data['date'];
            $purchase->due_days = $data['due_days'];
            $purchase->warehouse_id = $data['warehouse_id'];
            $purchase->supplier_id = $data['supplier_id'];
            $purchase->purchase_order_id = $data['purchase_order_id'];
            $purchase->delivery_note_reference = $data['delivery_note_reference'];
            $purchase->purchase_tax_invoice_number = $data['purchase_tax_invoice_number'];
            $purchase->purchase_tax_invoice_vat_base = $data['purchase_tax_invoice_vat_base'];
            $purchase->purchase_tax_invoice_vat = $data['purchase_tax_invoice_vat'];
            $purchase->return_tax_invoice_number = $data['return_tax_invoice_number'];
            $purchase->return_tax_invoice_vat_base = $data['return_tax_invoice_vat_base'];
            $purchase->return_tax_invoice_vat = $data['return_tax_invoice_vat'];
            $purchase->remarks = $data['remarks'];
            $purchase->is_posted = $data['is_posted'];
            $purchase->purchase_total = $data['purchase_total'];
            $purchase->purchase_global_discount_rate = $data['purchase_global_discount_rate'];
            $purchase->purchase_global_discount_fixed = $data['purchase_global_discount_fixed'];
            $purchase->purchase_additional_cost = $data['purchase_additional_cost'];
            $purchase->purchase_rounding = $data['purchase_rounding'];
            $purchase->purchase_grand_total = $data['purchase_grand_total'];
            $purchase->return_total = $data['return_total'];
            $purchase->return_global_discount_rate = $data['return_global_discount_rate'];
            $purchase->return_global_discount_fixed = $data['return_global_discount_fixed'];
            $purchase->return_rounding = $data['return_rounding'];
            $purchase->return_grand_total = $data['return_grand_total'];
            $purchase->amount_due = $data['amount_due'];
            $purchase->amount_paid_by_purchase_order_down_payment = $data['amount_paid_by_purchase_order_down_payment'];
            $purchase->amount_paid_by_purchase_return = $data['amount_paid_by_purchase_return'];
            $purchase->amount_paid_before_invoice = $data['amount_paid_before_invoice'];
            $purchase->amount_paid_on_invoice = $data['amount_paid_on_invoice'];
            $purchase->amount_paid_after_invoice = $data['amount_paid_after_invoice'];
            $purchase->amount_paid_total = $data['amount_paid_total'];
            $purchase->amount_due = $data['amount_due'];
            $purchase->is_paid_off = $data['is_paid_off'];
            $purchase->is_valid = $data['is_valid'];
            $purchase->save();

            $this->flushCache();

            return $purchase;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(Purchase $purchase, array $data): Purchase
    {
        $timer_start = microtime(true);

        try {
            $purchase->company_id = $data['company_id'];
            $purchase->branch_id = $data['branch_id'];
            $purchase->code = $this->generateUniqueCode($data['company_id'], $data['code'], $purchase->id);
            $purchase->date = $data['date'];
            $purchase->due_days = $data['due_days'];
            $purchase->warehouse_id = $data['warehouse_id'];
            $purchase->supplier_id = $data['supplier_id'];
            $purchase->purchase_order_id = $data['purchase_order_id'];
            $purchase->delivery_note_reference = $data['delivery_note_reference'];
            $purchase->purchase_tax_invoice_number = $data['purchase_tax_invoice_number'];
            $purchase->purchase_tax_invoice_vat_base = $data['purchase_tax_invoice_vat_base'];
            $purchase->purchase_tax_invoice_vat = $data['purchase_tax_invoice_vat'];
            $purchase->return_tax_invoice_number = $data['return_tax_invoice_number'];
            $purchase->return_tax_invoice_vat_base = $data['return_tax_invoice_vat_base'];
            $purchase->return_tax_invoice_vat = $data['return_tax_invoice_vat'];
            $purchase->remarks = $data['remarks'];
            $purchase->is_posted = $data['is_posted'];
            $purchase->purchase_total = $data['purchase_total'];
            $purchase->purchase_global_discount_rate = $data['purchase_global_discount_rate'];
            $purchase->purchase_global_discount_fixed = $data['purchase_global_discount_fixed'];
            $purchase->purchase_additional_cost = $data['purchase_additional_cost'];
            $purchase->purchase_rounding = $data['purchase_rounding'];
            $purchase->purchase_grand_total = $data['purchase_grand_total'];
            $purchase->return_total = $data['return_total'];
            $purchase->return_global_discount_rate = $data['return_global_discount_rate'];
            $purchase->return_global_discount_fixed = $data['return_global_discount_fixed'];
            $purchase->return_rounding = $data['return_rounding'];
            $purchase->return_grand_total = $data['return_grand_total'];
            $purchase->amount_due = $data['amount_due'];
            $purchase->amount_paid_by_purchase_order_down_payment = $data['amount_paid_by_purchase_order_down_payment'];
            $purchase->amount_paid_by_purchase_return = $data['amount_paid_by_purchase_return'];
            $purchase->amount_paid_before_invoice = $data['amount_paid_before_invoice'];
            $purchase->amount_paid_on_invoice = $data['amount_paid_on_invoice'];
            $purchase->amount_paid_after_invoice = $data['amount_paid_after_invoice'];
            $purchase->amount_paid_total = $data['amount_paid_total'];
            $purchase->amount_due = $data['amount_due'];
            $purchase->is_paid_off = $data['is_paid_off'];
            $purchase->is_valid = $data['is_valid'];
            $purchase->save();

            $this->flushCache();

            return $purchase->refresh();
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(Purchase $purchase): bool
    {
        $timer_start = microtime(true);

        $retval = false;

        try {
            $retval = $purchase->delete();

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
                $count = $company->purchases()->withTrashed()->count() + 1 + $tryCount;
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
        $result = Purchase::whereCompanyId('purchases', $companyId)->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0 ? true : false;
    }
}
