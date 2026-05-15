<?php

namespace App\Http\Resources;

use App\Helpers\TimezoneHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class AssetAdjustmentResource extends JsonResource
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
            'remarks' => $this->remarks,
            'is_posted' => $this->is_posted,
            'total_incoming_asset_qty' => dec_trim($this->total_incoming_asset_qty),
            'total_outgoing_asset_qty' => dec_trim($this->total_outgoing_asset_qty),
            'in_items' => AssetAdjustmentInItemResource::collection($this->whenLoaded('inItems')),
            'out_items' => AssetAdjustmentOutItemResource::collection($this->whenLoaded('outItems')),
        ];
    }
}
