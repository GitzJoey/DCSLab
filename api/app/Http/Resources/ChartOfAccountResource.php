<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ChartOfAccountResource extends JsonResource
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
            'company' => new CompanyResource($this->company),
            'parent_account' => new ChartOfAccountResource($this->whenLoaded('parentAccount')),
            'code' => $this->code,
            'name' => $this->name,
            'account_type' => $this->account_type->name,
            'remarks' => $this->remarks,
        ];
    }
}
