<?php

namespace App\Http\Resources;

use App\Helpers\TimezoneHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class ReceivableResource extends JsonResource
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
                'category' => new ReceivableCategoryResource($this->whenLoaded('category')),
            ]),
            $this->mergeWhen($this->relationLoaded('customer'), [
                'customer' => new CustomerResource($this->whenLoaded('customer')),
            ]),
            $this->mergeWhen($this->relationLoaded('cashAccount'), [
                'cash_account' => new CashAccountResource($this->whenLoaded('cashAccount')),
            ]),
            'direct_amount_received' => dec_trim($this->direct_amount_received),
            'opening_amount_due' => dec_trim($this->opening_amount_due),
            'amount_total' => dec_trim($this->amount_total),
            'amount_paid_by_cash_account' => dec_trim($this->amount_paid_by_cash_account),
            'amount_paid_by_stock_adjustment' => dec_trim($this->amount_paid_by_stock_adjustment),
            'amount_due' => dec_trim($this->amount_due),
            'due_days' => $this->due_days,
            'is_paid_off' => $this->is_paid_off,
            'remarks' => $this->remarks,
            $this->mergeWhen($this->relationLoaded('payments'), [
                'payments' => ReceivablePaymentResource::collection($this->whenLoaded('payments')),
            ]),
        ];
    }
}
