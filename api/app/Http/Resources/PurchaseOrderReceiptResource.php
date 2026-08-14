<?php

namespace App\Http\Resources;

use App\Helpers\TimezoneHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class PurchaseOrderReceiptResource extends JsonResource
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
            'code' => $this->code,
            'date' => TimezoneHelper::convertFromUTCIfValid($this->date),
            $this->mergeWhen($this->relationLoaded('supplier'), [
                'supplier' => new SupplierResource($this->whenLoaded('supplier')),
            ]),
            $this->mergeWhen($this->relationLoaded('purchaseOrder'), [
                'purchase_order' => new PurchaseOrderResource($this->whenLoaded('purchaseOrder')),
            ]),
            $this->mergeWhen($this->relationLoaded('warehouse'), [
                'warehouse' => new WarehouseResource($this->whenLoaded('warehouse')),
            ]),
            'remarks' => $this->remarks,
            'is_posted' => $this->is_posted,
            'total_value' => dec_trim($this->total_value),
            'total_cost' => dec_trim($this->total_cost),
            $this->mergeWhen($this->relationLoaded('items'), [
                'items' => PurchaseOrderReceiptItemResource::collection($this->whenLoaded('items')),
            ]),
            $this->mergeWhen($this->relationLoaded('costs'), [
                'costs' => PurchaseOrderReceiptCostResource::collection($this->whenLoaded('costs')),
            ]),
        ];
    }
}
