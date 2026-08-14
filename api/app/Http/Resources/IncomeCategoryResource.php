<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class IncomeCategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => Hashids::encode($this->id),
            'ulid' => $this->ulid,
            $this->mergeWhen($this->relationLoaded('company'), [
                'company' => new CompanyResource($this->whenLoaded('company')),
            ]),
            $this->mergeWhen($this->relationLoaded('parent'), [
                'parent' => new self($this->whenLoaded('parent')),
            ]),
            'code' => $this->code,
            'display_code' => $this->display_code,
            'name' => $this->name,
            'sequence' => $this->sequence,
            $this->mergeWhen($this->relationLoaded('childrenRecursive'), [
                'children' => self::collection($this->whenLoaded('childrenRecursive')),
            ]),
            $this->mergeWhen($this->relationLoaded('children'), [
                'children' => self::collection($this->whenLoaded('children')),
            ]),
        ];
    }
}
