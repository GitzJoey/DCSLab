<?php

namespace App\Http\Resources;

use App\Helpers\TimezoneHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class PurchaseOrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => Hashids::encode($this->id),
            'ulid' => $this->ulid,
            $this->mergeWhen($this->relationLoaded('company'), [
                'company' => new CompanyResource($this->whenLoaded('company')),
            ]),
            $this->mergeWhen($this->relationLoaded('branch'), [
                'branch' => new BranchResource($this->whenLoaded('branch')),
            ]),
            'code' => $this->code,
            'date' => TimezoneHelper::convertFromUTCIfValid($this->date),
            'due_days' => $this->due_days,
            $this->mergeWhen($this->relationLoaded('supplier'), [
                'supplier' => new SupplierResource($this->whenLoaded('supplier')),
            ]),
            'remarks' => $this->remarks,
            'item_total_before_global_discount' => dec_trim($this->item_total_before_global_discount),
            'global_discount' => dec_trim($this->global_discount),
            'item_total_after_global_discount' => dec_trim($this->item_total_after_global_discount),
            'vat_base' => dec_trim($this->vat_base),
            'vat' => dec_trim($this->vat),
            'item_total_after_vat' => dec_trim($this->item_total_after_vat),
            'rounding' => dec_trim($this->rounding),
            'amount_payable' => dec_trim($this->amount_payable),
            'amount_paid_down_payment' => dec_trim($this->amount_paid_down_payment),
            'amount_allocated_down_payment' => dec_trim($this->amount_allocated_down_payment),
            'amount_refunded_down_payment' => dec_trim($this->amount_refunded_down_payment),
            'amount_available_down_payment' => dec_trim($this->amount_available_down_payment),
            'global_discounts' => PurchaseOrderGlobalDiscountResource::collection($this->whenLoaded('globalDiscounts')),
            'items' => PurchaseOrderItemResource::collection($this->whenLoaded('items')),
            'down_payments' => PurchaseOrderDownPaymentResource::collection($this->whenLoaded('downPayments')),
            'refunded_down_payments' => PurchaseOrderDownPaymentRefundResource::collection($this->whenLoaded('refundedDownPayments')),
        ];
    }
}
