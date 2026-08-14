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
            $this->mergeWhen($this->relationLoaded('company'), [
                'company' => new CompanyResource($this->whenLoaded('company')),
            ]),
            $this->mergeWhen($this->relationLoaded('product'), [
                'product' => new ProductResource($this->whenLoaded('product')),
            ]),
            $this->mergeWhen(
                $this->relationLoaded('product')
                && $this->product
                && $this->product->relationLoaded('images'),
                function () {
                    return [
                        'product_images' => ProductImageResource::collection($this->product->images),
                    ];
                }
            ),
            'code' => $this->code,
            'is_manufacturer_sku' => $this->is_manufacturer_sku,
            $this->mergeWhen($this->relationLoaded('unit'), [
                'unit' => new UnitResource($this->whenLoaded('unit')),
            ]),
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
