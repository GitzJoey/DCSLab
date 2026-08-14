<?php

namespace App\Http\Resources;

use App\Enums\ChartOfAccountSystemKeyEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class ExpenseCategoryResource extends JsonResource
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
            'category_type' => (function () {
                if (! is_null($this->parent_id)) return null;

                return match ($this->chartOfAccount?->parent?->system_key) {
                    ChartOfAccountSystemKeyEnum::OTHER_EXPENSE_ROOT->value => 'other_expense',
                    ChartOfAccountSystemKeyEnum::EXPENSE_ROOT->value => 'expense',
                    default => null,
                };
            })(),
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
