<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class PurchaseReceiptProductUnitResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => Hashids::encode($this->id),
            'ulid' => $this->ulid,
            'company' => new CompanyResource($this->company),
            'branch' => new BranchResource($this->branch),
            'purchase_receipt' => new PurchaseReceiptResource($this->purchase_receipt),
            'purchase' => new PurchaseResource($this->purchase),
            'qty' => $this->qty,
            'product' => new ProductResource($this->product),
            'product_unit' => new ProductUnitResource($this->product_unit),
            'product_unit_amount_per_unit' => $this->product_unit_amount_per_unit,
            'product_unit_amount_total' => $this->product_unit_amount_total,
            'is_has_purchase' => $this->is_has_purchase,
        ];
    }
}
