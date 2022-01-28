<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SupplierResource extends JsonResource
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
            'name' => $this->name,
            'contact' => $this->contact,
            'address' => $this->address,
            'city' => $this->city,
            'payment_term_type' => $this->payment_term_type,
            'payment_term' => $this->payment_term,
            'taxable_enterprise' => $this->taxable_enterprise == 1 ? true : false,
            'tax_id' => $this->tax_id,
            'remarks' => $this->remarks,
            'status' => $this->status,
            'selected_products' => '',
            'main_products' => '',
            'supplier_poc' => '',
            'supplier_products' => '',
            'user' => new UserResource($this->whenLoaded('user'))
        ];
    }

    private function getSelectedProducts()
    {

    }

    private function getMainProducts()
    {

    }

    
    
}
