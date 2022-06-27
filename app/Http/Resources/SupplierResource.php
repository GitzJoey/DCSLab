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
            'hId' => $this->hId,
            'uuid' => $this->uuid,
            'code' => $this->code,
            'name' => $this->name,
            'contact' => $this->contact,
            'address' => $this->address,
            'city' => $this->city,
            'payment_term_type' => $this->payment_term_type->name,
            'payment_term' => $this->payment_term,
            'taxable_enterprise' => $this->taxable_enterprise,
            'tax_id' => $this->tax_id,
            'remarks' => $this->remarks,
            'status' => $this->status->name,
            $this->mergeWhen($this->relationLoaded('supplierProducts'), [
                'supplier_products' => SupplierProductResource::collection($this->whenLoaded('supplierProducts')),
                'selected_products' => $this->getSelectedProducts($this->whenLoaded('supplierProducts') ? $this->supplierProducts : null),
                'main_products' => $this->getMainProducts($this->whenLoaded('supplierProducts') ? $this->supplierProducts : null)
            ]),
            $this->mergeWhen($this->relationLoaded('user'), [
                'supplier_poc' => new UserResource($this->whenLoaded('user'))
            ])
        ];
    }

    private function getSelectedProducts($supplierProducts)
    {
        if (is_null($supplierProducts)) return [];

        return $supplierProducts->pluck('product.hId');
    }

    private function getMainProducts($supplierProducts)
    {
        if (is_null($supplierProducts)) return [];
        
        $mainProducts = $supplierProducts->where('main_product', '=', 1);

        return $mainProducts->count() != 0 ? $mainProducts->pluck('product.hId') : [];
    }
}
