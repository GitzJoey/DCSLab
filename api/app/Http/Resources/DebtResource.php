<?php

namespace App\Http\Resources;

use App\Helpers\TimezoneHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class DebtResource extends JsonResource
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
                'category' => new DebtCategoryResource($this->whenLoaded('category')),
            ]),
            $this->mergeWhen($this->relationLoaded('creditor'), [
                'creditor' => new DebtCreditorResource($this->whenLoaded('creditor')),
            ]),
            $this->mergeWhen($this->relationLoaded('supplier'), [
                'supplier' => new SupplierResource($this->whenLoaded('supplier')),
            ]),
            $this->mergeWhen($this->relationLoaded('cashAccount'), [
                'cash_account' => new CashAccountResource($this->whenLoaded('cashAccount')),
            ]),
            'direct_amount_received' => $this->direct_amount_received,
            'opening_amount_due' => $this->opening_amount_due,
            'amount_total' => $this->amount_total,
            'amount_paid_by_cash_account' => $this->amount_paid_by_cash_account,
            'amount_paid_by_stock_adjustment' => $this->amount_paid_by_stock_adjustment,
            'amount_due' => $this->amount_due,
            'due_days' => $this->due_days,
            'is_paid_off' => $this->is_paid_off,
            'remarks' => $this->remarks,
            $this->mergeWhen($this->relationLoaded('payments'), [
                'payments' => DebtPaymentResource::collection($this->whenLoaded('payments')),
            ]),
        ];
    }
}
