<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class PurchaseItemProductUnitPriceDiscountResource extends JsonResource
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
            $this->mergeWhen($this->relationLoaded('purchaseItem'), [
                'purchase_item' => new PurchaseItemResource($this->whenLoaded('purchaseItem')),
            ]),
            'sequence' => $this->sequence,
            'discount_type' => $this->discount_type?->value,
            'discount_value' => dec_trim($this->discount_value),
        ];
    }
}
