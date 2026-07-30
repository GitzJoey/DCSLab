<?php

namespace App\Http\Resources;

use App\Helpers\TimezoneHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class PurchaseInvoicePaymentResource extends JsonResource
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
            $this->mergeWhen($this->relationLoaded('purchaseInvoice'), [
                'purchase_invoice' => new PurchaseInvoiceResource($this->whenLoaded('purchaseInvoice')),
            ]),
            'code' => $this->code,
            'date' => TimezoneHelper::convertFromUTCIfValid($this->date),
            'payment_type' => $this->payment_type?->value,
            $this->mergeWhen($this->relationLoaded('cashAccount'), [
                'cash_account' => new CashAccountResource($this->whenLoaded('cashAccount')),
            ]),
            $this->mergeWhen($this->relationLoaded('purchaseOrderPayment'), [
                'purchase_order_payment' => new PurchaseOrderPaymentResource($this->whenLoaded('purchaseOrderPayment')),
            ]),
            $this->mergeWhen($this->relationLoaded('purchaseReturn'), [
                'purchase_return' => new PurchaseReturnResource($this->whenLoaded('purchaseReturn')),
            ]),
            'amount' => dec_trim($this->amount),
            'remarks' => $this->remarks,
        ];
    }
}
