<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class StockTransferResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => Hashids::encode($this->id),
            'ulid' => $this->ulid,
            'company' => new CompanyResource($this->company),
            'branch' => new BranchResource($this->branch),
            'code' => $this->code,
            'date' => $this->date,
            'source_warehouse' => new WarehouseResource($this->warehouse),
            'destination_warehouse' => new WarehouseResource($this->warehouse),
            'remarks' => $this->remarks,
            'is_posted' => $this->is_posted,
        ];
    }
}
