<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class CustomerAddressResource extends JsonResource
{
    public function toArray(Request $request): array
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
