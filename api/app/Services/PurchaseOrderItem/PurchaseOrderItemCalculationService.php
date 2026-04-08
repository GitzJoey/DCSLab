<?php

namespace App\Services\PurchaseOrderItem;

use App\Enums\DiscountTypeEnum;
use App\Models\PurchaseOrderItem;

class PurchaseOrderItemCalculationService
{
    public function fillCalculatedFieldsAfterSaveItemRow(PurchaseOrderItem $poItem): void
    {
        $poItem->product_unit_qty_base = $poItem->qty * $poItem->product_unit_conversion_value;
        $poItem->product_unit_price_discount = $this->calculateProductUnitPriceDiscountAmount($poItem);
        $poItem->product_unit_price_after_discount = $poItem->product_unit_price - $poItem->product_unit_price_discount;
        $poItem->product_unit_subtotal = $poItem->qty * $poItem->product_unit_price_after_discount;
        $poItem->product_unit_subtotal_discount = $this->calculateSubtotalDiscountAmount($poItem);
        $poItem->product_unit_subtotal_after_discount = $poItem->product_unit_subtotal - $poItem->product_unit_subtotal_discount;
    }

    public function fillCalculatedFieldsAfterSaveDiscountRows(
        PurchaseOrderItem $poItem,
        ?float $productUnitGlobalDiscount = null,
    ): void {
        if ($productUnitGlobalDiscount !== null) {
            $poItem->product_unit_global_discount = $productUnitGlobalDiscount;
        }

        if ($poItem->product_unit_global_discount < 0) {
            $poItem->product_unit_global_discount = 0;
        }

        $grossTotal = $poItem->product_unit_subtotal_after_discount - $poItem->product_unit_global_discount;
        $poItem->product_unit_total_before_vat = $this->calculateProductUnitTotalBeforeVatAmount(
            $poItem,
            $grossTotal,
        );
        $poItem->product_unit_vat_base = $poItem->product_unit_total_before_vat * $poItem->vat_base_factor;
        $poItem->product_unit_vat = $poItem->product_unit_vat_base * $poItem->vat_rate;
        $poItem->product_unit_rounding = 0;
        $poItem->product_unit_grand_total = $poItem->product_unit_total_before_vat
            + $poItem->product_unit_vat
            + $poItem->product_unit_rounding;

        $productUnitTotalCogs = $poItem->product_unit_total_before_vat;
        $poItem->product_unit_cogs = $poItem->qty == 0 ? 0 : $productUnitTotalCogs / $poItem->qty;
        $poItem->product_unit_total_cogs = $productUnitTotalCogs;
        $poItem->product_unit_base_unit_cogs = $poItem->product_unit_qty_base == 0 ? 0 : $productUnitTotalCogs / $poItem->product_unit_qty_base;
    }

    private function calculateProductUnitPriceDiscountAmount(PurchaseOrderItem $poItem): float
    {
        $beforeDiscount = (float) $poItem->product_unit_price;
        $afterDiscount = $beforeDiscount;

        foreach ($poItem->productUnitPriceDiscounts()->orderBy('sequence')->get() as $discount) {
            $discountType = $discount->discount_type instanceof DiscountTypeEnum
                ? $discount->discount_type
                : DiscountTypeEnum::resolveToEnum($discount->discount_type);
            $discountValue = (float) $discount->discount_value;

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

    private function calculateSubtotalDiscountAmount(PurchaseOrderItem $poItem): float
    {
        $beforeDiscount = (float) $poItem->product_unit_subtotal;
        $afterDiscount = $beforeDiscount;

        foreach ($poItem->subtotalDiscounts()->orderBy('sequence')->get() as $discount) {
            $discountType = $discount->discount_type instanceof DiscountTypeEnum
                ? $discount->discount_type
                : DiscountTypeEnum::resolveToEnum($discount->discount_type);
            $discountValue = (float) $discount->discount_value;

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

    private function calculateProductUnitTotalBeforeVatAmount(
        PurchaseOrderItem $poItem,
        float $grossTotal,
    ): float {
        $vatMultiplier = 1 + ($poItem->vat_base_factor * $poItem->vat_rate);

        if ($poItem->is_vat_included && $vatMultiplier > 0) {
            return $grossTotal / $vatMultiplier;
        }

        return $grossTotal;
    }
}
