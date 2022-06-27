<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CompanyResource extends JsonResource
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
            'code' => $this->code,
            'name' => $this->name,
            'address' => $this->address,
            'default' => $this->default,
            'status' => $this->status->name,
            $this->mergeWhen($this->relationLoaded('branches'), [
                'branches' => BranchResource::collection($this->whenLoaded('branches'))
            ]),
            $this->mergeWhen($this->relationLoaded('warehouses'), [
                'warehouses' => WarehouseResource::collection($this->whenLoaded('warehouses'))
            ])
        ];
    }
}
