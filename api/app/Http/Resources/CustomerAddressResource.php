<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class CustomerAddressResource extends JsonResource
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
            'company' => new CompanyResource($this->company),
            'customer' => new CustomerResource($this->customer),
            'address' => $this->address,
            'city' => $this->city,
            'contact' => $this->contact,
            'is_main' => $this->is_main,
            'remarks' => $this->remarks,
        ];
    }
}
