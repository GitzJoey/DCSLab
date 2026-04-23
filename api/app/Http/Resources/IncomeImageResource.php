<?php

namespace App\Http\Resources;

use App\Helpers\ImageHelper\ImageHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class IncomeImageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => Hashids::encode($this->id),
            'income_id' => $this->whenNotNull($this->income_id, fn () => Hashids::encode($this->income_id)),
            'path' => $this->path,
            'url' => ImageHelper::getUrl($this->path),
            'hash' => $this->hash,
            'is_main' => $this->is_main,
        ];
    }
}
