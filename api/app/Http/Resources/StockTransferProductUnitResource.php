<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class StockTransferProductUnitResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => Hashids::encode($this->id),
            'ulid' => $this->ulid,
            'company' => new CompanyResource($this->whenLoaded('company')),
            'branch' => new BranchResource($this->whenLoaded('branch')),
            'stock_transfer' => new StockTransferResource($this->whenLoaded('stockTransfer')),
            'qty' => $this->qty,
            'product_unit' => new ProductUnitResource($this->whenLoaded('productUnit')),
            'product_unit_conversion_value' => $this->product_unit_conversion_value,
            'product_unit_qty_base' => $this->product_unit_qty_base,
            'remarks' => $this->remarks,
            'serials' => StockTransferProductUnitSerialResource::collection($this->whenLoaded('serials')),
        ];
    }
}
