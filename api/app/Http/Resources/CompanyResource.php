<?php

namespace App\Http\Resources;

use App\Enums\RecordStatus;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyResource extends JsonResource
{
    protected string $type;

    public function type(string $value) {
        $this->type = $value;
        return $this;
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            $this->mergeWhen(env('APP_DEBUG', false), [
                'id' => $this->id,
            ]),
            'ulid' => $this->uuid,
            'code' => $this->code,
            'name' => $this->name,
            'address' => $this->address,
            'default' => $this->default,
            'status' => $this->setStatus($this->status, $this->deleted_at),
            $this->mergeWhen($this->relationLoaded('branches'), [
                'branches' => BranchResource::collection($this->whenLoaded('branches')),
            ]),
            $this->mergeWhen($this->relationLoaded('warehouses'), [
                'warehouses' => WarehouseResource::collection($this->whenLoaded('warehouses')),
            ]),
        ];
    }

    private function setStatus($status, $deleted_at)
    {
        if (! is_null($deleted_at)) {
            return RecordStatus::DELETED->name;
        } else {
            return $status->name;
        }
    }
}
