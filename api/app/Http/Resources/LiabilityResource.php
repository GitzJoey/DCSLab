<?php

namespace App\Http\Resources;

use App\Helpers\TimezoneHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class LiabilityResource extends JsonResource
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
            'date' => TimezoneHelper::convertFromUTCIfValid($this->date),
            $this->mergeWhen($this->relationLoaded('category'), [
                'category' => new LiabilityCategoryResource($this->whenLoaded('category')),
            ]),
            $this->mergeWhen($this->relationLoaded('creditor'), [
                'creditor' => new LiabilityCreditorResource($this->whenLoaded('creditor')),
            ]),
            $this->mergeWhen($this->relationLoaded('supplier'), [
                'supplier' => new SupplierResource($this->whenLoaded('supplier')),
            ]),
            $this->mergeWhen($this->relationLoaded('cashAccount'), [
                'cash_account' => new CashAccountResource($this->whenLoaded('cashAccount')),
            ]),
            'amount_received' => $this->amount_received,
            'amount_payable' => $this->amount_payable,
            'amount_total' => $this->amount_total,
            'amount_paid_by_cash_account' => $this->amount_paid_by_cash_account,
            'amount_paid_by_stock_adjustment' => $this->amount_paid_by_stock_adjustment,
            'amount_due' => $this->amount_due,
            'due_days' => $this->due_days,
            'is_paid_off' => $this->is_paid_off,
            'remarks' => $this->remarks,
            $this->mergeWhen($this->relationLoaded('payments'), [
                'payments' => LiabilityPaymentResource::collection($this->whenLoaded('payments')),
            ]),
        ];
    }
}
