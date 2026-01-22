<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class SaleReceiptProductUnitResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => Hashids::encode($this->id),
            'ulid' => $this->ulid,
            'company' => new CompanyResource($this->company),
            'branch' => new BranchResource($this->branch),
            'sale_receipt' => new SaleReceiptResource($this->sale_receipt),
            'qty' => $this->qty,
            'product_id' => $this->product_id,
            'product_unit_id' => $this->product_unit_id,
            'product_unit_amount_per_unit' => $this->product_unit_amount_per_unit,
            'product_unit_amount_total' => $this->product_unit_amount_total,
            'is_has_sale' => $this->is_has_sale,
        ];
    }
}
