<?php

namespace App\Http\Resources;

use App\Enums\RecordStatus;
use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class ProductResource extends JsonResource
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
            'name' => $this->name,
            'product_type' => $this->product_type->name,
            'taxable_supply' => $this->taxable_supply,
            'standard_rated_supply' => $this->standard_rated_supply,
            'price_include_vat' => $this->price_include_vat,
            'point' => $this->point,
            'use_serial_number' => $this->use_serial_number,
            'has_expiry_date' => $this->has_expiry_date,
            'status' => $this->setStatus($this->status, $this->deleted_at),
            'remarks' => $this->remarks,
            $this->mergeWhen($this->relationLoaded('productGroup'), [
                'product_group' => new ProductGroupResource($this->whenLoaded('productGroup')),
            ]),
            $this->mergeWhen($this->relationLoaded('brand'), [
                'brand' => new BrandResource($this->whenLoaded('brand')),
            ]),
            $this->mergeWhen($this->relationLoaded('productUnits'), [
                'product_units' => ProductUnitResource::collection($this->whenLoaded('productUnits')),
            ]),
        ];
    }

    private function setStatus($status, $deleted_at)
    {
        if (! is_null($deleted_at)) {
            return RecordStatus::DELETED->name;
        } else {
            return $status->name;
        }
    }
}
