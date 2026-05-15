<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class AssetSaleItemResource extends JsonResource
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
            'remarks' => $this->remarks,
            'serials' => AssetSaleItemSerialResource::collection($this->whenLoaded('serials')),
        ];
    }
}
