<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class CustomerGroupResource extends JsonResource
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
            'max_open_invoice' => (int) $this->max_open_invoice,
            'max_outstanding_invoice' => (float) (string) $this->max_outstanding_invoice,
            'max_invoice_age' => (int) $this->max_invoice_age,
            'payment_term_type' => $this->payment_term_type->name,
            'payment_term' => (int) $this->payment_term,
            'selling_point' => (int) $this->selling_point,
            'selling_point_multiple' => (float) (string) $this->selling_point_multiple,
            'sell_at_cost' => $this->sell_at_cost,
            'price_markup_percent' => (float) (string) $this->price_markup_percent,
            'price_markup_nominal' => (float) (string) $this->price_markup_nominal,
            'price_markdown_percent' => (float) (string) $this->price_markdown_percent,
            'price_markdown_nominal' => (float) (string) $this->price_markdown_nominal,
            'rounding_type' => $this->rounding_type->name,
            'rounding_digit' => (int) $this->rounding_digit,
            'remarks' => $this->remarks,
        ];
    }
}
