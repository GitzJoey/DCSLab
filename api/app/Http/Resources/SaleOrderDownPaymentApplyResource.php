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
            $this->mergeWhen($this->relationLoaded('company'), [
                'company' => new CompanyResource($this->whenLoaded('company')),
            ]),
            $this->mergeWhen($this->relationLoaded('branch'), [
                'branch' => new BranchResource($this->whenLoaded('branch')),
            ]),
            $this->mergeWhen($this->relationLoaded('saleOrder'), [
                'sale_order' => new SalesOrderResource($this->whenLoaded('saleOrder')),
            ]),
            'code' => $this->code,
            'date' => $this->date,
            $this->mergeWhen($this->relationLoaded('cashAccount'), [
                'cash_account' => new CashAccountResource($this->whenLoaded('cashAccount')),
            ]),
            'amount' => $this->amount,
            'remarks' => $this->remarks,
        ];
    }
}
