<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            'hId' => $this->hId,
            'code' => $this->code,
            'name' => $this->name,
            'product_type' => $this->product_type,
            'taxable_supply' => $this->taxable_supply == 1 ? true : false,
            'standard_rated_supply' => $this->standard_rated_supply,
            'price_include_vat' => $this->price_include_vat == 1 ? true : false,
            'point' => $this->point,
            'use_serial_number' => $this->use_serial_number == 1 ? true : false,
            'has_expiry_date' => $this->has_expiry_date == 1 ? true : false,
            'status' => $this->status == 1 ? true : false,
            'remarks' => $this->remarks,
            'brand' => '',
            'product_group' => ''
        ];
    }
}
