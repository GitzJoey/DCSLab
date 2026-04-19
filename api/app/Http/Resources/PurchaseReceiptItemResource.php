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
            $this->mergeWhen($this->relationLoaded('purchaseItem'), [
                'purchase_item' => new PurchaseItemResource($this->whenLoaded('purchaseItem')),
            ]),
            $this->mergeWhen($this->relationLoaded('productUnit'), [
                'product_unit' => new ProductUnitResource($this->whenLoaded('productUnit')),
            ]),
            'qty' => $this->qty,
            'product_unit_conversion_value' => $this->product_unit_conversion_value,
            'product_unit_qty_base' => $this->product_unit_qty_base,
            'remarks' => $this->remarks,

            $this->mergeWhen($this->relationLoaded('serials'), [
                'serials' => PurchaseReceiptItemSerialResource::collection($this->whenLoaded('serials')),
            ]),
        ];
    }
}
