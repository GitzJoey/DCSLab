<?php

namespace App\Http\Resources;

use App\Helpers\TimezoneHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class SalesReturnRefundResource extends JsonResource
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
            $this->mergeWhen($this->relationLoaded('salesReturn'), [
                'sales_return' => new SalesReturnResource($this->whenLoaded('salesReturn')),
            ]),
            $this->mergeWhen($this->relationLoaded('cashAccount'), [
                'cash_account' => new CashAccountResource($this->whenLoaded('cashAccount')),
            ]),
            'amount' => dec_trim($this->amount),
            'remarks' => $this->remarks,
        ];
    }
}
