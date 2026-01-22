<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class PurchasePaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => Hashids::encode($this->id),
            'ulid' => $this->ulid,
            'company' => new CompanyResource($this->company),
            'branch' => new BranchResource($this->branch),
            'purchase' => new PurchaseResource($this->purchase),
            'code' => $this->code,
            'date' => $this->date,
            'cash_account' => new CashAccountResource($this->cash_account),
            'amount' => $this->amount,
            'remarks' => $this->remarks,
        ];
    }
}
