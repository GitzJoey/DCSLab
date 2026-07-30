<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class SalesReturnItemSerialResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => Hashids::encode($this->id),
            'ulid' => $this->ulid,

            $this->mergeWhen($this->relationLoaded('company'), [
                'company' => new CompanyResource($this->whenLoaded('company')),
            ]),
            $this->mergeWhen($this->relationLoaded('branch'), [
                'branch' => new BranchResource($this->whenLoaded('branch')),
            ]),
            $this->mergeWhen($this->relationLoaded('salesReturn'), [
                'sales_return' => new SalesReturnResource($this->whenLoaded('salesReturn')),
            ]),
            $this->mergeWhen($this->relationLoaded('salesReturnItem'), [
                'sales_return_item' => new SalesReturnItemResource($this->whenLoaded('salesReturnItem')),
            ]),
            'serial' => $this->serial,
        ];
    }
}
