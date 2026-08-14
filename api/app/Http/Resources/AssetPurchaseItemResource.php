<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class AssetPurchaseItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => Hashids::encode($this->id),
            'ulid' => $this->ulid,
            'asset' => new AssetResource($this->whenLoaded('asset')),
            'qty' => dec_trim($this->qty),
            'unit_price' => dec_trim($this->unit_price),
            'subtotal' => dec_trim($this->subtotal),
            'allocated_additional_cost' => dec_trim($this->allocated_additional_cost),
            'subtotal_after_additional_cost' => dec_trim($this->subtotal_after_additional_cost),
            'unit_acquisition_cost' => dec_trim($this->unit_acquisition_cost),
            'remarks' => $this->remarks,
            'serials' => AssetPurchaseItemSerialResource::collection($this->whenLoaded('serials')),
        ];
    }
}
