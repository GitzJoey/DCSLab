<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Config;

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
            'hId' => $this->hId,
            'name' => $this->name,
            'email' => $this->email,
            'email_verified' => !is_null($this->email_verified_at),
            'profile' => new ProfileResource($this->profile),
            'selected_roles' => $this->relationLoaded('roles') ? $this->selectedRolesInArray($this->roles):'',
            'selected_settings' => $this->relationLoaded('settings') ? $this->selectedSettingsInArray($this->settings):'',
            'password_expiry_day' => $this->getPasswordExpiryDay($this->password_changed_at),
            'roles' => RoleResource::collection($this->whenLoaded('permissions')),
            'roles_description' => $this->relationLoaded('roles') ? $this->flattenRoles($this->roles):'',
            'companies' => CompanyResource::collection($this->whenLoaded('companies')),
            'settings' => SettingResource::collection($this->whenLoaded('settings'))
        ];
    }

    private function flattenRoles($roles)
    {
        return $roles->pluck('display_name')->implode(',');
    }

    private function selectedRolesInArray($roles)
    {
        return $roles->pluck('hId');
    }

    private function getPasswordExpiryDay($password_changed_at)
    {
        if (is_null($password_changed_at))
            return 0;

        return Carbon::now()->diffInDays(Carbon::parse($this->password_changed_at)->addDays(Config::get('const.DEFAULT.PASSWORD_EXPIRY_DAYS')));
    }

    private function selectedSettingsInArray($settings)
    {
        $settings = array();
        foreach ($this->settings as $s) {
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
            $settings[$skey] = $s->value;
        }
        return $settings;
    }
}
