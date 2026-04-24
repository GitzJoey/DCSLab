<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class PurchaseItemResource extends JsonResource
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
            $this->mergeWhen($this->relationLoaded('purchase'), [
                'purchase' => new PurchaseResource($this->whenLoaded('purchase')),
            ]),
            $this->mergeWhen($this->relationLoaded('purchaseOrderItem'), [
                'purchase_order_item' => new PurchaseOrderItemResource(
                    $this->whenLoaded('purchaseOrderItem')
                ),
            ]),
            $this->mergeWhen($this->relationLoaded('productUnit'), [
                'product_unit' => new ProductUnitResource($this->whenLoaded('productUnit')),
            ]),
            $this->mergeWhen($this->relationLoaded('vatProfile'), [
                'vat_profile' => new VatProfileResource($this->whenLoaded('vatProfile')),
            ]),
            $this->mergeWhen($this->relationLoaded('productUnitPriceDiscounts'), [
                'product_unit_price_discounts' => PurchaseItemProductUnitPriceDiscountResource::collection(
                    $this->whenLoaded('productUnitPriceDiscounts')
                ),
            ]),
            $this->mergeWhen($this->relationLoaded('subtotalDiscounts'), [
                'subtotal_discounts' => PurchaseItemSubtotalDiscountResource::collection(
                    $this->whenLoaded('subtotalDiscounts')
                ),
            ]),

            'qty' => $this->qty,
            'product_unit_conversion_value' => $this->product_unit_conversion_value,
            'product_unit_qty_base' => $this->product_unit_qty_base,
            'qty_received_base' => $this->qty_received_base,
            'qty_outstanding_base' => $this->qty_outstanding_base,
            'qty_excess_base' => $this->qty_excess_base,
            'product_unit_price' => $this->product_unit_price,
            'product_unit_is_price_include_vat' => $this->product_unit_is_price_include_vat,

            'price_discount' => $this->price_discount,
            'price_after_discount' => $this->price_after_discount,
            'subtotal' => $this->subtotal,
            'subtotal_discount' => $this->subtotal_discount,
            'subtotal_after_discount' => $this->subtotal_after_discount,
            'global_discount' => $this->global_discount,
            'subtotal_after_global_discount' => $this->subtotal_after_global_discount,

            'vat_rate' => $this->vat_rate,
            'vat_base_numerator' => $this->vat_base_numerator,
            'vat_base_denominator' => $this->vat_base_denominator,
            'vat_base' => $this->vat_base,
            'vat' => $this->vat,
            'subtotal_after_vat' => $this->subtotal_after_vat,

            'additional_cost' => $this->additional_cost,
            'rounding' => $this->rounding,
            'amount_payable' => $this->amount_payable,
            'cogs' => $this->cogs,
            'total_cogs' => $this->total_cogs,
            'base_unit_cogs' => $this->base_unit_cogs,

            'remarks' => $this->remarks,
        ];
    }
}
