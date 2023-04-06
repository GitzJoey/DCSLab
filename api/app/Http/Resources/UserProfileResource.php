<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
                'profile' => (new ProfileResource($this->whenLoaded('profile')))->type('UserProfile'),
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
                'selected_settings' => $this->selectedSettingsInArray($this->whenLoaded('settings') ? $this->settings : null),
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

    private function selectedRolesInArray($roles)
    {
        if (is_null($roles)) {
            return [];
        }

        return $roles->pluck('display_name');
    }

    private function getPasswordExpiryDay($password_changed_at)
    {
        if (is_null($password_changed_at)) {
            return 0;
        }

        $diff = Carbon::now()->diffInDays(Carbon::parse($this->password_changed_at)->addDays(config('dcslab.PASSWORD_EXPIRY_DAYS')), false);

        return $diff <= 0 ? 0 : $diff;
    }

    private function selectedSettingsInArray($settings)
    {
        if (is_null($settings)) {
            return [];
        }

        $result = [];
        foreach ($settings as $s) {
            $skey = '';
            switch ($s->key) {
                case 'PREFS.THEME':
                    $skey = 'theme';
                    break;
                case 'PREFS.DATE_FORMAT':
                    $skey = 'dateFormat';
                    break;
                case 'PREFS.TIME_FORMAT':
                    $skey = 'timeFormat';
                    break;
                default:
                    break;
            }
            $result[$skey] = $s->value;
        }

        return $result;
    }
}
