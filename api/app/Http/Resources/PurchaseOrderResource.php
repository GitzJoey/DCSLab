<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class PurchaseOrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => Hashids::encode($this->id),
            'ulid' => $this->ulid,
            'company' => new CompanyResource($this->company),
            'branch' => new BranchResource($this->branch),
            'supplier' => new SupplierResource($this->supplier),
            'code' => $this->code,
            'date' => $this->date,
            'shipping_date' => $this->shipping_date,
            'shipping_address' => $this->shipping_address,
            'remarks' => $this->remarks,
            'is_has_invoice' => $this->is_has_invoice,
            'is_received' => $this->is_received,
            'total' => $this->total,
            'global_discount_rate' => $this->global_discount_rate,
            'global_discount_fixed' => $this->global_discount_fixed,
            'grand_total' => $this->grand_total,
            'down_payment' => $this->down_payment,
            'down_payment_due_days' => $this->down_payment_due_days,
            'down_payment_applied' => $this->down_payment_applied,
            'down_payment_remaining' => $this->down_payment_remaining,
            'is_down_payment_paid_off' => $this->is_down_payment_paid_off,
        ];
    }
}
