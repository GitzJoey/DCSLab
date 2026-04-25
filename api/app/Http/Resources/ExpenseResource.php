<?php

namespace App\Http\Resources;

use App\Helpers\TimezoneHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class ExpenseResource extends JsonResource
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
                'category' => new ExpenseCategoryResource($this->whenLoaded('category')),
            ]),
            $this->mergeWhen($this->relationLoaded('paidImmediatelyCashAccount'), [
                'paid_immediately_cash_account' => new CashAccountResource($this->whenLoaded('paidImmediatelyCashAccount')),
            ]),
            'amount_paid_immediately' => dec_trim($this->amount_paid_immediately),
            'amount_payable' => dec_trim($this->amount_payable),
            'due_days' => $this->due_days,
            'amount_payable_paid' => dec_trim($this->amount_payable_paid),
            'amount_payable_due' => dec_trim($this->amount_payable_due),
            'is_amount_payable_paid_off' => $this->is_amount_payable_paid_off,
            'amount_total' => dec_trim($this->amount_total),
            'remarks' => $this->remarks,
            $this->mergeWhen($this->relationLoaded('payments'), [
                'payments' => ExpensePaymentResource::collection($this->whenLoaded('payments')),
            ]),
            $this->mergeWhen($this->relationLoaded('images'), [
                'expense_images' => ExpenseImageResource::collection($this->whenLoaded('images')),
            ]),
            $this->mergeWhen($this->relationLoaded('mainImage'), [
                'main_expense_image' => new ExpenseImageResource($this->whenLoaded('mainImage')),
            ]),
        ];
    }
}
