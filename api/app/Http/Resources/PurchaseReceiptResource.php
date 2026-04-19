<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class PurchaseReceiptResource extends JsonResource
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
            'code' => $this->code,
            'date' => $this->date,
            $this->mergeWhen($this->relationLoaded('warehouse'), [
                'warehouse' => new WarehouseResource($this->whenLoaded('warehouse')),
            ]),
            'remarks' => $this->remarks,
            'is_posted' => $this->is_posted,

            $this->mergeWhen($this->relationLoaded('items'), [
                'items' => PurchaseReceiptItemResource::collection($this->whenLoaded('items')),
            ]),
        ];
    }
}
