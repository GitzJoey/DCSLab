<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class SalesOrderDeliveryItemResource extends JsonResource
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
            $this->mergeWhen($this->relationLoaded('salesOrderItem'), [
                'sales_order_item' => new SalesOrderItemResource($this->whenLoaded('salesOrderItem')),
            ]),
            'has_sales_order_item_product' => (bool) $this->has_sales_order_item_product,
            'qty' => dec_trim($this->qty),
            $this->mergeWhen($this->relationLoaded('productUnit'), [
                'product_unit' => new ProductUnitResource($this->whenLoaded('productUnit')),
            ]),
            'product_unit_conversion_value' => dec_trim($this->product_unit_conversion_value),
            'product_unit_qty_base' => dec_trim($this->product_unit_qty_base),
            'base_unit_cogs' => dec_trim($this->base_unit_cogs),
            'total_cogs' => dec_trim($this->total_cogs),
            'remarks' => $this->remarks,

            $this->mergeWhen($this->relationLoaded('serials'), [
                'serials' => SalesOrderDeliveryItemSerialResource::collection($this->whenLoaded('serials')),
            ]),
        ];
    }
}
