<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class SaleReceiptResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => Hashids::encode($this->id),
            'ulid' => $this->ulid,
            'company' => new CompanyResource($this->company),
            'branch' => new BranchResource($this->branch),
            'code' => $this->code,
            'sale' => new SaleResource($this->sale),
            'warehouse' => new WarehouseResource($this->warehouse),
        ];
    }
}
