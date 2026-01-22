<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class ProductUnitResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => Hashids::encode($this->id),
            'ulid' => $this->ulid,
            'company' => new CompanyResource($this->company),
            'product' => new ProductResource($this->whenLoaded('product')),
            'code' => $this->code,
            'is_manufacturer_sku' => $this->is_manufacturer_sku,
            'unit' => new UnitResource($this->unit),
            'price' => $this->price,
            'is_base' => $this->is_base,
            'conversion_value' => $this->conversion_value,
            'base_unit_price' => $this->conversion_value > 0 ? $this->price / $this->conversion_value : 0,
            'is_primary_unit' => $this->is_primary_unit,
            'point' => $this->point,
            'remarks' => $this->remarks,
        ];
    }
}
