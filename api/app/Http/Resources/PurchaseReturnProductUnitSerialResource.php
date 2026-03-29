<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class PurchaseReturnProductUnitSerialResource extends JsonResource
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
            $this->mergeWhen($this->relationLoaded('purchase'), [
                'purchase' => new PurchaseResource($this->whenLoaded('purchase')),
            ]),
            $this->mergeWhen($this->relationLoaded('purchaseReturnProductUnit'), [
                'purchase_return_product_unit' => new PurchaseReturnProductUnitResource($this->whenLoaded('purchaseReturnProductUnit')),
            ]),
            'serial' => $this->serial,
        ];
    }
}
