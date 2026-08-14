<?php

namespace App\Http\Resources;

use App\Helpers\TimezoneHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class SalesOrderDeliveryResource extends JsonResource
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
            $this->mergeWhen($this->relationLoaded('customer'), [
                'customer' => new CustomerResource($this->whenLoaded('customer')),
            ]),
            $this->mergeWhen($this->relationLoaded('salesOrder'), [
                'sales_order' => new SalesOrderResource($this->whenLoaded('salesOrder')),
            ]),
            'code' => $this->code,
            'date' => TimezoneHelper::convertFromUTCIfValid($this->date),
            $this->mergeWhen($this->relationLoaded('warehouse'), [
                'warehouse' => new WarehouseResource($this->whenLoaded('warehouse')),
            ]),
            'remarks' => $this->remarks,
            'is_posted' => $this->is_posted,
            'total_cogs' => dec_trim($this->total_cogs),
            'total_cost' => dec_trim($this->total_cost),

            $this->mergeWhen($this->relationLoaded('items'), [
                'items' => SalesOrderDeliveryItemResource::collection($this->whenLoaded('items')),
            ]),
            $this->mergeWhen($this->relationLoaded('costs'), [
                'costs' => SalesOrderDeliveryCostResource::collection($this->whenLoaded('costs')),
            ]),
        ];
    }
}
