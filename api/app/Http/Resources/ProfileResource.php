<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'address' => $this->address,
            'city' => $this->city,
            'postal_code' => $this->postal_code,
            'country' => $this->country,
            'status' => $this->status->name,
            'tax_id' => $this->tax_id,
            'ic_num' => $this->ic_num,
            'img_path' => $this->img_path,
            'remarks' => $this->remarks,
        ];
    }
}
