<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class StockAdjustmentOutItemSerialResource extends JsonResource
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
            $this->mergeWhen($this->relationLoaded('stockAdjustment'), [
                'stock_adjustment' => new StockAdjustmentResource($this->whenLoaded('stockAdjustment')),
            ]),
            $this->mergeWhen($this->relationLoaded('stockAdjustmentOutItem'), [
                'stock_adjustment_out_item' => new StockAdjustmentOutItemResource($this->whenLoaded('stockAdjustmentOutItem')),
            ]),
            'serial' => $this->serial,
        ];
    }
}
