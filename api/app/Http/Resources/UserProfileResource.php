<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class UserProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => Hashids::encode($this->id),
            'ulid' => $this->ulid,
            'name' => $this->name,
            'email' => $this->email,
            'email_verified' => ! is_null($this->email_verified_at),
            'password_expiry_day' => $this->getPasswordExpiryDay($this->password_changed_at),
            $this->mergeWhen($this->relationLoaded('profile'), [
                'profile' => (new ProfileResource($this->whenLoaded('profile'))),
            ]),
            $this->mergeWhen($this->relationLoaded('roles'), [
                'roles' => RoleResource::collection($this->whenLoaded('roles')),
            ]),
            $this->mergeWhen($this->relationLoaded('companies'), [
                'companies' => CompanyResource::collection($this->whenLoaded('companies')),
            ]),
            $this->mergeWhen($this->relationLoaded('settings'), [
                'settings' => (new SettingResource($this->whenLoaded('settings'))),
            ]),
            'two_factor' => $this->getTwoFactorStatus(),
        ];
    }

    private function getPasswordExpiryDay($password_changed_at)
    {
        if (is_null($password_changed_at)) {
            return 0;
        }

        $diff = Carbon::now()->diffInDays(Carbon::parse($this->password_changed_at)->addDays(config('dcslab.PASSWORD_EXPIRY_DAYS')), false);

        return $diff <= 0 ? 0 : $diff;
    }

    private function getTwoFactorStatus()
    {
        if (!auth()->user()) return false;
        return auth()->user()->two_factor_secret !== null;
    }
}
