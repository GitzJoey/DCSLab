<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class StockTransferProductUnitSerialResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => Hashids::encode($this->id),
            'ulid' => $this->ulid,
            'company' => new CompanyResource($this->whenLoaded('company')),
            'branch' => new BranchResource($this->whenLoaded('branch')),
            'stock_transfer' => new StockTransferResource($this->whenLoaded('stockTransfer')),
            'stock_transfer_product_unit' => new StockTransferProductUnitResource($this->whenLoaded('stockTransferProductUnit')),
            'serial' => $this->serial,
        ];
    }
}
