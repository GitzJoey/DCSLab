<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'name' => $this->name,
            'email' => $this->email,
            'email_verified' => ! is_null($this->email_verified_at),
            'password_expiry_day' => $this->getPasswordExpiryDay($this->password_changed_at),
            $this->mergeWhen($this->relationLoaded('profile'), [
                'profile' => new ProfileResource($this->whenLoaded('profile')),
            ]),
            $this->mergeWhen($this->relationLoaded('roles'), [
                'roles' => RoleResource::collection($this->whenLoaded('roles')),
                'roles_description' => $this->flattenRoles($this->whenLoaded('roles') ? $this->roles : null),
                'selected_roles' => $this->selectedRolesInArray($this->whenLoaded('roles') ? $this->roles : null),
            ]),
            $this->mergeWhen($this->relationLoaded('companies'), [
                'companies' => CompanyResource::collection($this->whenLoaded('companies')),
            ]),
            $this->mergeWhen($this->relationLoaded('settings'), [
                'settings' => SettingResource::collection($this->whenLoaded('settings')),
                'selected_settings' => $this->selectedSettingsInArray($this->whenLoaded('settings') ? $this->settings : null),
            ]),
        ];
    }
}
