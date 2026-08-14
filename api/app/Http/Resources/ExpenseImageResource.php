<?php

namespace App\Http\Resources;

use App\Helpers\ImageHelper\ImageHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class ExpenseImageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => Hashids::encode($this->id),
            'expense_id' => $this->whenNotNull($this->expense_id, fn () => Hashids::encode($this->expense_id)),
            'path' => $this->path,
            'url' => ImageHelper::getUrl($this->path),
            'hash' => $this->hash,
            'is_main' => $this->is_main,
        ];
    }
}
