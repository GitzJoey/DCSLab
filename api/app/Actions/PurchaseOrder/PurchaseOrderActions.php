<?php

namespace App\Actions\PurchaseOrder;

use App\Actions\PurchaseOrderDownPayment\PurchaseOrderDownPaymentActions;
use App\Actions\PurchaseOrderDownPaymentAllocation\PurchaseOrderDownPaymentAllocationActions;
use App\Actions\PurchaseOrderDownPaymentRefund\PurchaseOrderDownPaymentRefundActions;
use App\Actions\PurchaseOrderGlobalDiscount\PurchaseOrderGlobalDiscountActions;
use App\Actions\PurchaseOrderItem\PurchaseOrderItemActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\PurchaseOrderCreateDTO;
use App\DTOs\PurchaseOrderDownPaymentCreateDTO;
use App\DTOs\PurchaseOrderDownPaymentRefundCreateDTO;
use App\DTOs\PurchaseOrderDownPaymentRefundUpdateDTO;
use App\DTOs\PurchaseOrderDownPaymentUpdateDTO;
use App\DTOs\PurchaseOrderGlobalDiscountCreateDTO;
use App\DTOs\PurchaseOrderGlobalDiscountUpdateDTO;
use App\DTOs\PurchaseOrderItemCreateDTO;
use App\DTOs\PurchaseOrderItemUpdateDTO;
use App\DTOs\PurchaseOrderUpdateDTO;
use App\Helpers\TimezoneHelper;
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

    private $purchaseOrderItemActions;

    private $purchaseOrderGlobalDiscountActions;

    private $purchaseOrderDownPaymentActions;

    private $purchaseOrderDownPaymentAllocationActions;

    private $purchaseOrderDownPaymentRefundActions;

    public function __construct(
        PurchaseOrderItemActions $purchaseOrderItemActions,
        PurchaseOrderGlobalDiscountActions $purchaseOrderGlobalDiscountActions,
        PurchaseOrderDownPaymentActions $purchaseOrderDownPaymentActions,
        PurchaseOrderDownPaymentAllocationActions $purchaseOrderDownPaymentAllocationActions,
        PurchaseOrderDownPaymentRefundActions $purchaseOrderDownPaymentRefundActions,
    ) {
        $this->purchaseOrderItemActions = $purchaseOrderItemActions;
        $this->purchaseOrderGlobalDiscountActions = $purchaseOrderGlobalDiscountActions;
        $this->purchaseOrderDownPaymentActions = $purchaseOrderDownPaymentActions;
        $this->purchaseOrderDownPaymentAllocationActions = $purchaseOrderDownPaymentAllocationActions;
        $this->purchaseOrderDownPaymentRefundActions = $purchaseOrderDownPaymentRefundActions;
    }

    public function readAny(
        bool $withTrashed,
        int $companyId,
        ?int $branchId,
        ?string $search,

        ?string $startDate,
        ?string $endDate,
        ?int $supplierId,

        ?ExecuteDTO $execute
    ) {
        $query = PurchaseOrder::select('purchase_orders.*')
            ->with(['company', 'branch', 'supplier'])
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
        ) {
            $query->withoutTrashed();
            if ($withTrashed) $query->withTrashed();

            if ($search) {
                $query->search($search);
            }

            if ($startDate) {
                $query->where('purchase_orders.date', '>=', TimezoneHelper::convertToUTC($startDate));
            }

            if ($endDate) {
                $query->where('purchase_orders.date', '<=', TimezoneHelper::convertToUTC($endDate));
            }

            if ($supplierId) {
                $query->where('purchase_orders.supplier_id', $supplierId);
            }
        });

        $query->orderBy('purchase_orders.date', 'desc');

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

    public function read(PurchaseOrder $purchaseOrder): PurchaseOrder
    {
        return $purchaseOrder->load([
            'company',
            'branch',
            'supplier',
            'globalDiscounts',
            'items.productUnit.unit',
            'items.productUnit.product.category',
            'items.productUnit.product.brand',
            'items.productUnit.product.baseProductUnit.unit',
            'items.productUnit.product.images',
            'items.productUnit.product.mainImage',
            'items.vatProfile',
            'items.productUnitPriceDiscounts',
            'items.subtotalDiscounts',
            'downPayments.cashAccount',
            'refundedDownPayments.cashAccount',
        ]);
    }

    public function generateUniqueCode(int $companyId, string $code, ?int $exceptId): string
    {
        if ($code != Config::get('dcslab.KEYWORDS.AUTO')) return $code;

        $company = Company::find($companyId);

        $tryCount = 0;
        do {
            $count = $company->purchaseOrders()->withTrashed()->count() + 1 + $tryCount;
            $code = 'PO'.str_pad($count, 5, '0', STR_PAD_LEFT);
            $tryCount++;
        } while (! $this->isUniqueCode($companyId, $code, $exceptId));

        return $code;
    }

    public function isUniqueCode(int $companyId, string $code, ?int $exceptId): bool
    {
        $result = PurchaseOrder::where('company_id', $companyId)->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0;
    }

    private function generateDate(string $date): string
    {
        if ($date == config('dcslab.KEYWORDS.AUTO')) {
            $nowLocal = now(TimezoneHelper::getUserTimezone())->toDateTimeString();

            return TimezoneHelper::convertToUTC($nowLocal);
        }

        return TimezoneHelper::convertToUTC($date);
    }

    public function create(PurchaseOrderCreateDTO $data): PurchaseOrder
    {
        $timer_start = microtime(true);

        try {
            $purchaseOrder = new PurchaseOrder();
            $purchaseOrder->company_id = $data->companyId;
            $purchaseOrder->branch_id = $data->branchId;
            $purchaseOrder->code = $this->generateUniqueCode($data->companyId, $data->code, null);
            $purchaseOrder->date = $this->generateDate($data->date);
            $purchaseOrder->due_days = $data->dueDays;
            $purchaseOrder->supplier_id = $data->supplierId;
            $purchaseOrder->remarks = $data->remarks;
            $purchaseOrder->rounding = $data->rounding;
            $purchaseOrder->save();

            foreach ($data->items as $item) {
                $dto = new PurchaseOrderItemCreateDTO(
                    companyId: $purchaseOrder->company_id,
                    branchId: $purchaseOrder->branch_id,
                    purchaseOrderId: $purchaseOrder->id,
                    qty: $item['qty'],
                    productUnitId: $item['product_unit_id'],
                    productUnitConversionValue: $item['product_unit_conversion_value'],
                    productUnitPrice: $item['product_unit_price'],
                    productUnitIsPriceIncludeVat: $item['product_unit_is_price_include_vat'],
                    productUnitPriceDiscounts: $item['product_unit_price_discounts'],
                    subtotalDiscounts: $item['subtotal_discounts'],
                    vatProfileId: $item['vat_profile_id'],
                    vatRate: $item['vat_rate'],
                    vatBaseNumerator: $item['vat_base_numerator'],
                    vatBaseDenominator: $item['vat_base_denominator'],
                    remarks: $item['remarks'] ?? null,
                );

                $this->purchaseOrderItemActions->create($dto, false);
            }

            foreach ($data->globalDiscounts as $globalDiscount) {
                $dto = new PurchaseOrderGlobalDiscountCreateDTO(
                    companyId: $purchaseOrder->company_id,
                    branchId: $purchaseOrder->branch_id,
                    purchaseOrderId: $purchaseOrder->id,
                    sequence: $globalDiscount['sequence'],
                    discountType: $globalDiscount['discount_type'],
                    discountValue: $globalDiscount['discount_value'],
                );

                $this->purchaseOrderGlobalDiscountActions->create($dto);
            }

            foreach ($data->downPayments as $downPayment) {
                $dto = new PurchaseOrderDownPaymentCreateDTO(
                    companyId: $purchaseOrder->company_id,
                    branchId: $purchaseOrder->branch_id,
                    purchaseOrderId: $purchaseOrder->id,
                    code: $downPayment['code'],
                    date: $downPayment['date'],
                    cashAccountId: $downPayment['cash_account_id'],
                    amount: $downPayment['amount'],
                    remarks: $downPayment['remarks'] ?? null,
                );

                $this->purchaseOrderDownPaymentActions->create($dto, false);
            }

            foreach ($data->refundedDownPayments as $refundedDownPayment) {
                $dto = new PurchaseOrderDownPaymentRefundCreateDTO(
                    companyId: $purchaseOrder->company_id,
                    branchId: $purchaseOrder->branch_id,
                    purchaseOrderId: $purchaseOrder->id,
                    code: $refundedDownPayment['code'],
                    date: $refundedDownPayment['date'],
                    cashAccountId: $refundedDownPayment['cash_account_id'],
                    amount: $refundedDownPayment['amount'],
                    remarks: $refundedDownPayment['remarks'] ?? null,
                );

                $this->purchaseOrderDownPaymentRefundActions->create($dto, false);
            }

            self::updateSummary($purchaseOrder);
            $this->purchaseOrderItemActions->updateCalculatedFieldsByPurchaseOrder($purchaseOrder);

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

    public function update(PurchaseOrder $purchaseOrder, PurchaseOrderUpdateDTO $data): PurchaseOrder
    {
        $timer_start = microtime(true);

        try {
            $purchaseOrder->company_id = $data->companyId;
            $purchaseOrder->branch_id = $data->branchId;
            $purchaseOrder->code = $this->generateUniqueCode($data->companyId, $data->code, $purchaseOrder->id);
            $purchaseOrder->date = $this->generateDate($data->date);
            $purchaseOrder->due_days = $data->dueDays;
            $purchaseOrder->supplier_id = $data->supplierId;
            $purchaseOrder->remarks = $data->remarks;
            $purchaseOrder->rounding = $data->rounding;
            $purchaseOrder->save();

            foreach ($data->deleteGlobalDiscountIds as $deleteId) {
                $poGlobalDiscount = $purchaseOrder->globalDiscounts()->findOrFail($deleteId);
                $this->purchaseOrderGlobalDiscountActions->delete($poGlobalDiscount);
            }

            foreach ($data->globalDiscounts as $globalDiscount) {
                if (! empty($globalDiscount['id'])) {
                    $poGlobalDiscount = $purchaseOrder->globalDiscounts()->findOrFail($globalDiscount['id']);
                    $dto = new PurchaseOrderGlobalDiscountUpdateDTO(
                        sequence: $globalDiscount['sequence'],
                        discountType: $globalDiscount['discount_type'],
                        discountValue: $globalDiscount['discount_value'],
                    );

                    $this->purchaseOrderGlobalDiscountActions->update($poGlobalDiscount, $dto);
                } else {
                    $dto = new PurchaseOrderGlobalDiscountCreateDTO(
                        companyId: $purchaseOrder->company_id,
                        branchId: $purchaseOrder->branch_id,
                        purchaseOrderId: $purchaseOrder->id,
                        sequence: $globalDiscount['sequence'],
                        discountType: $globalDiscount['discount_type'],
                        discountValue: $globalDiscount['discount_value'],
                    );

                    $this->purchaseOrderGlobalDiscountActions->create($dto);
                }
            }

            foreach ($data->deleteItemIds as $deleteId) {
                $poItem = $purchaseOrder->items()->findOrFail($deleteId);
                $this->purchaseOrderItemActions->delete($poItem);
            }

            foreach ($data->items as $item) {
                if (! empty($item['id'])) {
                    $poItem = $purchaseOrder->items()->findOrFail($item['id']);
                    $dto = new PurchaseOrderItemUpdateDTO(
                        qty: $item['qty'],
                        productUnitId: $item['product_unit_id'],
                        productUnitConversionValue: $item['product_unit_conversion_value'],
                        productUnitPrice: $item['product_unit_price'],
                        productUnitIsPriceIncludeVat: $item['product_unit_is_price_include_vat'],
                        deleteProductUnitPriceDiscountIds: $item['delete_product_unit_price_discount_ids'],
                        productUnitPriceDiscounts: $item['product_unit_price_discounts'],
                        deleteSubtotalDiscountIds: $item['delete_subtotal_discount_ids'],
                        subtotalDiscounts: $item['subtotal_discounts'],
                        vatProfileId: $item['vat_profile_id'],
                        vatRate: $item['vat_rate'],
                        vatBaseNumerator: $item['vat_base_numerator'],
                        vatBaseDenominator: $item['vat_base_denominator'],
                        remarks: $item['remarks'],
                    );

                    $this->purchaseOrderItemActions->update($poItem, $dto, false);
                } else {
                    $dto = new PurchaseOrderItemCreateDTO(
                        companyId: $purchaseOrder->company_id,
                        branchId: $purchaseOrder->branch_id,
                        purchaseOrderId: $purchaseOrder->id,
                        qty: $item['qty'],
                        productUnitId: $item['product_unit_id'],
                        productUnitConversionValue: $item['product_unit_conversion_value'],
                        productUnitPrice: $item['product_unit_price'],
                        productUnitIsPriceIncludeVat: $item['product_unit_is_price_include_vat'],
                        productUnitPriceDiscounts: $item['product_unit_price_discounts'],
                        subtotalDiscounts: $item['subtotal_discounts'],
                        vatProfileId: $item['vat_profile_id'],
                        vatRate: $item['vat_rate'],
                        vatBaseNumerator: $item['vat_base_numerator'],
                        vatBaseDenominator: $item['vat_base_denominator'],
                        remarks: $item['remarks'] ?? null,
                    );

                    $this->purchaseOrderItemActions->create($dto, false);
                }
            }

            foreach ($data->deleteDownPaymentIds as $deleteId) {
                $poDownPayment = $purchaseOrder->downPayments()->findOrFail($deleteId);
                $this->purchaseOrderDownPaymentActions->delete($poDownPayment);
            }

            foreach ($data->downPayments as $downPayment) {
                if (! empty($downPayment['id'])) {
                    $poDownPayment = $purchaseOrder->downPayments()->findOrFail($downPayment['id']);
                    $dto = new PurchaseOrderDownPaymentUpdateDTO(
                        code: $downPayment['code'],
                        date: $downPayment['date'],
                        cashAccountId: $downPayment['cash_account_id'],
                        amount: $downPayment['amount'],
                        remarks: $downPayment['remarks'] ?? null,
                    );

                    $this->purchaseOrderDownPaymentActions->update($poDownPayment, $dto, false);
                } else {
                    $dto = new PurchaseOrderDownPaymentCreateDTO(
                        companyId: $purchaseOrder->company_id,
                        branchId: $purchaseOrder->branch_id,
                        purchaseOrderId: $purchaseOrder->id,
                        code: $downPayment['code'],
                        date: $downPayment['date'],
                        cashAccountId: $downPayment['cash_account_id'],
                        amount: $downPayment['amount'],
                        remarks: $downPayment['remarks'] ?? null,
                    );

                    $this->purchaseOrderDownPaymentActions->create($dto, false);
                }
            }

            foreach ($data->deleteRefundedDownPaymentIds as $deleteId) {
                $poRefundedDownPayment = $purchaseOrder->refundedDownPayments()->findOrFail($deleteId);
                $this->purchaseOrderDownPaymentRefundActions->delete($poRefundedDownPayment);
            }

            foreach ($data->refundedDownPayments as $refundedDownPayment) {
                if (! empty($refundedDownPayment['id'])) {
                    $poRefundedDownPayment = $purchaseOrder->refundedDownPayments()->findOrFail($refundedDownPayment['id']);
                    $dto = new PurchaseOrderDownPaymentRefundUpdateDTO(
                        code: $refundedDownPayment['code'],
                        date: $refundedDownPayment['date'],
                        cashAccountId: $refundedDownPayment['cash_account_id'],
                        amount: $refundedDownPayment['amount'],
                        remarks: $refundedDownPayment['remarks'] ?? null,
                    );

                    $this->purchaseOrderDownPaymentRefundActions->update($poRefundedDownPayment, $dto, false);
                } else {
                    $dto = new PurchaseOrderDownPaymentRefundCreateDTO(
                        companyId: $purchaseOrder->company_id,
                        branchId: $purchaseOrder->branch_id,
                        purchaseOrderId: $purchaseOrder->id,
                        code: $refundedDownPayment['code'],
                        date: $refundedDownPayment['date'],
                        cashAccountId: $refundedDownPayment['cash_account_id'],
                        amount: $refundedDownPayment['amount'],
                        remarks: $refundedDownPayment['remarks'] ?? null,
                    );

                    $this->purchaseOrderDownPaymentRefundActions->create($dto, false);
                }
            }

            self::updateSummary($purchaseOrder);
            $this->purchaseOrderItemActions->updateCalculatedFieldsByPurchaseOrder($purchaseOrder);

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

    /**
     * Update the purchase order header summary.
     *
     * This method recalculates the persisted summary fields from the current
     * persisted child records.
     *
     * Child actions may call this method statically after they create or update
     * their own records directly. Do not construct or inject the parent action
     * only to refresh the header summary.
     */
    public static function updateSummary(PurchaseOrder $po): void
    {
        $po->item_total_before_global_discount = app(PurchaseOrderItemActions::class)->getSubtotalAfterDiscountAmountByPurchaseOrderId($po->id);
        $po->global_discount = app(PurchaseOrderGlobalDiscountActions::class)->getAmountByPurchaseOrderId($po->id);
        $po->item_total_after_global_discount = $po->item_total_before_global_discount - $po->global_discount;
        $po->amount_paid_down_payment = app(PurchaseOrderDownPaymentActions::class)->getAmountByPurchaseOrderId($po->id);
        $po->amount_allocated_down_payment = app(PurchaseOrderDownPaymentAllocationActions::class)->getAmountByPurchaseOrderId($po->id);
        $po->amount_refunded_down_payment = app(PurchaseOrderDownPaymentRefundActions::class)->getAmountByPurchaseOrderId($po->id);
        $po->amount_available_down_payment = $po->amount_paid_down_payment - $po->amount_allocated_down_payment - $po->amount_refunded_down_payment;

        $po->save();
    }

    public function delete(PurchaseOrder $purchaseOrder): bool
    {
        $timer_start = microtime(true);

        try {
            foreach ($purchaseOrder->items as $poItem) {
                $this->purchaseOrderItemActions->delete($poItem);
            }

            foreach ($purchaseOrder->globalDiscounts as $poGlobalDiscount) {
                $this->purchaseOrderGlobalDiscountActions->delete($poGlobalDiscount);
            }

            foreach ($purchaseOrder->downPayments as $poDownPayment) {
                $this->purchaseOrderDownPaymentActions->delete($poDownPayment);
            }

            foreach ($purchaseOrder->refundedDownPayments as $poRefundedDownPayment) {
                $this->purchaseOrderDownPaymentRefundActions->delete($poRefundedDownPayment);
            }

            $result = $purchaseOrder->delete();

            $this->flushCache();

            return $result;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }
}
