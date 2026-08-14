<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class SalesInvoiceItemResource extends JsonResource
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
            $this->mergeWhen($this->relationLoaded('salesInvoice'), [
                'sales_invoice' => new SalesInvoiceResource($this->whenLoaded('salesInvoice')),
            ]),
            $this->mergeWhen($this->relationLoaded('salesOrderItem'), [
                'sales_order_item' => new SalesOrderItemResource($this->whenLoaded('salesOrderItem')),
            ]),
            'qty' => dec_trim($this->qty),
            $this->mergeWhen($this->relationLoaded('productUnit'), [
                'product_unit' => new ProductUnitResource($this->whenLoaded('productUnit')),
            ]),
            'product_unit_conversion_value' => dec_trim($this->product_unit_conversion_value),
            'product_unit_qty_base' => dec_trim($this->product_unit_qty_base),
            'product_unit_price' => dec_trim($this->product_unit_price),
            'product_unit_is_price_include_vat' => $this->product_unit_is_price_include_vat,
            'price_discount' => dec_trim($this->price_discount),
            'price_after_discount' => dec_trim($this->price_after_discount),
            'subtotal' => dec_trim($this->subtotal),
            'subtotal_discount' => dec_trim($this->subtotal_discount),
            'subtotal_after_discount' => dec_trim($this->subtotal_after_discount),
            'global_discount' => dec_trim($this->global_discount),
            'subtotal_after_global_discount' => dec_trim($this->subtotal_after_global_discount),
            $this->mergeWhen($this->relationLoaded('vatProfile'), [
                'vat_profile' => new VatProfileResource($this->whenLoaded('vatProfile')),
            ]),
            'vat_rate' => dec_trim($this->vat_rate),
            'vat_base_numerator' => $this->vat_base_numerator,
            'vat_base_denominator' => $this->vat_base_denominator,
            'vat_base' => dec_trim($this->vat_base),
            'vat' => dec_trim($this->vat),
            'subtotal_after_vat' => dec_trim($this->subtotal_after_vat),
            'rounding' => dec_trim($this->rounding),
            'amount_payable' => dec_trim($this->amount_payable),
            'remarks' => $this->remarks,
        ];
    }
}
