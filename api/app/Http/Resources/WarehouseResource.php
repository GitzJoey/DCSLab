<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class WarehouseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'hId' => $this->hId,
            'uuid' => $this->uuid,
            $this->mergeWhen($this->relationLoaded('company'), [
                'company' => new CompanyResource($this->whenLoaded('company')),
            ]),
            $this->mergeWhen($this->relationLoaded('branch'), [
                'branch' => new BranchResource($this->whenLoaded('branch')),
            ]),
            'code' => $this->code,
            'name' => $this->name,
            'address' => $this->address,
            'city' => $this->city,
            'contact' => $this->contact,
            'remarks' => $this->remarks,
            'status' => $this->status->name,
        ];
    }
}
