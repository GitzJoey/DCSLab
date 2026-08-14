<?php

namespace App\Http\Resources;

use App\Helpers\TimezoneHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class AssetSaleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => Hashids::encode($this->id),
            'ulid' => $this->ulid,
            'company' => new CompanyResource($this->whenLoaded('company')),
            'branch' => new BranchResource($this->whenLoaded('branch')),
            'customer' => new CustomerResource($this->whenLoaded('customer')),
            'code' => $this->code,
            'date' => TimezoneHelper::convertFromUTCIfValid($this->date),
            'due_days' => $this->due_days,
            'remarks' => $this->remarks,
            'is_posted' => $this->is_posted,
            'item_total' => dec_trim($this->item_total),
            'rounding' => dec_trim($this->rounding),
            'amount_receivable' => dec_trim($this->amount_receivable),
            'items' => AssetSaleItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
