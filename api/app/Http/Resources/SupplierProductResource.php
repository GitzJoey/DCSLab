<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class SupplierProductResource extends JsonResource
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
            'company' => new CompanyResource($this->company),
            $this->mergeWhen($this->relationLoaded('supplier'), [
                'supplier' => new SupplierResource($this->supplier),
            ]),
            $this->mergeWhen($this->relationLoaded('product'), [
                'product' => new ProductResource($this->product),
            ]),
            'main_product' => $this->main_product,
        ];
    }
}
