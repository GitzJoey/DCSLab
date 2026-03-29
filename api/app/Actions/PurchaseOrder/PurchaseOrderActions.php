<?php

namespace App\Actions\PurchaseOrder;

use App\Actions\PurchaseOrderDownPayment\PurchaseOrderDownPaymentActions;
use App\Actions\PurchaseOrderProductUnit\PurchaseOrderProductUnitActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\PurchaseOrderCreateDTO;
use App\DTOs\PurchaseOrderUpdateDTO;
use App\Models\Company;
use App\Models\PurchaseOrder;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Facades\Config;

class PurchaseOrderActions
{
    use CacheHelper;
    use LoggerHelper;

    private $purchaseOrderProductUnitActions;

    private $purchaseOrderDownPaymentActions;

    public function __construct(
        PurchaseOrderProductUnitActions $purchaseOrderProductUnitActions,
        PurchaseOrderDownPaymentActions $purchaseOrderDownPaymentActions,
    ) {
        $this->purchaseOrderProductUnitActions = $purchaseOrderProductUnitActions;
        $this->purchaseOrderDownPaymentActions = $purchaseOrderDownPaymentActions;
    }

    public function readAny(
        bool $withTrashed,
        int $companyId,
        ?int $branchId,
        ?string $search,

        ?string $startDate,
        ?string $endDate,
        ?int $supplierId,
        ?bool $isHasInvoice,
        ?bool $isReceived,
        ?bool $isDownPaymentPaidOff,

        ?ExecuteDTO $execute
    ) {
        $query = PurchaseOrder::select('purchase_orders.*')
            ->with(['company', 'branch', 'supplier'])
            ->when($execute?->pagination, function ($query) {
                $query->with([
                    'purchaseOrderProductUnits.product.images',
                    'purchaseOrderProductUnits.productUnit.unit',
                    'purchaseOrderProductUnits.discounts',
                    'purchaseOrderDownPayments.cashAccount',
                ]);
            })
            ->join('companies', 'companies.id', '=', 'purchase_orders.company_id')
            ->whereCompanyId('purchase_orders', $companyId)
            ->whereBranchId('purchase_orders', $branchId)
            ->withTrashed();

        $query->where(function ($query) use (
            $withTrashed,
            $search,
            $startDate,
            $endDate,
            $supplierId,
            $isHasInvoice,
            $isReceived,
            $isDownPaymentPaidOff,
        ) {
            $query->withoutTrashed();
            if ($withTrashed) $query->withTrashed();

            if ($search) {
                $query->search($search);
            }

            if ($startDate) {
                $query->where('purchase_orders.date', '>=', $startDate);
            }

            if ($endDate) {
                $query->where('purchase_orders.date', '<=', $endDate);
            }

            if ($supplierId) {
                $query->where('purchase_orders.supplier_id', $supplierId);
            }

            if (! is_null($isHasInvoice)) {
                $query->where('purchase_orders.is_has_invoice', $isHasInvoice);
            }

            if (! is_null($isReceived)) {
                $query->where('purchase_orders.is_received', $isReceived);
            }

            if (! is_null($isDownPaymentPaidOff)) {
                $query->where('purchase_orders.is_down_payment_paid_off', $isDownPaymentPaidOff);
            }
        });

        $query->orderBy('purchase_orders.date', 'desc')
            ->orderBy('purchase_orders.id', 'asc');

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
                    $supplierId ?? '[null]',
                    is_null($isHasInvoice) ? '[null]' : ($isHasInvoice ? 'true' : 'false'),
                    is_null($isReceived) ? '[null]' : ($isReceived ? 'true' : 'false'),
                    is_null($isDownPaymentPaidOff) ? '[null]' : ($isDownPaymentPaidOff ? 'true' : 'false'),
                    $execute->pagination ? 'true' : 'false',
                    $execute->pagination?->page ?? '[null]',
                    $execute->pagination?->perPage ?? '[null]',
                    $execute->get?->limit ?? '[null]',
                ];

                $cacheKey = 'read_any_purchase_order_'.implode('_', $cacheParams);

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

    public function read(PurchaseOrder $purchaseOrder): PurchaseOrder
    {
        return $purchaseOrder->load([
            'company',
            'branch',
            'supplier',
            'purchaseOrderProductUnits.product.images',
            'purchaseOrderProductUnits.productUnit.unit',
            'purchaseOrderProductUnits.discounts',
            'purchaseOrderDownPayments.cashAccount',
        ]);
    }

