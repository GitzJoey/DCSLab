<?php

namespace App\Http\Resources;

use App\Helpers\TimezoneHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class PurchaseOrderResource extends JsonResource
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
            'remarks' => $this->remarks,
            'total_before_global_discount' => $this->total_before_global_discount,
            'global_discount' => $this->global_discount,
            'total_before_vat' => $this->total_before_vat,
            'vat_base' => $this->vat_base,
            'vat' => $this->vat,
            'rounding' => $this->rounding,
            'grand_total' => $this->grand_total,
            'amount_paid_down_payment' => $this->amount_paid_down_payment,
            'amount_allocated_down_payment' => $this->amount_allocated_down_payment,
            'amount_available_down_payment' => $this->amount_available_down_payment,
            'global_discounts' => PurchaseOrderGlobalDiscountResource::collection($this->whenLoaded('globalDiscounts')),
            'items' => PurchaseOrderItemResource::collection($this->whenLoaded('items')),
            'down_payments' => PurchaseOrderDownPaymentResource::collection($this->whenLoaded('downPayments')),
        ];
    }
}
