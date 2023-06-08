<?php

namespace App\Http\Resources;

use App\Enums\RecordStatus;
use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class CustomerResource extends JsonResource
{
    protected string $type = '';

    public function type(string $value)
    {
        $this->type = $value;

        return $this;
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => Hashids::encode($this->id),
            'ulid' => $this->ulid,
            $this->mergeWhen($this->relationLoaded('company'), [
                'company' => new CompanyResource($this->company),
            ]),
            'code' => $this->code,
            'name' => $this->name,
            'is_member' => $this->is_member,
            $this->mergeWhen($this->relationLoaded('customerGroup'), [
                'customer_group' => new CustomerGroupResource($this->customerGroup),
            ]),
            'zone' => $this->zone,
            $this->mergeWhen($this->relationLoaded('customerAddresses'), [
                'customer_addresses' => CustomerAddressResource::collection($this->customerAddresses),
            ]),
            'max_open_invoice' => $this->max_open_invoice,
            'max_outstanding_invoice' => $this->max_outstanding_invoice,
            'max_invoice_age' => $this->max_invoice_age,
            'payment_term_type' => $this->payment_term_type->name,
            'payment_term' => $this->payment_term,
            'tax_id' => $this->tax_id,
            'taxable_enterprise' => $this->taxable_enterprise,
            'remarks' => $this->remarks,
            'status' => $this->setStatus($this->status, $this->deleted_at),
            $this->mergeWhen($this->relationLoaded('user'), [
                'customer_pic' => new UserResource($this->user),
            ]),
        ];
    }

    private function setStatus($status, $deleted_at)
    {
        if (! is_null($deleted_at)) {
            return RecordStatus::DELETED->name;
        } else {
            return $status->name;
        }
    }
}
