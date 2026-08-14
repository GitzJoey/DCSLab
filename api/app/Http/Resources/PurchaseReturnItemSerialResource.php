<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class PurchaseReturnItemSerialResource extends JsonResource
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
            $this->mergeWhen($this->relationLoaded('purchaseReturn'), [
                'purchase_return' => new PurchaseReturnResource($this->whenLoaded('purchaseReturn')),
            ]),
            $this->mergeWhen($this->relationLoaded('purchaseReturnItem'), [
                'purchase_return_item' => new PurchaseReturnItemResource($this->whenLoaded('purchaseReturnItem')),
            ]),
            'serial' => $this->serial,
        ];
    }
}
