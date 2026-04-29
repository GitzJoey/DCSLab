<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class PurchaseReceiptItemResource extends JsonResource
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
            $this->mergeWhen($this->relationLoaded('purchaseReceipt'), [
                'purchase_receipt' => new PurchaseReceiptResource($this->whenLoaded('purchaseReceipt')),
            ]),
            'has_purchase_item_product' => (bool) $this->has_purchase_item_product,
            'qty' => dec_trim($this->qty),
            $this->mergeWhen($this->relationLoaded('productUnit'), [
                'product_unit' => new ProductUnitResource($this->whenLoaded('productUnit')),
            ]),
            'product_unit_conversion_value' => dec_trim($this->product_unit_conversion_value),
            'product_unit_qty_base' => dec_trim($this->product_unit_qty_base),
            'remarks' => $this->remarks,

            $this->mergeWhen($this->relationLoaded('serials'), [
                'serials' => PurchaseReceiptItemSerialResource::collection($this->whenLoaded('serials')),
            ]),
        ];
    }
}
