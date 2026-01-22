<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class NonCapitalWithdrawalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => Hashids::encode($this->id),
            'ulid' => $this->ulid,
            'company' => new CompanyResource($this->company),
            'branch' => new BranchResource($this->branch),
            'code' => $this->code,
            'date' => $this->date,
            'category_id' => new NonCapitalWithdrawalCategoryResource($this->category),
            'cash_account' => new CashAccountResource($this->cash_account),
            'amount' => $this->amount,
            'remarks' => $this->remarks,
        ];
    }
}
