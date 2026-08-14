<?php

namespace App\Http\Resources;

use App\Helpers\TimezoneHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class StockAdjustmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => Hashids::encode($this->id),
            'ulid' => $this->ulid,
            'company' => new CompanyResource($this->whenLoaded('company')),
            'branch' => new BranchResource($this->whenLoaded('branch')),
            'code' => $this->code,
            'date' => TimezoneHelper::convertFromUTCIfValid($this->date),
            'category' => new StockAdjustmentCategoryResource($this->whenLoaded('category')),
            'in_warehouse' => new WarehouseResource($this->whenLoaded('inWarehouse')),
            'out_warehouse' => new WarehouseResource($this->whenLoaded('outWarehouse')),
            'remarks' => $this->remarks,
            'is_posted' => $this->is_posted,
            'total_incoming_item_qty' => dec_trim($this->total_incoming_item_qty),
            'total_incoming_item_cogs' => dec_trim($this->total_incoming_item_cogs),
            'total_outgoing_item_qty' => dec_trim($this->total_outgoing_item_qty),

            'in_items' => StockAdjustmentInItemResource::collection($this->whenLoaded('inItems')),
            'out_items' => StockAdjustmentOutItemResource::collection($this->whenLoaded('outItems')),
        ];
    }
}
