<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class BrandResource extends JsonResource
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
        ];
    }
}
