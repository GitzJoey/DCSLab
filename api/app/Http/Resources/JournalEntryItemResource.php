<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class JournalEntryItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => Hashids::encode($this->id),
            'journal_entry_id' => Hashids::encode($this->journal_entry_id),
            'chart_of_account_id' => Hashids::encode($this->chart_of_account_id),
            'sequence' => $this->sequence,
            'debit' => dec_trim($this->debit),
            'credit' => dec_trim($this->credit),
            'remarks' => $this->remarks,
            $this->mergeWhen($this->relationLoaded('chartOfAccount') && $this->chartOfAccount, [
                'chart_of_account' => new ChartOfAccountResource($this->whenLoaded('chartOfAccount')),
            ]),
        ];
    }
}
