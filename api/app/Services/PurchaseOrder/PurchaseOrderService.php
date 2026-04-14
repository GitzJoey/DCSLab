<?php

namespace App\Services\PurchaseOrder;

use App\Actions\PurchaseOrderDownPayment\PurchaseOrderDownPaymentActions;
use App\Actions\PurchaseOrderGlobalDiscount\PurchaseOrderGlobalDiscountActions;
use App\Actions\PurchaseOrderItem\PurchaseOrderItemActions;
use App\DTOs\PurchaseOrderDownPaymentCreateDTO;
use App\DTOs\PurchaseOrderDownPaymentUpdateDTO;
use App\DTOs\PurchaseOrderGlobalDiscountCreateDTO;
use App\DTOs\PurchaseOrderGlobalDiscountUpdateDTO;
use App\DTOs\PurchaseOrderItemCreateDTO;
use App\DTOs\PurchaseOrderItemUpdateDTO;
use App\Enums\DiscountTypeEnum;
use App\Helpers\TimezoneHelper;
use App\Models\PurchaseOrder;
use App\Services\PurchaseOrderItem\PurchaseOrderItemCalculationService;

class PurchaseOrderService
{
    public function __construct(
        private PurchaseOrderGlobalDiscountActions $purchaseOrderGlobalDiscountActions,
        private PurchaseOrderItemActions $purchaseOrderItemActions,
        private PurchaseOrderDownPaymentActions $purchaseOrderDownPaymentActions,
        private PurchaseOrderItemCalculationService $purchaseOrderItemCalculationService,
    ) {
    }

    public function generateDate(string $date): string
    {
        if ($date == config('dcslab.KEYWORDS.AUTO')) {
            $nowLocal = now(TimezoneHelper::getUserTimezone())->toDateTimeString();

            return TimezoneHelper::convertToUTC($nowLocal);
        }

        return TimezoneHelper::convertToUTC($date);
    }

