<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class StockAdjustmentInItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => Hashids::encode($this->id),
            'ulid' => $this->ulid,
            'company' => new CompanyResource($this->whenLoaded('company')),
            'branch' => new BranchResource($this->whenLoaded('branch')),
            'stock_adjustment' => new StockAdjustmentResource($this->whenLoaded('stockAdjustment')),
            'qty' => dec_trim($this->qty),
            'product_unit' => new ProductUnitResource($this->whenLoaded('productUnit')),
            'product_unit_conversion_value' => dec_trim($this->product_unit_conversion_value),
            'product_unit_qty_base' => dec_trim($this->product_unit_qty_base),
            'product_unit_cogs' => dec_trim($this->product_unit_cogs),
            'product_unit_total_cogs' => dec_trim($this->product_unit_total_cogs),
            'product_unit_base_unit_cogs' => dec_trim($this->product_unit_base_unit_cogs),
            'remarks' => $this->remarks,
            'serials' => StockAdjustmentInItemSerialResource::collection($this->whenLoaded('serials')),
        ];
    }
}
