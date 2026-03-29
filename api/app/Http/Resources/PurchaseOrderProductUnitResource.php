<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class PurchaseOrderProductUnitResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $discounts = $this->relationLoaded('discounts') ? $this->discounts : collect();
        $discountResources = PurchaseOrderProductUnitDiscountResource::collection($discounts);

        return [
            'id' => Hashids::encode($this->id),
            'ulid' => $this->ulid,
            'company' => new CompanyResource($this->whenLoaded('company')),
            'branch' => new BranchResource($this->whenLoaded('branch')),
            'purchase_order' => new PurchaseOrderResource($this->whenLoaded('purchaseOrder')),
            'qty' => $this->qty,
            'product' => new ProductResource($this->whenLoaded('product')),
            'product_unit' => new ProductUnitResource($this->whenLoaded('productUnit')),
            'product_unit_amount_per_unit' => $this->product_unit_amount_per_unit,
            'product_unit_amount_total' => $this->product_unit_amount_total,
            'product_unit_initial_price' => $this->product_unit_initial_price,
            'discounts' => $discountResources,
            'product_unit_discount_rate1' => $this->getDiscountValue($discounts, 1, 'rate'),
            'product_unit_discount_rate2' => $this->getDiscountValue($discounts, 2, 'rate'),
            'product_unit_discount_rate3' => $this->getDiscountValue($discounts, 3, 'rate'),
            'product_unit_discount_rate4' => $this->getDiscountValue($discounts, 4, 'rate'),
            'product_unit_discount_rate5' => $this->getDiscountValue($discounts, 5, 'rate'),
            'product_unit_discount_fixed1' => $this->getDiscountValue($discounts, 1, 'fixed'),
            'product_unit_discount_fixed2' => $this->getDiscountValue($discounts, 2, 'fixed'),
            'product_unit_discount_fixed3' => $this->getDiscountValue($discounts, 3, 'fixed'),
            'product_unit_discount_fixed4' => $this->getDiscountValue($discounts, 4, 'fixed'),
            'product_unit_discount_fixed5' => $this->getDiscountValue($discounts, 5, 'fixed'),
            'product_unit_net_price' => $this->product_unit_net_price,
            'product_unit_subtotal' => $this->product_unit_subtotal,
            'product_unit_subtotal_discount_rate' => $this->product_unit_subtotal_discount_rate,
            'product_unit_subtotal_discount_fixed' => $this->product_unit_subtotal_discount_fixed,
            'product_unit_total' => $this->product_unit_total,
            'product_unit_global_discount_rate' => $this->product_unit_global_discount_rate,
            'product_unit_global_discount_fixed' => $this->product_unit_global_discount_fixed,
            'product_unit_grand_total' => $this->product_unit_grand_total,
            'product_is_taxable' => $this->product_is_taxable,
            'product_vat_rate' => $this->product_vat_rate,
            'product_price_include_vat' => $this->product_is_price_include_vat,
            'product_vat_base' => $this->product_vat_base,
            'product_vat' => $this->product_vat,
            'product_unit_final_price' => $this->product_unit_final_price,
            'product_final_price_base_unit' => $this->product_final_price_base_unit,
            'remarks' => $this->remarks,
        ];
    }

    private function getDiscountValue($discounts, int $sequence, string $field): string|int|float
    {
        return collect($discounts)
            ->firstWhere('sequence', $sequence)?->{$field} ?? 0;
    }
}
