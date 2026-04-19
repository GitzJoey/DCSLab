<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class PurchaseReceiptItemSerialResource extends JsonResource
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
            $this->mergeWhen($this->relationLoaded('purchaseReceiptItem'), [
                'purchase_receipt_item' => new PurchaseReceiptItemResource($this->whenLoaded('purchaseReceiptItem')),
            ]),
            'serial' => $this->serial,
        ];
    }
}
