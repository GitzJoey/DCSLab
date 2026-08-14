<?php

namespace App\Http\Resources;

use App\Helpers\TimezoneHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class SalesInvoicePaymentResource extends JsonResource
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
            $this->mergeWhen($this->relationLoaded('salesInvoice'), [
                'sales_invoice' => new SalesInvoiceResource($this->whenLoaded('salesInvoice')),
            ]),
            'code' => $this->code,
            'date' => TimezoneHelper::convertFromUTCIfValid($this->date),
            'payment_type' => $this->payment_type?->value,
            $this->mergeWhen($this->relationLoaded('cashAccount'), [
                'cash_account' => new CashAccountResource($this->whenLoaded('cashAccount')),
            ]),
            $this->mergeWhen($this->relationLoaded('salesOrderPayment'), [
                'sales_order_payment' => new SalesOrderPaymentResource($this->whenLoaded('salesOrderPayment')),
            ]),
            $this->mergeWhen($this->relationLoaded('salesReturn'), [
                'sales_return' => new SalesReturnResource($this->whenLoaded('salesReturn')),
            ]),
            'amount' => dec_trim($this->amount),
            'remarks' => $this->remarks,
        ];
    }
}
