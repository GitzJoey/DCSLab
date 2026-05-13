<?php

namespace App\Http\Resources;

use App\Helpers\TimezoneHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class JournalEntryResource extends JsonResource
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
            'source_type' => $this->source_type,
            'source_id' => $this->source_id,
            'reference_no' => $this->reference_no,
            'total_debit' => dec_trim($this->total_debit),
            'total_credit' => dec_trim($this->total_credit),
            'remarks' => $this->remarks,
            'items' => JournalEntryItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
