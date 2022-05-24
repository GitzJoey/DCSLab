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
            $this->mergeWhen($this->whenLoaded('company'), [
                'company' => new CompanyResource($this->company)
            ]),
            $this->mergeWhen($this->whenLoaded('branch'), [
                'branch' => new BranchResource($this->branch)
            ]),
            'code' => $this->code,
            'name' => $this->name,
            'address' => $this->address,
            'city' => $this->city,
            'contact' => $this->contact,
            'remarks' => $this->remarks,
            'status' => $this->status->name
        ];
    }
}
