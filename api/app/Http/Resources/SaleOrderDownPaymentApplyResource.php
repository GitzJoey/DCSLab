<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class SaleOrderDownPaymentApplyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => Hashids::encode($this->id),
            'ulid' => $this->ulid,
            'company' => new CompanyResource($this->company),
            'branch' => new BranchResource($this->branch),
            'sale_order' => new SalesOrderResource($this->sale_order),
            'code' => $this->code,
            'date' => $this->date,
            'cash_account' => new CashAccountResource($this->cash_account),
            'amount' => $this->amount,
            'remarks' => $this->remarks,
        ];
    }
}
