<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class StockAdjustmentInProductSerialResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => Hashids::encode($this->id),
            $this->mergeWhen($this->relationLoaded('company'), [
                'company' => new CompanyResource($this->whenLoaded('company')),
            ]),
            $this->mergeWhen($this->relationLoaded('branch'), [
                'branch' => new BranchResource($this->whenLoaded('branch')),
            ]),
            $this->mergeWhen($this->relationLoaded('stockAdjustment'), [
                'stock_adjustment' => new StockAdjustmentResource($this->whenLoaded('stockAdjustment')),
            ]),
            $this->mergeWhen($this->relationLoaded('stockAdjustmentInProduct'), [
                'stock_adjustment_in_product' => new StockAdjustmentInProductResource($this->whenLoaded('stockAdjustmentInProduct')),
            ]),
            'serial' => $this->serial,
        ];
    }
}
