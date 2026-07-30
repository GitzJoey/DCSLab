<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class PurchaseOrderReceiptItemSerialResource extends JsonResource
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
            $this->mergeWhen($this->relationLoaded('purchaseOrderReceiptItem'), [
                'purchase_order_receipt_item' => new PurchaseOrderReceiptItemResource($this->whenLoaded('purchaseOrderReceiptItem')),
            ]),
            'serial' => $this->serial,
        ];
    }
}
