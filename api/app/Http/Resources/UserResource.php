<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    protected static $type;

	public static function make(...$parameters)
	{
		$resource = $parameters['resource'] ?? $parameters[0];
		self::$type = $parameters['type'] ?? $parameters[1] ?? null;
		return parent::make($resource);
	}

    public static function collection(...$parameters)
	{
		$resource = $parameters['resource'] ?? $parameters[0];
		self::$type = $parameters['type'] ?? $parameters[1] ?? null;
		return parent::collection($resource);
	}

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        if (self::$type == 'UserProfile') {
            return [
                $this->mergeWhen(env('APP_DEBUG', false), [
                    'id' => $this->id,
                ]),
                'uuid' => $this->uuid,
                'name' => $this->name,
                'email' => $this->email,
                'emailVerified' => ! is_null($this->email_verified_at),
                'passwordExpiryDay' => $this->getPasswordExpiryDay($this->password_changed_at),
                $this->mergeWhen($this->relationLoaded('profile'), [
                    'profile' => new ProfileResource($this->whenLoaded('profile')),
                ]),
                $this->mergeWhen($this->relationLoaded('roles'), [
                    'roles' => RoleResource::collection($this->whenLoaded('roles')),
                    'rolesDescription' => $this->flattenRoles($this->whenLoaded('roles') ? $this->roles : null),
                    'selectedRoles' => $this->selectedRolesInArray($this->whenLoaded('roles') ? $this->roles : null),
                ]),
                $this->mergeWhen($this->relationLoaded('companies'), [
                    'companies' => CompanyResource::collection($this->whenLoaded('companies')),
                ]),
                $this->mergeWhen($this->relationLoaded('settings'), [
                    'selectedSettings' => $this->selectedSettingsInArray($this->whenLoaded('settings') ? $this->settings : null),
                ]),
            ];
        } else {
            return [
                $this->mergeWhen(env('APP_DEBUG', false), [
                    'id' => $this->id,
                ]),
                'uuid' => $this->uuid,
                'name' => $this->name,
                'email' => $this->email,
                'emailVerified' => ! is_null($this->email_verified_at),
                'passwordExpiryDay' => $this->getPasswordExpiryDay($this->password_changed_at),
                $this->mergeWhen($this->relationLoaded('profile'), [
                    'profile' => new ProfileResource($this->whenLoaded('profile')),
                ]),
                $this->mergeWhen($this->relationLoaded('roles'), [
                    'roles' => RoleResource::collection($this->whenLoaded('roles')),
                    'rolesDescription' => $this->flattenRoles($this->whenLoaded('roles') ? $this->roles : null),
                ]),
                $this->mergeWhen($this->relationLoaded('companies'), [
                    'companies' => CompanyResource::collection($this->whenLoaded('companies')),
                ]),
                $this->mergeWhen($this->relationLoaded('settings'), [
                    'settings' => SettingResource::collection($this->whenLoaded('settings')),
                ]),
            ];
        }
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

        return $roles->pluck('hId');
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
