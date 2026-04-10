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
            'product_unit_price_discount' => $this->product_unit_price_discount,
            'product_unit_price_discounts' => PurchaseOrderItemProductUnitPriceDiscountResource::collection($this->whenLoaded('productUnitPriceDiscounts')),
            'product_unit_price_after_discount' => $this->product_unit_price_after_discount,
            'product_unit_subtotal' => $this->product_unit_subtotal,
            'product_unit_subtotal_discount' => $this->product_unit_subtotal_discount,
            'subtotal_discounts' => PurchaseOrderItemSubtotalDiscountResource::collection($this->whenLoaded('subtotalDiscounts')),
            'product_unit_subtotal_after_discount' => $this->product_unit_subtotal_after_discount,
            'product_unit_global_discount' => $this->product_unit_global_discount,
            'product_unit_total_before_vat' => $this->product_unit_total_before_vat,
            'is_vat_included' => $this->is_vat_included,
            $this->mergeWhen($this->relationLoaded('vatProfile'), [
                'vat_profile' => new VatProfileResource($this->whenLoaded('vatProfile')),
            ]),
            'vat_rate' => $this->vat_rate,
            'vat_base_numerator' => $this->vat_base_numerator,
            'vat_base_denominator' => $this->vat_base_denominator,
            'product_unit_vat_base' => $this->product_unit_vat_base,
            'product_unit_vat' => $this->product_unit_vat,
            'product_unit_rounding' => $this->product_unit_rounding,
            'product_unit_grand_total' => $this->product_unit_grand_total,
            'product_unit_cogs' => $this->product_unit_cogs,
            'product_unit_total_cogs' => $this->product_unit_total_cogs,
            'product_unit_base_unit_cogs' => $this->product_unit_base_unit_cogs,
            'remarks' => $this->remarks,
        ];
    }
}
