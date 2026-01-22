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
            'company' => new CompanyResource($this->company),
            'code' => $this->code,
            'category' => new ProductCategoryResource($this->category),
            'brand' => new BrandResource($this->brand),
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
            'product_units' => ProductUnitResource::collection($this->whenLoaded('productUnits')),
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
