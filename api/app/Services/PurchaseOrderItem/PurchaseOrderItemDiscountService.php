<?php

namespace App\Services\PurchaseOrderItem;

use App\Actions\PurchaseOrderItemProductUnitPriceDiscount\PurchaseOrderItemProductUnitPriceDiscountActions;
use App\Actions\PurchaseOrderItemSubtotalDiscount\PurchaseOrderItemSubtotalDiscountActions;
use App\DTOs\PurchaseOrderItemProductUnitPriceDiscountCreateDTO;
use App\DTOs\PurchaseOrderItemProductUnitPriceDiscountUpdateDTO;
use App\DTOs\PurchaseOrderItemSubtotalDiscountCreateDTO;
use App\DTOs\PurchaseOrderItemSubtotalDiscountUpdateDTO;
use App\Models\PurchaseOrderItem;

class PurchaseOrderItemDiscountService
{
    public function __construct(
        private PurchaseOrderItemProductUnitPriceDiscountActions $purchaseOrderItemProductUnitPriceDiscountActions,
        private PurchaseOrderItemSubtotalDiscountActions $purchaseOrderItemSubtotalDiscountActions,
    ) {
    }

    public function createProductUnitPriceDiscounts(PurchaseOrderItem $poItem, array $discounts): void
    {
        foreach ($discounts as $discount) {
            $dto = new PurchaseOrderItemProductUnitPriceDiscountCreateDTO(
                companyId: $poItem->company_id,
                branchId: $poItem->branch_id,
                purchaseOrderItemId: $poItem->id,
                sequence: $discount['sequence'],
                discountType: $discount['discount_type'],
                discountValue: $discount['discount_value'],
            );

            $this->purchaseOrderItemProductUnitPriceDiscountActions->create($dto);
        }
    }

    public function createSubtotalDiscounts(PurchaseOrderItem $poItem, array $discounts): void
    {
        foreach ($discounts as $discount) {
            $dto = new PurchaseOrderItemSubtotalDiscountCreateDTO(
                companyId: $poItem->company_id,
                branchId: $poItem->branch_id,
                purchaseOrderItemId: $poItem->id,
                sequence: $discount['sequence'],
                discountType: $discount['discount_type'],
                discountValue: $discount['discount_value'],
            );

            $this->purchaseOrderItemSubtotalDiscountActions->create($dto);
        }
    }

    public function syncProductUnitPriceDiscounts(PurchaseOrderItem $poItem, array $deleteIds, array $discounts): void
    {
        foreach ($deleteIds as $deleteId) {
            $poItemPriceDiscount = $poItem->productUnitPriceDiscounts()->findOrFail($deleteId);
            $this->purchaseOrderItemProductUnitPriceDiscountActions->delete($poItemPriceDiscount);
        }

        foreach ($discounts as $discount) {
            if (! empty($discount['id'])) {
                $poItemPriceDiscount = $poItem->productUnitPriceDiscounts()->findOrFail($discount['id']);
                $dto = new PurchaseOrderItemProductUnitPriceDiscountUpdateDTO(
                    sequence: $discount['sequence'],
                    discountType: $discount['discount_type'],
                    discountValue: $discount['discount_value'],
                );

                $this->purchaseOrderItemProductUnitPriceDiscountActions->update($poItemPriceDiscount, $dto);
            } else {
                $dto = new PurchaseOrderItemProductUnitPriceDiscountCreateDTO(
                    companyId: $poItem->company_id,
                    branchId: $poItem->branch_id,
                    purchaseOrderItemId: $poItem->id,
                    sequence: $discount['sequence'],
                    discountType: $discount['discount_type'],
                    discountValue: $discount['discount_value'],
                );

                $this->purchaseOrderItemProductUnitPriceDiscountActions->create($dto);
            }
        }
    }

    public function syncSubtotalDiscounts(PurchaseOrderItem $poItem, array $deleteIds, array $discounts): void
    {
        foreach ($deleteIds as $deleteId) {
            $poItemSubtotalDiscount = $poItem->subtotalDiscounts()->findOrFail($deleteId);
            $this->purchaseOrderItemSubtotalDiscountActions->delete($poItemSubtotalDiscount);
        }

        foreach ($discounts as $discount) {
            if (! empty($discount['id'])) {
                $poItemSubtotalDiscount = $poItem->subtotalDiscounts()->findOrFail($discount['id']);
                $dto = new PurchaseOrderItemSubtotalDiscountUpdateDTO(
                    sequence: $discount['sequence'],
                    discountType: $discount['discount_type'],
                    discountValue: $discount['discount_value'],
                );

                $this->purchaseOrderItemSubtotalDiscountActions->update($poItemSubtotalDiscount, $dto);
            } else {
                $dto = new PurchaseOrderItemSubtotalDiscountCreateDTO(
                    companyId: $poItem->company_id,
                    branchId: $poItem->branch_id,
                    purchaseOrderItemId: $poItem->id,
                    sequence: $discount['sequence'],
                    discountType: $discount['discount_type'],
                    discountValue: $discount['discount_value'],
                );

                $this->purchaseOrderItemSubtotalDiscountActions->create($dto);
            }
        }
    }
}
