<?php

namespace App\Http\Resources;

use App\Helpers\TimezoneHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class SalesReturnResource extends JsonResource
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
            $this->mergeWhen($this->relationLoaded('customer'), [
                'customer' => new CustomerResource($this->whenLoaded('customer')),
            ]),
            $this->mergeWhen($this->relationLoaded('salesInvoice'), [
                'sales_invoice' => new SalesInvoiceResource($this->whenLoaded('salesInvoice')),
            ]),
            $this->mergeWhen($this->relationLoaded('warehouse'), [
                'warehouse' => new WarehouseResource($this->whenLoaded('warehouse')),
            ]),
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
            'amount_allocated_to_invoice' => dec_trim($this->amount_allocated_to_invoice),
            'amount_received_total' => dec_trim($this->amount_received_total),
            'amount_settled_total' => dec_trim($this->amount_settled_total),
            'amount_available' => dec_trim($this->amount_available),
            'is_settled' => $this->is_settled,

            $this->mergeWhen($this->relationLoaded('items'), [
                'items' => SalesReturnItemResource::collection($this->whenLoaded('items')),
            ]),
            $this->mergeWhen($this->relationLoaded('refunds'), [
                'refunds' => SalesReturnRefundResource::collection($this->whenLoaded('refunds')),
            ]),
        ];
    }
}
