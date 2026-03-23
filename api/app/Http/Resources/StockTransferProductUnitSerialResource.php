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
            $this->mergeWhen($this->relationLoaded('company'), [
                'company' => new CompanyResource($this->whenLoaded('company')),
            ]),
            $this->mergeWhen($this->relationLoaded('branch'), [
                'branch' => new BranchResource($this->whenLoaded('branch')),
            ]),
            $this->mergeWhen($this->relationLoaded('stockTransfer'), [
                'stock_transfer' => new StockTransferResource($this->whenLoaded('stockTransfer')),
            ]),
            $this->mergeWhen($this->relationLoaded('stockTransferProductUnit'), [
                'stock_transfer_product_unit' => new StockTransferProductUnitResource($this->whenLoaded('stockTransferProductUnit')),
            ]),
            'serial' => $this->serial,
        ];
    }
}
