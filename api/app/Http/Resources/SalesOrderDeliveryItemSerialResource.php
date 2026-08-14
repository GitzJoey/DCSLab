<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class SalesOrderDeliveryItemSerialResource extends JsonResource
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
            $this->mergeWhen($this->relationLoaded('salesOrderDelivery'), [
                'sales_order_delivery' => new SalesOrderDeliveryResource($this->whenLoaded('salesOrderDelivery')),
            ]),
            $this->mergeWhen($this->relationLoaded('salesOrderDeliveryItem'), [
                'sales_order_delivery_item' => new SalesOrderDeliveryItemResource($this->whenLoaded('salesOrderDeliveryItem')),
            ]),
            'serial' => $this->serial,
        ];
    }
}
