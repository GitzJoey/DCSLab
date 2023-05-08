<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class ProductUnitResource extends JsonResource
{
    protected string $type = '';

    public function type(string $value)
    {
        $this->type = $value;

        return $this;
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => Hashids::encode($this->id),
            'ulid' => $this->ulid,
            'code' => $this->code,
            $this->mergeWhen($this->relationLoaded('unit'), [
                'unit' => new UnitResource($this->whenLoaded('unit')),
            ]),
            'is_base' => $this->is_base,
            'conversion_value' => $this->conversion_value,
            'is_primary_unit' => $this->is_primary_unit,
            'remarks' => $this->remarks,
        ];
    }
}
