<?php

namespace App\Http\Resources;

use App\Enums\RecordStatusEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class CustomerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => Hashids::encode($this->id),
            'ulid' => $this->ulid,
            $this->mergeWhen($this->relationLoaded('company'), [
                'company' => new CompanyResource($this->whenLoaded('company')),
            ]),
            $this->mergeWhen($this->relationLoaded('user'), [
                'user' => new UserResource($this->whenLoaded('user')),
            ]),
            'code' => $this->code,
            'is_member' => $this->is_member,
            'name' => $this->name,
            $this->mergeWhen($this->relationLoaded('group'), [
                'group' => new CustomerGroupResource($this->whenLoaded('group')),
            ]),
            'zone' => $this->zone,
            'max_open_invoice' => $this->max_open_invoice,
            'max_outstanding_invoice' => $this->max_outstanding_invoice,
            'max_invoice_age' => $this->max_invoice_age,
            'payment_term_type' => $this->payment_term_type?->name,
            'payment_term' => $this->payment_term,
            'taxable_enterprise' => $this->taxable_enterprise,
            'tax_id' => $this->tax_id,
            'status' => $this->setStatus($this->status, $this->deleted_at),
            'remarks' => $this->remarks,
        ];
    }

    private function setStatus($status, $deleted_at)
    {
        if (! is_null($deleted_at)) {
            return RecordStatusEnum::DELETED->name;
        } else {
            return $status->name;
        }
    }
}
