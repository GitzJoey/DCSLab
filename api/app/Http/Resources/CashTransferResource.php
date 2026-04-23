<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class CashTransferResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => Hashids::encode($this->id),
            'ulid' => $this->ulid,
            $this->mergeWhen($this->relationLoaded('company'), [
                'company' => new CompanyResource($this->whenLoaded('company')),
            ]),
            $this->mergeWhen($this->relationLoaded('branch'), [
                'branch' => new BranchResource($this->whenLoaded('branch')),
            ]),
            'code' => $this->code,
            'date' => $this->date,
            $this->mergeWhen($this->relationLoaded('sourceCashAccount'), [
                'source_cash_account' => new CashAccountResource($this->whenLoaded('sourceCashAccount')),
            ]),
            $this->mergeWhen($this->relationLoaded('destinationCashAccount'), [
                'destination_cash_account' => new CashAccountResource($this->whenLoaded('destinationCashAccount')),
            ]),
            'amount' => $this->amount,
            'remarks' => $this->remarks,
        ];
    }
}
