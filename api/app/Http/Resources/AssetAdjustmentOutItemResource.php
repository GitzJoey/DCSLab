<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class AssetAdjustmentOutItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => Hashids::encode($this->id),
            'ulid' => $this->ulid,
            'asset' => new AssetResource($this->whenLoaded('asset')),
            'qty' => dec_trim($this->qty),
            'remarks' => $this->remarks,
            'serials' => AssetAdjustmentOutItemSerialResource::collection($this->whenLoaded('serials')),
        ];
    }
}
