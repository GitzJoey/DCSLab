<?php

namespace App\Http\Resources;

use App\Helpers\TimezoneHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class PurchaseInvoiceResource extends JsonResource
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
            'due_days' => $this->due_days,
            $this->mergeWhen($this->relationLoaded('supplier'), [
                'supplier' => new SupplierResource($this->whenLoaded('supplier')),
            ]),
            $this->mergeWhen($this->relationLoaded('purchaseOrder'), [
                'purchase_order' => new PurchaseOrderResource($this->whenLoaded('purchaseOrder')),
            ]),
            'tax_invoice_number' => $this->tax_invoice_number,
            'tax_invoice_vat_base' => dec_trim($this->tax_invoice_vat_base),
            'tax_invoice_vat' => dec_trim($this->tax_invoice_vat),
            'remarks' => $this->remarks,
            'is_posted' => $this->is_posted,
            'item_total_before_global_discount' => dec_trim($this->item_total_before_global_discount),
            'global_discount' => dec_trim($this->global_discount),
            'item_total_after_global_discount' => dec_trim($this->item_total_after_global_discount),
            'vat_base' => dec_trim($this->vat_base),
            'vat' => dec_trim($this->vat),
            'item_total_after_vat' => dec_trim($this->item_total_after_vat),
            'rounding' => dec_trim($this->rounding),
            'amount_payable' => dec_trim($this->amount_payable),
            'amount_paid_down_payment' => dec_trim($this->amount_paid_down_payment),
            'amount_paid_return' => dec_trim($this->amount_paid_return),
            'amount_paid_total' => dec_trim($this->amount_paid_total),
            'amount_due' => dec_trim($this->amount_due),
            'is_paid_off' => $this->is_paid_off,
            $this->mergeWhen($this->relationLoaded('items'), [
                'items' => PurchaseInvoiceItemResource::collection($this->whenLoaded('items')),
            ]),
            $this->mergeWhen($this->relationLoaded('payments'), [
                'payments' => PurchaseInvoicePaymentResource::collection($this->whenLoaded('payments')),
            ]),
        ];
    }
}
