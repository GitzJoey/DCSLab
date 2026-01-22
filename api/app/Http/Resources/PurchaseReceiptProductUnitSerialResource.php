<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class PurchaseReceiptProductUnitSerialResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => Hashids::encode($this->id),
            'ulid' => $this->ulid,
            'company' => new CompanyResource($this->company),
            'branch' => new BranchResource($this->branch),
            'purchase_receipt' => new PurchaseReceiptResource($this->purchase_receipt),
            'purchase_receipt_product_unit' => new PurchaseReceiptProductUnitResource($this->purchase_receipt_product_unit),
            'serial' => $this->serial,
        ];
    }
}
