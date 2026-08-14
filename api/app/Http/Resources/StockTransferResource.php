<?php

namespace App\Http\Resources;

use App\Helpers\TimezoneHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class StockTransferResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => Hashids::encode($this->id),
            'ulid' => $this->ulid,
            'company' => new CompanyResource($this->whenLoaded('company')),
            'branch' => new BranchResource($this->whenLoaded('branch')),
            'code' => $this->code,
            'date' => TimezoneHelper::convertFromUTCIfValid($this->date),
            'source_warehouse' => new WarehouseResource($this->whenLoaded('sourceWarehouse')),
            'destination_warehouse' => new WarehouseResource($this->whenLoaded('destinationWarehouse')),
            'remarks' => $this->remarks,
            'is_posted' => $this->is_posted,
            'items' => StockTransferItemResource::collection($this->whenLoaded('stockTransferItems')),
        ];
    }
}
