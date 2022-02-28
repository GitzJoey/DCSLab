<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductUnitResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'code' => $this->code,
            'unit' => new UnitResource($this->unit),
            'is_base' => $this->is_base == 1 ? true : false,
            'conversion_value' => $this->conversion_value,
            'is_primary_unit' => $this->is_primary_unit == 1 ? true : false,
            'remarks' => $this->remarks
        ];
    }
}
