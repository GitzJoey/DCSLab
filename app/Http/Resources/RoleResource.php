<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

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
            'hId' => $this->hId,
            'display_name' => $this->display_name,
            $this->mergeWhen($this->relationLoaded('permissions'), [
                'permissions' => PermissionResource::collection($this->permissions)
            ])
        ];
    }
}
