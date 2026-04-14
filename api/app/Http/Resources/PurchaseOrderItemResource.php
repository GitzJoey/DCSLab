<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class PurchaseOrderItemResource extends JsonResource
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
            $this->mergeWhen($this->relationLoaded('purchaseOrder'), [
                'purchase_order' => new PurchaseOrderResource($this->whenLoaded('purchaseOrder')),
            ]),
            'qty' => $this->qty,
            $this->mergeWhen($this->relationLoaded('productUnit'), [
                'product_unit' => new ProductUnitResource($this->whenLoaded('productUnit')),
            ]),
            'product_unit_conversion_value' => $this->product_unit_conversion_value,
            'product_unit_qty_base' => $this->product_unit_qty_base,
            'product_unit_price' => $this->product_unit_price,
            'product_unit_is_price_include_vat' => $this->product_unit_is_price_include_vat,
            'price_discount' => $this->price_discount,
            'product_unit_price_discounts' => PurchaseOrderItemProductUnitPriceDiscountResource::collection($this->whenLoaded('productUnitPriceDiscounts')),
            'price_after_discount' => $this->price_after_discount,
            'subtotal' => $this->subtotal,
            'subtotal_discount' => $this->subtotal_discount,
            'subtotal_discounts' => PurchaseOrderItemSubtotalDiscountResource::collection($this->whenLoaded('subtotalDiscounts')),
            'subtotal_after_discount' => $this->subtotal_after_discount,
            'global_discount' => $this->global_discount,
            'total_before_vat' => $this->total_before_vat,
            $this->mergeWhen($this->relationLoaded('vatProfile'), [
                'vat_profile' => new VatProfileResource($this->whenLoaded('vatProfile')),
            ]),
            'vat_rate' => $this->vat_rate,
            'vat_base_numerator' => $this->vat_base_numerator,
            'vat_base_denominator' => $this->vat_base_denominator,
            'vat_base' => $this->vat_base,
            'vat' => $this->vat,
            'rounding' => $this->rounding,
            'grand_total' => $this->grand_total,
            'cogs' => $this->cogs,
            'total_cogs' => $this->total_cogs,
            'base_unit_cogs' => $this->base_unit_cogs,
            'remarks' => $this->remarks,
        ];
    }
}
