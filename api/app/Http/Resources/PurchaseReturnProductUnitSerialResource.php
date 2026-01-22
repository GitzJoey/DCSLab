<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class PurchaseReturnProductUnitSerialResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => Hashids::encode($this->id),
            'ulid' => $this->ulid,
            'company' => new CompanyResource($this->company),
            'branch' => new BranchResource($this->branch),
            'purchase' => new PurchaseResource($this->purchase),
            'purchase_return_product_unit' => new PurchaseReturnProductUnitResource($this->purchaseReturnProductUnit),
            'code' => $this->code,
            'remarks' => $this->remarks,
        ];
    }
}
