<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class SaleProductUnitResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => Hashids::encode($this->id),
            'ulid' => $this->ulid,
            'company' => new CompanyResource($this->company),
            'branch_id' => new BranchResource($this->branch),
            'sale_id' => new SaleResource($this->sale),
            'warehouse_id' => new WarehouseResource($this->warehouse),

            'qty' => $this->qty,
            'product_id' => new ProductResource($this->product),
            'product_unit_id' => new ProductUnitResource($this->product_unit),
            'product_unit_amount_per_unit' => $this->product_unit_amount_per_unit,
            'product_unit_amount_total' => $this->product_unit_amount_total,
            'product_unit_initial_price' => $this->product_unit_initial_price,
            'product_unit_discount_rate1' => $this->product_unit_discount_rate1,
            'product_unit_discount_rate2' => $this->product_unit_discount_rate2,
            'product_unit_discount_rate3' => $this->product_unit_discount_rate3,
            'product_unit_discount_rate4' => $this->product_unit_discount_rate4,
            'product_unit_discount_rate5' => $this->product_unit_discount_rate5,
            'product_unit_discount_fixed1' => $this->product_unit_discount_fixed1,
            'product_unit_discount_fixed2' => $this->product_unit_discount_fixed2,
            'product_unit_discount_fixed3' => $this->product_unit_discount_fixed3,
            'product_unit_discount_fixed4' => $this->product_unit_discount_fixed4,
            'product_unit_discount_fixed5' => $this->product_unit_discount_fixed5,
            'product_unit_net_price' => $this->product_unit_net_price,
            'product_unit_subtotal' => $this->product_unit_subtotal,
            'product_unit_subtotal_discount_rate' => $this->product_unit_subtotal_discount_rate,
            'product_unit_subtotal_discount_fixed' => $this->product_unit_subtotal_discount_fixed,
            'product_unit_total' => $this->product_unit_total,

            'product_is_taxable' => $this->product_is_taxable,
            'product_vat_rate' => $this->product_vat_rate,
            'product_price_include_vat' => $this->product_price_include_vat,
            'product_vat_base' => $this->product_vat_base,
            'product_vat' => $this->product_vat,

            'product_unit_final_price' => $this->product_unit_final_price,

            'is_received' => $this->is_received,
            'is_valid' => $this->is_valid,
        ];
    }
}
