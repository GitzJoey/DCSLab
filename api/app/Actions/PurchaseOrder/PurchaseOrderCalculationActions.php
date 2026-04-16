<?php

namespace App\Actions\PurchaseOrder;

use App\Actions\PurchaseOrderDownPayment\PurchaseOrderDownPaymentActions;
use App\Actions\PurchaseOrderDownPaymentAllocation\PurchaseOrderDownPaymentAllocationActions;
use App\Actions\PurchaseOrderDownPaymentRefund\PurchaseOrderDownPaymentRefundActions;
use App\Actions\PurchaseOrderGlobalDiscount\PurchaseOrderGlobalDiscountActions;
use App\Actions\PurchaseOrderItem\PurchaseOrderItemActions;
use App\Models\PurchaseOrder;

class PurchaseOrderCalculationActions
{
    private $purchaseOrderGlobalDiscountActions;

    private $purchaseOrderDownPaymentActions;

    private $purchaseOrderItemActions;

    private $purchaseOrderDownPaymentAllocationActions;

    private $purchaseOrderDownPaymentRefundActions;

    public function __construct(
        PurchaseOrderDownPaymentActions $purchaseOrderDownPaymentActions,
        PurchaseOrderDownPaymentAllocationActions $purchaseOrderDownPaymentAllocationActions,
        PurchaseOrderDownPaymentRefundActions $purchaseOrderDownPaymentRefundActions,
        PurchaseOrderGlobalDiscountActions $purchaseOrderGlobalDiscountActions,
        PurchaseOrderItemActions $purchaseOrderItemActions,
    ) {
        $this->purchaseOrderDownPaymentActions = $purchaseOrderDownPaymentActions;
        $this->purchaseOrderDownPaymentAllocationActions = $purchaseOrderDownPaymentAllocationActions;
        $this->purchaseOrderDownPaymentRefundActions = $purchaseOrderDownPaymentRefundActions;
        $this->purchaseOrderGlobalDiscountActions = $purchaseOrderGlobalDiscountActions;
        $this->purchaseOrderItemActions = $purchaseOrderItemActions;
    }

    public function updateSummary(PurchaseOrder $po): void
    {
        $po->item_total_before_global_discount = $this->purchaseOrderItemActions->getSubtotalAfterDiscountAmountByPurchaseOrderId($po->id);
        $po->global_discount = $this->purchaseOrderGlobalDiscountActions->getAmountByPurchaseOrderId($po->id);
        $po->item_total_after_global_discount = $po->item_total_before_global_discount - $po->global_discount;
        $po->amount_paid_down_payment = $this->purchaseOrderDownPaymentActions->getAmountByPurchaseOrderId($po->id);
        $po->amount_allocated_down_payment = $this->purchaseOrderDownPaymentAllocationActions->getAmountByPurchaseOrderId($po->id);
        $po->amount_refunded_down_payment = $this->purchaseOrderDownPaymentRefundActions->getAmountByPurchaseOrderId($po->id);
        $po->amount_available_down_payment = $po->amount_paid_down_payment - $po->amount_allocated_down_payment - $po->amount_refunded_down_payment;
        $po->save();
    }
}
