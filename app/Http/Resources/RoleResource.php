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
                'permissions' => PermissionResource::collection($this->whenLoaded('permissions')),
                'permissions_description' => $this->flattenPermissions($this->whenLoaded('permissions') ? $this->permissions : null),
            ]),
        ];
    }

    private function flattenPermissions($permissions)
    {
        if (is_null($permissions)) return [];

        return $permissions->pluck('display_name')->implode(',');
    }
}
