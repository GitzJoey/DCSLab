<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class RoleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => Hashids::encode($this->id),
            'display_name' => $this->display_name,
            $this->mergeWhen($this->relationLoaded('permissions'), [
                'permissions' => PermissionResource::collection($this->whenLoaded('permissions')),
            ]),
        ];
    }
}
