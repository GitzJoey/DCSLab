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
            $this->mergeWhen($this->relationLoaded('journalEntry') && $this->journalEntry, [
                'journal_entry' => new JournalEntryResource($this->whenLoaded('journalEntry')),
            ]),
            $this->mergeWhen($this->relationLoaded('chartOfAccount') && $this->chartOfAccount, [
                'chart_of_account' => new ChartOfAccountResource($this->whenLoaded('chartOfAccount')),
            ]),
            'sequence' => $this->sequence,
            'debit' => dec_trim($this->debit),
            'credit' => dec_trim($this->credit),
            'remarks' => $this->remarks,
        ];
    }
}
