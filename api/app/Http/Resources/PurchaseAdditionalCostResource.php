<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class PurchaseAdditionalCostResource extends JsonResource
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
            $this->mergeWhen($this->relationLoaded('purchase'), [
                'purchase' => new PurchaseResource($this->whenLoaded('purchase')),
            ]),
            'code' => $this->code,
            'date' => $this->date,
            'due_days' => $this->due_days,
            $this->mergeWhen($this->relationLoaded('category'), [
                'category' => new PurchaseAdditionalCostCategoryResource($this->whenLoaded('category')),
            ]),
            $this->mergeWhen($this->relationLoaded('paidImmediatelyCashAccount'), [
                'paid_immediately_cash_account' => new CashAccountResource($this->whenLoaded('paidImmediatelyCashAccount')),
            ]),
            'amount_paid_immediately' => dec_trim($this->amount_paid_immediately),
            'amount_payable' => dec_trim($this->amount_payable),
            'amount_payable_paid' => dec_trim($this->amount_payable_paid),
            'amount_payable_due' => dec_trim($this->amount_payable_due),
            'is_amount_payable_paid_off' => $this->is_amount_payable_paid_off,
            'amount_total' => dec_trim($this->amount_total),
            'remarks' => $this->remarks,
            $this->mergeWhen($this->relationLoaded('payments'), [
                'payments' => PurchaseAdditionalCostPaymentResource::collection($this->whenLoaded('payments')),
            ]),
        ];
    }
}