    public function create(PurchaseOrderCreateDTO $data): PurchaseOrder
    {
        $timer_start = microtime(true);

        try {
            $purchaseOrder = new PurchaseOrder();
            $this->fillPurchaseOrder($purchaseOrder, $data);
            $purchaseOrder->code = $this->generateUniqueCode($data->companyId, $data->code, null);
            $purchaseOrder->save();

            $this->saveProductUnits($purchaseOrder, $data->productUnits);
            $this->saveDownPayments($purchaseOrder, $data->downPayments);

            $this->flushCache();

            return $purchaseOrder;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(PurchaseOrder $purchaseOrder, PurchaseOrderUpdateDTO $data): PurchaseOrder
    {
        $timer_start = microtime(true);

        try {
            $this->fillPurchaseOrder($purchaseOrder, $data);
            $purchaseOrder->code = $this->generateUniqueCode($data->companyId, $data->code, $purchaseOrder->id);
            $purchaseOrder->save();

            $this->updateProductUnits($purchaseOrder, $data->deleteProductUnitIds, $data->productUnits);
            $this->updateDownPayments($purchaseOrder, $data->deleteDownPaymentIds, $data->downPayments);

            $this->flushCache();

            return $purchaseOrder->refresh();
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(PurchaseOrder $purchaseOrder): bool
    {
        $timer_start = microtime(true);

        $retval = false;

        try {
            $retval = $purchaseOrder->delete();

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

    private function fillPurchaseOrder(PurchaseOrder $purchaseOrder, PurchaseOrderCreateDTO|PurchaseOrderUpdateDTO $data): void
    {
        $purchaseOrder->company_id = $data->companyId;
        $purchaseOrder->branch_id = $data->branchId;
        $purchaseOrder->supplier_id = $data->supplierId;
        $purchaseOrder->date = $data->date;
        $purchaseOrder->shipping_date = $data->shippingDate;
        $purchaseOrder->shipping_address = $data->shippingAddress;
        $purchaseOrder->remarks = $data->remarks;
        $purchaseOrder->is_has_invoice = $data->isHasInvoice;
        $purchaseOrder->is_received = $data->isReceived;
        $purchaseOrder->total = $data->total;
        $purchaseOrder->global_discount_rate = $data->globalDiscountRate;
        $purchaseOrder->global_discount_fixed = $data->globalDiscountFixed;
        $purchaseOrder->grand_total = $data->grandTotal;
        $purchaseOrder->down_payment = $data->downPayment;
        $purchaseOrder->down_payment_due_days = $data->downPaymentDueDays;
        $purchaseOrder->down_payment_applied = $data->downPaymentApplied;
        $purchaseOrder->down_payment_remaining = $data->downPaymentRemaining;
        $purchaseOrder->is_down_payment_paid_off = $data->isDownPaymentPaidOff;
    }

    private function saveProductUnits(PurchaseOrder $purchaseOrder, array $productUnits): void
    {
        foreach ($productUnits as $productUnit) {
            $this->purchaseOrderProductUnitActions->create([
                'company_id' => $purchaseOrder->company_id,
                'branch_id' => $purchaseOrder->branch_id,
                'purchase_order_id' => $purchaseOrder->id,
                'qty' => $productUnit['qty'],
                'product_id' => $productUnit['product_id'],
                'product_unit_id' => $productUnit['product_unit_id'],
                'product_unit_amount_per_unit' => $productUnit['product_unit_amount_per_unit'],
                'product_unit_amount_total' => $productUnit['product_unit_amount_total'],
                'product_unit_initial_price' => $productUnit['product_unit_initial_price'],
                'discounts' => $productUnit['discounts'],
                'product_unit_net_price' => $productUnit['product_unit_net_price'],
                'product_unit_subtotal' => $productUnit['product_unit_subtotal'],
                'product_unit_subtotal_discount_rate' => $productUnit['product_unit_subtotal_discount_rate'],
                'product_unit_subtotal_discount_fixed' => $productUnit['product_unit_subtotal_discount_fixed'],
                'product_unit_total' => $productUnit['product_unit_total'],
                'product_unit_global_discount_rate' => $productUnit['product_unit_global_discount_rate'],
                'product_unit_global_discount_fixed' => $productUnit['product_unit_global_discount_fixed'],
                'product_unit_grand_total' => $productUnit['product_unit_grand_total'],
                'product_is_taxable' => $productUnit['product_is_taxable'],
                'product_vat_rate' => $productUnit['product_vat_rate'],
                'product_price_include_vat' => $productUnit['product_price_include_vat'],
                'product_vat_base' => $productUnit['product_vat_base'],
                'product_vat' => $productUnit['product_vat'],
                'product_unit_final_price' => $productUnit['product_unit_final_price'],
                'product_final_price_base_unit' => $productUnit['product_final_price_base_unit'],
                'remarks' => $productUnit['remarks'],
            ]);
        }
    }

    private function saveDownPayments(PurchaseOrder $purchaseOrder, array $downPayments): void
    {
        foreach ($downPayments as $downPayment) {
            $this->purchaseOrderDownPaymentActions->create([
                'company_id' => $purchaseOrder->company_id,
                'branch_id' => $purchaseOrder->branch_id,
                'purchase_order_id' => $purchaseOrder->id,
                'code' => $downPayment['code'],
                'date' => $downPayment['date'],
                'cash_account_id' => $downPayment['cash_account_id'],
                'amount' => $downPayment['amount'],
                'remarks' => $downPayment['remarks'],
            ]);
        }
    }

    private function updateProductUnits(PurchaseOrder $purchaseOrder, array $deleteIds, array $productUnits): void
    {
        foreach ($deleteIds as $deleteId) {
            $purchaseOrderProductUnit = $purchaseOrder->purchaseOrderProductUnits()->findOrFail($deleteId);
            $this->purchaseOrderProductUnitActions->delete($purchaseOrderProductUnit);
        }

        foreach ($productUnits as $productUnit) {
            if ($productUnit['id']) {
                $purchaseOrderProductUnit = $purchaseOrder->purchaseOrderProductUnits()->findOrFail($productUnit['id']);

                $this->purchaseOrderProductUnitActions->update($purchaseOrderProductUnit, [
                    'company_id' => $purchaseOrder->company_id,
                    'branch_id' => $purchaseOrder->branch_id,
                    'purchase_order_id' => $purchaseOrder->id,
                    'qty' => $productUnit['qty'],
                    'product_id' => $productUnit['product_id'],
                    'product_unit_id' => $productUnit['product_unit_id'],
                    'product_unit_amount_per_unit' => $productUnit['product_unit_amount_per_unit'],
                    'product_unit_amount_total' => $productUnit['product_unit_amount_total'],
                    'product_unit_initial_price' => $productUnit['product_unit_initial_price'],
                    'discounts' => $productUnit['discounts'],
                    'product_unit_net_price' => $productUnit['product_unit_net_price'],
                    'product_unit_subtotal' => $productUnit['product_unit_subtotal'],
                    'product_unit_subtotal_discount_rate' => $productUnit['product_unit_subtotal_discount_rate'],
                    'product_unit_subtotal_discount_fixed' => $productUnit['product_unit_subtotal_discount_fixed'],
                    'product_unit_total' => $productUnit['product_unit_total'],
                    'product_unit_global_discount_rate' => $productUnit['product_unit_global_discount_rate'],
                    'product_unit_global_discount_fixed' => $productUnit['product_unit_global_discount_fixed'],
                    'product_unit_grand_total' => $productUnit['product_unit_grand_total'],
                    'product_is_taxable' => $productUnit['product_is_taxable'],
                    'product_vat_rate' => $productUnit['product_vat_rate'],
                    'product_price_include_vat' => $productUnit['product_price_include_vat'],
                    'product_vat_base' => $productUnit['product_vat_base'],
                    'product_vat' => $productUnit['product_vat'],
                    'product_unit_final_price' => $productUnit['product_unit_final_price'],
                    'product_final_price_base_unit' => $productUnit['product_final_price_base_unit'],
                    'remarks' => $productUnit['remarks'],
                ]);
            } else {
                $this->saveProductUnits($purchaseOrder, [$productUnit]);
            }
        }
    }

    private function updateDownPayments(PurchaseOrder $purchaseOrder, array $deleteIds, array $downPayments): void
    {
        foreach ($deleteIds as $deleteId) {
            $purchaseOrderDownPayment = $purchaseOrder->purchaseOrderDownPayments()->findOrFail($deleteId);
            $this->purchaseOrderDownPaymentActions->delete($purchaseOrderDownPayment);
        }

        foreach ($downPayments as $downPayment) {
            if ($downPayment['id']) {
                $purchaseOrderDownPayment = $purchaseOrder->purchaseOrderDownPayments()->findOrFail($downPayment['id']);

                $this->purchaseOrderDownPaymentActions->update($purchaseOrderDownPayment, [
                    'code' => $downPayment['code'],
                    'date' => $downPayment['date'],
                    'cash_account_id' => $downPayment['cash_account_id'],
                    'amount' => $downPayment['amount'],
                    'remarks' => $downPayment['remarks'],
                ]);
            } else {
                $this->saveDownPayments($purchaseOrder, [$downPayment]);
            }
        }
    }

    public function generateUniqueCode(int $companyId, string $code, ?int $exceptId): string
    {
        if ($code == config('dcslab.KEYWORDS.AUTO')) {
            $company = Company::find($companyId);

            $tryCount = 0;
            do {
                $count = $company->purchaseOrders()->withTrashed()->count() + 1 + $tryCount;
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
        $result = PurchaseOrder::whereCompanyId('purchase_orders', $companyId)->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0 ? true : false;
    }
}
