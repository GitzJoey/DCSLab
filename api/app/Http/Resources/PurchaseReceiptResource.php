<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class PurchaseReceiptResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => Hashids::encode($this->id),
            'ulid' => $this->ulid,
            'company' => new CompanyResource($this->company),
            'branch' => new BranchResource($this->branch),
            'code' => $this->code,
            'purchase' => new PurchaseResource($this->purchase),
            'warehouse' => new WarehouseResource($this->warehouse),
            'is_posted' => $this->is_posted,
            'is_valid' => $this->is_valid,
        ];
    }
}
