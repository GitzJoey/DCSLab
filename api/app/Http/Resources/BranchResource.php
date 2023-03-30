<?php

namespace App\Http\Resources;

use App\Enums\RecordStatus;
use Illuminate\Http\Resources\Json\JsonResource;

class BranchResource extends JsonResource
{
    protected string $type;

    public function type(string $value)
    {
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
            'ulid' => $this->ulid,
            $this->mergeWhen($this->relationLoaded('company'), [
                'company' => new CompanyResource($this->whenLoaded('company')),
            ]),
            'code' => $this->code,
            'name' => $this->name,
            'address' => $this->address,
            'city' => $this->city,
            'contact' => $this->contact,
            'status' => $this->setStatus($this->status, $this->deleted_at),
            'is_main' => $this->is_main,
            'remarks' => $this->remarks,
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
