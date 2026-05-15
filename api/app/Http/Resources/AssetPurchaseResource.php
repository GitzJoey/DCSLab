<?php

namespace App\Http\Resources;

use App\Helpers\TimezoneHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class AssetPurchaseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => Hashids::encode($this->id),
            'ulid' => $this->ulid,
            'company' => new CompanyResource($this->whenLoaded('company')),
            'branch' => new BranchResource($this->whenLoaded('branch')),
            'supplier' => new SupplierResource($this->whenLoaded('supplier')),
            'code' => $this->code,
            'date' => TimezoneHelper::convertFromUTCIfValid($this->date),
            'due_days' => $this->due_days,
            'remarks' => $this->remarks,
            'is_posted' => $this->is_posted,
            'item_total' => dec_trim($this->item_total),
            'additional_cost' => dec_trim($this->additional_cost),
            'rounding' => dec_trim($this->rounding),
            'amount_payable' => dec_trim($this->amount_payable),
            'items' => AssetPurchaseItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
