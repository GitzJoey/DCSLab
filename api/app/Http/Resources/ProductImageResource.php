<?php

namespace App\Http\Resources;

use App\Helpers\ImageHelper\ImageHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class ProductImageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => Hashids::encode($this->id),
            'product_id' => $this->whenNotNull($this->product_id, fn () => Hashids::encode($this->product_id)),
            'path' => $this->path,
            'url' => ImageHelper::getUrl($this->path),
            'hash' => $this->hash,
            'is_thumbnail' => $this->is_thumbnail,
        ];
    }
}
