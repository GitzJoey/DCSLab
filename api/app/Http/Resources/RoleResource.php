<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
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
            'displayName' => $this->display_name,
            $this->mergeWhen($this->relationLoaded('permissions'), [
                'permissions' => PermissionResource::collection($this->whenLoaded('permissions')),
                'permissionsDescription' => $this->flattenPermissions($this->whenLoaded('permissions') ? $this->permissions : null),
            ]),
        ];
    }

    private function flattenPermissions($permissions)
    {
        if (is_null($permissions)) {
            return [];
        }

        return $permissions->pluck('display_name')->implode(',');
    }
}
