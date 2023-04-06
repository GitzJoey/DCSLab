<?php

namespace App\Http\Resources;

use App\Enums\RecordStatus;
use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class SupplierResource extends JsonResource
{
    protected string $type;

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
            'contact' => $this->contact,
            'address' => $this->address,
            'city' => $this->city,
            'payment_term_type' => $this->payment_term_type->name,
            'payment_term' => $this->payment_term,
            'taxable_enterprise' => $this->taxable_enterprise,
            'tax_id' => $this->tax_id,
            'remarks' => $this->remarks,
            'status' => $this->setStatus($this->status, $this->deleted_at),
            $this->mergeWhen($this->relationLoaded('user'), [
                'supplier_pic' => new UserResource($this->whenLoaded('user')),
            ]),
            $this->mergeWhen($this->relationLoaded('supplierProducts'), [
                'supplier_products' => SupplierProductResource::collection($this->whenLoaded('supplierProducts')),
                'selected_products' => $this->getSelectedProducts($this->whenLoaded('supplierProducts') ? $this->supplierProducts : null),
                'main_products' => $this->getMainProducts($this->whenLoaded('supplierProducts') ? $this->supplierProducts : null),
            ]),
        ];
    }

    private function getSelectedProducts($supplierProducts)
    {
        if (is_null($supplierProducts)) {
            return [];
        }

        return $supplierProducts->pluck('product.hId');
    }

    private function getMainProducts($supplierProducts)
    {
        if (is_null($supplierProducts)) {
            return [];
        }

        $mainProducts = $supplierProducts->where('main_product', '=', 1);

        return $mainProducts->count() != 0 ? $mainProducts->pluck('product.hId') : [];
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
