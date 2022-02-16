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
            $this->mergeWhen($this->whenLoaded('supplier_products'), [
                'supplier_products' => SupplierProductResource::collection($this->supplierProducts),
                'selected_products' => $this->getSelectedProducts($this->supplierProducts),
                'main_products' => $this->getMainProducts($this->supplierProducts)
            ]),
            $this->mergeWhen($this->whenLoaded('user'), [
                'supplier_poc' => new UserResource($this->user)
            ])
        ];
    }

    private function getSelectedProducts($supplierProducts)
    {
        return $supplierProducts->pluck('product.hId');
    }

    private function getMainProducts($supplierProducts)
    {
        return $supplierProducts->where('main_product', '=', 1)->pluck('product.hId');
    }
}
