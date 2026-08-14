<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class AssetResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => Hashids::encode($this->id),
            'ulid' => $this->ulid,
            $this->mergeWhen($this->relationLoaded('company'), [
                'company' => new CompanyResource($this->whenLoaded('company')),
            ]),
            $this->mergeWhen($this->relationLoaded('assetCategory'), [
                'asset_category' => new AssetCategoryResource($this->whenLoaded('assetCategory')),
            ]),
            'code' => $this->code,
            'name' => $this->name,
            $this->mergeWhen($this->relationLoaded('assetUnit'), [
                'asset_unit' => new AssetUnitResource($this->whenLoaded('assetUnit')),
            ]),
            'status' => $this->status?->value,
            'remarks' => $this->remarks,
        ];
    }
}
