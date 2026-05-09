<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class ChartOfAccountResource extends JsonResource
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
            'scope' => $this->scope?->value,
            'system_key' => $this->system_key?->value,
            'source_type' => $this->source_type,
            'source_id' => $this->source_id,
            'code' => $this->code,
            'name' => $this->name,
            'account_type' => $this->account_type,
            'normal_balance' => $this->normal_balance,
            'level' => $this->level,
            'is_group' => $this->is_group,
            'is_active' => $this->is_active,
            'remarks' => $this->remarks,
            $this->mergeWhen($this->relationLoaded('childrenRecursive'), [
                'children' => self::collection($this->whenLoaded('childrenRecursive')),
            ]),
            $this->mergeWhen($this->relationLoaded('children'), [
                'children' => self::collection($this->whenLoaded('children')),
            ]),
        ];
    }
}
