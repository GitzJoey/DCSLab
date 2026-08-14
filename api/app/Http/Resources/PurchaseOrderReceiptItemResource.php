<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class PurchaseOrderReceiptItemResource extends JsonResource
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
            $this->mergeWhen($this->relationLoaded('purchaseOrderReceipt'), [
                'purchase_order_receipt' => new PurchaseOrderReceiptResource($this->whenLoaded('purchaseOrderReceipt')),
            ]),
            $this->mergeWhen($this->relationLoaded('purchaseOrderItem'), [
                'purchase_order_item' => new PurchaseOrderItemResource($this->whenLoaded('purchaseOrderItem')),
            ]),
            'has_purchase_order_item_product' => $this->has_purchase_order_item_product,
            'qty' => dec_trim($this->qty),
            $this->mergeWhen($this->relationLoaded('productUnit'), [
                'product_unit' => new ProductUnitResource($this->whenLoaded('productUnit')),
            ]),
            $this->mergeWhen($this->relationLoaded('product'), [
                'product' => new ProductResource($this->whenLoaded('product')),
            ]),
            'product_unit_conversion_value' => dec_trim($this->product_unit_conversion_value),
            'product_unit_qty_base' => dec_trim($this->product_unit_qty_base),
            'base_unit_value' => dec_trim($this->base_unit_value),
            'total_value' => dec_trim($this->total_value),
            'remarks' => $this->remarks,
            $this->mergeWhen($this->relationLoaded('serials'), [
                'serials' => PurchaseOrderReceiptItemSerialResource::collection($this->whenLoaded('serials')),
            ]),
        ];
    }
}
