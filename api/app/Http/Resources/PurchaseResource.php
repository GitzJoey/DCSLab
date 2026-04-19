<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class PurchaseResource extends JsonResource
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
            'date' => $this->date,
            'due_days' => $this->due_days,
            $this->mergeWhen($this->relationLoaded('warehouse'), [
                'warehouse' => new WarehouseResource($this->whenLoaded('warehouse')),
            ]),
            $this->mergeWhen($this->relationLoaded('supplier'), [
                'supplier' => new SupplierResource($this->whenLoaded('supplier')),
            ]),
            $this->mergeWhen($this->relationLoaded('purchaseOrder'), [
                'purchase_order' => new PurchaseOrderResource($this->whenLoaded('purchaseOrder')),
            ]),
            'tax_invoice_number' => $this->tax_invoice_number,
            'tax_invoice_vat_base' => $this->tax_invoice_vat_base,
            'tax_invoice_vat' => $this->tax_invoice_vat,
            'remarks' => $this->remarks,
            'is_posted' => $this->is_posted,
            'item_total_before_global_discount' => $this->item_total_before_global_discount,
            'global_discount' => $this->global_discount,
            'item_total_after_global_discount' => $this->item_total_after_global_discount,
            'vat_base' => $this->vat_base,
            'vat' => $this->vat,
            'item_total_after_vat' => $this->item_total_after_vat,
            'additional_cost' => $this->additional_cost,
            'rounding' => $this->rounding,
            'amount_payable' => $this->amount_payable,
            'amount_paid_by_purchase_order_down_payment' => $this->amount_paid_by_purchase_order_down_payment,
            'amount_paid_by_purchase_return' => $this->amount_paid_by_purchase_return,
            'amount_paid_total' => $this->amount_paid_total,
            'amount_due' => $this->amount_due,
            'is_paid_off' => $this->is_paid_off,
            $this->mergeWhen($this->relationLoaded('items'), [
                'items' => PurchaseItemResource::collection($this->whenLoaded('items')),
            ]),
            $this->mergeWhen($this->relationLoaded('globalDiscounts'), [
                'global_discounts' => PurchaseGlobalDiscountResource::collection($this->whenLoaded('globalDiscounts')),
            ]),
            $this->mergeWhen($this->relationLoaded('additionalCosts'), [
                'additional_costs' => PurchaseAdditionalCostResource::collection($this->whenLoaded('additionalCosts')),
            ]),
        ];
    }
}
