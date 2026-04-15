<?php

namespace App\Actions\PurchaseOrder;

use App\Actions\PurchaseOrderDownPayment\PurchaseOrderDownPaymentActions;
use App\Actions\PurchaseOrderGlobalDiscount\PurchaseOrderGlobalDiscountActions;
use App\Actions\PurchaseOrderItem\PurchaseOrderItemActions;
use App\Models\PurchaseOrder;

class PurchaseOrderCalculationActions
{
    private $purchaseOrderGlobalDiscountActions;

    private $purchaseOrderDownPaymentActions;

    private $purchaseOrderItemActions;

    public function __construct(
        PurchaseOrderDownPaymentActions $purchaseOrderDownPaymentActions,
        PurchaseOrderGlobalDiscountActions $purchaseOrderGlobalDiscountActions,
        PurchaseOrderItemActions $purchaseOrderItemActions,
    ) {
        $this->purchaseOrderDownPaymentActions = $purchaseOrderDownPaymentActions;
        $this->purchaseOrderGlobalDiscountActions = $purchaseOrderGlobalDiscountActions;
        $this->purchaseOrderItemActions = $purchaseOrderItemActions;
    }

    public function updateSummary(PurchaseOrder $purchaseOrder): void
    {
        $purchaseOrder->item_total_before_global_discount = $this->purchaseOrderItemActions
            ->getSubtotalAfterDiscountAmountByPurchaseOrderId($purchaseOrder->id);
        $purchaseOrder->global_discount = $this->purchaseOrderGlobalDiscountActions
            ->getAmountByPurchaseOrderId($purchaseOrder->id);
        $purchaseOrder->item_total_after_global_discount =
            $purchaseOrder->item_total_before_global_discount - $purchaseOrder->global_discount;
        $purchaseOrder->amount_paid_down_payment = $this->purchaseOrderDownPaymentActions
            ->getAmountByPurchaseOrderId($purchaseOrder->id);
        $purchaseOrder->save();
    }
}