    public function createGlobalDiscounts(
        PurchaseOrder $purchaseOrder,
        array $globalDiscounts,
    ): void {
        foreach ($globalDiscounts as $globalDiscount) {
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

    public function createItems(
        PurchaseOrder $purchaseOrder,
        array $items,
    ): void {
        foreach ($items as $item) {
            $dto = new PurchaseOrderItemCreateDTO(
                companyId: $purchaseOrder->company_id,
                branchId: $purchaseOrder->branch_id,
                purchaseOrderId: $purchaseOrder->id,
                qty: $item['qty'],
                productUnitId: $item['product_unit_id'],
                productUnitConversionValue: $item['product_unit_conversion_value'],
                productUnitPrice: $item['product_unit_price'],
                productUnitPriceDiscounts: $item['product_unit_price_discounts'],
                subtotalDiscounts: $item['subtotal_discounts'],
                isVatIncluded: $item['is_vat_included'],
                vatProfileId: $item['vat_profile_id'],
                vatRate: $item['vat_rate'],
                vatBaseNumerator: $item['vat_base_numerator'],
                vatBaseDenominator: $item['vat_base_denominator'],
                remarks: $item['remarks'] ?? null,
            );

            $this->purchaseOrderItemActions->create($dto);
        }
    }

    public function createDownPayments(
        PurchaseOrder $purchaseOrder,
        array $downPayments,
    ): void {
        foreach ($downPayments as $downPayment) {
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

            $this->purchaseOrderDownPaymentActions->create($dto);
        }
    }

    public function updateSummary(PurchaseOrder $purchaseOrder): void
    {
        $poItems = $purchaseOrder->items()->orderBy('id')->get();
        $totalBeforeGlobalDiscount = (float) $poItems->sum('product_unit_subtotal_after_discount');
        $globalDiscount = $this->calculateGlobalDiscountAmount($purchaseOrder, $totalBeforeGlobalDiscount);

        $allocatedGlobalDiscount = 0.0;
        $lastItemId = $poItems->last()?->id;

        foreach ($poItems as $poItem) {
            if ($lastItemId && $poItem->id === $lastItemId) {
                $productUnitGlobalDiscount = $globalDiscount - $allocatedGlobalDiscount;
            } elseif ($totalBeforeGlobalDiscount > 0) {
                $productUnitGlobalDiscount = $globalDiscount * ($poItem->product_unit_subtotal_after_discount / $totalBeforeGlobalDiscount);
                $allocatedGlobalDiscount += $productUnitGlobalDiscount;
            } else {
                $productUnitGlobalDiscount = 0;
            }

            $this->purchaseOrderItemCalculationService->fillCalculatedFieldsAfterSaveDiscountRows(
                $poItem,
                productUnitGlobalDiscount: $productUnitGlobalDiscount,
            );
            $poItem->save();
        }

        $purchaseOrder->total_before_global_discount = $totalBeforeGlobalDiscount;
        $purchaseOrder->global_discount = (float) $poItems->sum('product_unit_global_discount');
        $purchaseOrder->total_before_vat = (float) $poItems->sum('product_unit_total_before_vat');
        $purchaseOrder->vat_base = (float) $poItems->sum('product_unit_vat_base');
        $purchaseOrder->vat = (float) $poItems->sum('product_unit_vat');
        $purchaseOrder->rounding = (float) $purchaseOrder->rounding;
        $purchaseOrder->grand_total = $purchaseOrder->total_before_vat
            + $purchaseOrder->vat
            + $purchaseOrder->rounding;
        $purchaseOrder->amount_paid_down_payment = (float) $purchaseOrder->downPayments()->sum('amount');
        $purchaseOrder->amount_allocated_down_payment = 0;
        $purchaseOrder->amount_available_down_payment = $purchaseOrder->amount_paid_down_payment - $purchaseOrder->amount_allocated_down_payment;
        $purchaseOrder->save();
    }

    public function syncGlobalDiscounts(
        PurchaseOrder $purchaseOrder,
        array $deleteGlobalDiscountIds,
        array $globalDiscounts,
    ): void {
        foreach ($deleteGlobalDiscountIds as $deleteId) {
            $poGlobalDiscount = $purchaseOrder->globalDiscounts()->findOrFail($deleteId);
            $this->purchaseOrderGlobalDiscountActions->delete($poGlobalDiscount);
        }

        foreach ($globalDiscounts as $globalDiscount) {
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
    }

    public function syncItems(
        PurchaseOrder $purchaseOrder,
        array $deleteItemIds,
        array $items,
    ): void {
        foreach ($deleteItemIds as $deleteId) {
            $poItem = $purchaseOrder->items()->findOrFail($deleteId);
            $this->purchaseOrderItemActions->delete($poItem);
        }

        foreach ($items as $item) {
            if (! empty($item['id'])) {
                $poItem = $purchaseOrder->items()->findOrFail($item['id']);
                $dto = new PurchaseOrderItemUpdateDTO(
                    qty: $item['qty'],
                    productUnitId: $item['product_unit_id'],
                    productUnitConversionValue: $item['product_unit_conversion_value'],
                    productUnitPrice: $item['product_unit_price'],
                    deleteProductUnitPriceDiscountIds: $item['delete_product_unit_price_discount_ids'],
                    productUnitPriceDiscounts: $item['product_unit_price_discounts'],
                    deleteSubtotalDiscountIds: $item['delete_subtotal_discount_ids'],
                    subtotalDiscounts: $item['subtotal_discounts'],
                    isVatIncluded: $item['is_vat_included'],
                    vatProfileId: $item['vat_profile_id'],
                    vatRate: $item['vat_rate'],
                    vatBaseNumerator: $item['vat_base_numerator'],
                    vatBaseDenominator: $item['vat_base_denominator'],
                    remarks: $item['remarks'],
                );

                $this->purchaseOrderItemActions->update($poItem, $dto);
            } else {
                $dto = new PurchaseOrderItemCreateDTO(
                    companyId: $purchaseOrder->company_id,
                    branchId: $purchaseOrder->branch_id,
                    purchaseOrderId: $purchaseOrder->id,
                    qty: $item['qty'],
                    productUnitId: $item['product_unit_id'],
                    productUnitConversionValue: $item['product_unit_conversion_value'],
                    productUnitPrice: $item['product_unit_price'],
                    productUnitPriceDiscounts: $item['product_unit_price_discounts'],
                    subtotalDiscounts: $item['subtotal_discounts'],
                    isVatIncluded: $item['is_vat_included'],
                    vatProfileId: $item['vat_profile_id'],
                    vatRate: $item['vat_rate'],
                    vatBaseNumerator: $item['vat_base_numerator'],
                    vatBaseDenominator: $item['vat_base_denominator'],
                    remarks: $item['remarks'] ?? null,
                );

                $this->purchaseOrderItemActions->create($dto);
            }
        }
    }

    public function syncDownPayments(
        PurchaseOrder $purchaseOrder,
        array $deleteDownPaymentIds,
        array $downPayments,
    ): void {
        foreach ($deleteDownPaymentIds as $deleteId) {
            $poDownPayment = $purchaseOrder->downPayments()->findOrFail($deleteId);
            $this->purchaseOrderDownPaymentActions->delete($poDownPayment);
        }

        foreach ($downPayments as $downPayment) {
            if (! empty($downPayment['id'])) {
                $poDownPayment = $purchaseOrder->downPayments()->findOrFail($downPayment['id']);
                $dto = new PurchaseOrderDownPaymentUpdateDTO(
                    code: $downPayment['code'],
                    date: $downPayment['date'],
                    cashAccountId: $downPayment['cash_account_id'],
                    amount: $downPayment['amount'],
                    remarks: $downPayment['remarks'] ?? null,
                );

                $this->purchaseOrderDownPaymentActions->update($poDownPayment, $dto);
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

                $this->purchaseOrderDownPaymentActions->create($dto);
            }
        }
    }

    public function deleteGlobalDiscounts(PurchaseOrder $purchaseOrder): void
    {
        foreach ($purchaseOrder->globalDiscounts as $poGlobalDiscount) {
            $this->purchaseOrderGlobalDiscountActions->delete($poGlobalDiscount);
        }
    }

    public function deleteDownPayments(PurchaseOrder $purchaseOrder): void
    {
        foreach ($purchaseOrder->downPayments as $poDownPayment) {
            $this->purchaseOrderDownPaymentActions->delete($poDownPayment);
        }
    }

    public function deleteItems(PurchaseOrder $purchaseOrder): void
    {
        foreach ($purchaseOrder->items as $poItem) {
            $this->purchaseOrderItemActions->delete($poItem);
        }
    }

    private function calculateGlobalDiscountAmount(PurchaseOrder $purchaseOrder, float $beforeDiscount): float
    {
        $afterDiscount = $beforeDiscount;

        foreach ($purchaseOrder->globalDiscounts()->orderBy('sequence')->get() as $globalDiscount) {
            $discountType = $globalDiscount->discount_type instanceof DiscountTypeEnum
                ? $globalDiscount->discount_type
                : DiscountTypeEnum::resolveToEnum($globalDiscount->discount_type);
            $discountValue = (float) $globalDiscount->discount_value;

            if ($discountType === DiscountTypeEnum::PERCENTAGE) {
                $afterDiscount -= $afterDiscount * $discountValue / 100;
            } else {
                $afterDiscount -= $discountValue;
            }

            if ($afterDiscount < 0) {
                $afterDiscount = 0;
            }
        }

        return $beforeDiscount - $afterDiscount;
    }
}
