<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class VatProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => Hashids::encode($this->id),
            'ulid' => $this->ulid,
            $this->mergeWhen($this->relationLoaded('company'), [
                'company' => new CompanyResource($this->whenLoaded('company')),
            ]),
            'code' => $this->code,
            'name' => $this->name,
            'vat_rate' => dec_trim($this->vat_rate),
            'vat_base_numerator' => $this->vat_base_numerator,
            'vat_base_denominator' => $this->vat_base_denominator,
            'remarks' => $this->remarks,
            'is_active' => $this->is_active,
        ];
    }
}
