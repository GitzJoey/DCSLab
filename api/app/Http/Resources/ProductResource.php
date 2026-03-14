<?php

namespace App\Http\Resources;

use App\Enums\RecordStatusEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => Hashids::encode($this->id),
            'ulid' => $this->ulid,
            $this->mergeWhen($this->relationLoaded('company'), [
                'company' => new CompanyResource($this->whenLoaded('company')),
            ]),
            'code' => $this->code,
            $this->mergeWhen($this->relationLoaded('category'), [
                'category' => new ProductCategoryResource($this->whenLoaded('category')),
            ]),
            $this->mergeWhen($this->relationLoaded('brand'), [
                'brand' => new BrandResource($this->whenLoaded('brand')),
            ]),
            'name' => $this->name,
            'slug' => $this->slug,
            'is_taxable' => $this->is_taxable,
            'vat_rate' => $this->vat_rate,
            'is_price_include_vat' => $this->is_price_include_vat,
            'is_use_serial_number' => $this->is_use_serial_number,
            'is_expirable' => $this->is_expirable,
            'remarks' => $this->remarks,
            'type' => $this->type,
            'status' => $this->setStatus($this->status, $this->deleted_at),
            'remaining_stock_base_unit' => $this->when(
                isset($this->remaining_stock),
                (float) $this->remaining_stock
            ),
            $this->mergeWhen($this->relationLoaded('productUnits'), [
                'product_units' => ProductUnitResource::collection($this->whenLoaded('productUnits')),
            ]),
            $this->mergeWhen($this->relationLoaded('images'), [
                'product_images' => ProductImageResource::collection($this->whenLoaded('images')),
            ]),
        ];
    }

    private function setStatus($status, $deleted_at)
    {
        if (! is_null($deleted_at)) {
            return RecordStatusEnum::DELETED->name;
        } else {
            return $status->name;
        }
    }
}
