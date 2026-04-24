<?php

namespace App\Http\Resources;

use App\Helpers\TimezoneHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class PurchaseReceiptResource extends JsonResource
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
            $this->mergeWhen($this->relationLoaded('supplier'), [
                'supplier' => new SupplierResource($this->whenLoaded('supplier')),
            ]),
            $this->mergeWhen($this->relationLoaded('purchase'), [
                'purchase' => new PurchaseResource($this->whenLoaded('purchase')),
            ]),
            'code' => $this->code,
            'date' => TimezoneHelper::convertFromUTCIfValid($this->date),
            'is_linked_to_purchase' => ! is_null($this->purchase_id),
            'is_from_direct_purchase' => $this->is_from_direct_purchase,
            $this->mergeWhen($this->relationLoaded('warehouse'), [
                'warehouse' => new WarehouseResource($this->whenLoaded('warehouse')),
            ]),
            'remarks' => $this->remarks,
            'is_posted' => $this->is_posted,

            $this->mergeWhen($this->relationLoaded('items'), [
                'items' => PurchaseReceiptItemResource::collection($this->whenLoaded('items')),
            ]),
        ];
    }
}
