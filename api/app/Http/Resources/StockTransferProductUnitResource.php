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
            'company' => new CompanyResource($this->company),
            'branch' => new BranchResource($this->branch),
            'stock_transfer' => new StockTransferResource($this->stockTransfer),
            'qty' => $this->qty,
            'product_id' => $this->product_id,
            'product_unit_id' => $this->product_unit_id,
            'remarks' => $this->remarks,
        ];
    }
}
