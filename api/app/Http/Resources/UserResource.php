<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class UserResource extends JsonResource
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
                'role_descriptions' => $this->flattenRoles($this->roles ? $this->roles : null),
            ]),
            $this->mergeWhen($this->relationLoaded('companies'), [
                'companies' => CompanyResource::collection($this->whenLoaded('companies')),
            ]),
            $this->mergeWhen($this->relationLoaded('settings'), [
                'settings' => (new SettingResource($this->whenLoaded('settings'))),
            ]),
        ];
    }

    private function flattenRoles($roles)
    {
        if (is_null($roles)) {
            return '';
        }

        return $roles->pluck('display_name')->implode(',');
    }

    private function getPasswordExpiryDay($password_changed_at)
    {
        if (is_null($password_changed_at)) {
            return 0;
        }

        $diff = Carbon::now()->diffInDays(Carbon::parse($this->password_changed_at)->addDays(config('dcslab.PASSWORD_EXPIRY_DAYS')), false);

        return $diff <= 0 ? 0 : $diff;
    }
}
