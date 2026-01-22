<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class SupplierResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => Hashids::encode($this->id),
            'ulid' => $this->ulid,
            'company' => new CompanyResource($this->company),
            'user_id' => new UserResource($this->user),
            'code' => $this->code,
            'name' => $this->name,
            'address' => $this->address,
            'payment_term_type' => $this->payment_term_type,
            'payment_term' => $this->payment_term,
            'taxable_enterprise' => $this->taxable_enterprise,
            'tax_id' => $this->tax_id,
            'status' => $this->status,
            'remarks' => $this->remarks,
        ];
    }
}
