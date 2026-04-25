<?php

namespace App\Http\Resources;

use App\Helpers\TimezoneHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class PrepaidIncomeResource extends JsonResource
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
                'category' => new IncomeCategoryResource($this->whenLoaded('category')),
            ]),
            'estimated_useful_life' => $this->estimated_useful_life,
            $this->mergeWhen($this->relationLoaded('paidImmediatelyCashAccount'), [
                'paid_immediately_cash_account' => new CashAccountResource($this->whenLoaded('paidImmediatelyCashAccount')),
            ]),
            'amount_paid_immediately' => dec_trim($this->amount_paid_immediately),
            'amount_receivable' => dec_trim($this->amount_receivable),
            'due_days' => $this->due_days,
            'amount_receivable_paid' => dec_trim($this->amount_receivable_paid),
            'amount_receivable_due' => dec_trim($this->amount_receivable_due),
            'is_amount_receivable_paid_off' => $this->is_amount_receivable_paid_off,
            'amount_total' => dec_trim($this->amount_total),
            'remarks' => $this->remarks,
            $this->mergeWhen($this->relationLoaded('payments'), [
                'payments' => PrepaidIncomePaymentResource::collection($this->whenLoaded('payments')),
            ]),
            $this->mergeWhen($this->relationLoaded('images'), [
                'prepaid_income_images' => PrepaidIncomeImageResource::collection($this->whenLoaded('images')),
            ]),
            $this->mergeWhen($this->relationLoaded('mainImage'), [
                'main_prepaid_income_image' => new PrepaidIncomeImageResource($this->whenLoaded('mainImage')),
            ]),
        ];
    }
}
