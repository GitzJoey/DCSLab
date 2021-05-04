<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laratrust\Traits\LaratrustUserTrait;

use Laravel\Sanctum\HasApiTokens;
use Vinkla\Hashids\Facades\Hashids;

class User extends Authenticatable
{
    use LaratrustUserTrait;
    use HasFactory, HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'id',
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected $appends = ['hId', 'selectedRoles', 'selectedSettings'];

    public function getHIdAttribute() : string
    {
        return HashIds::encode($this->attributes['id']);
    }

    public function getSelectedRolesAttribute()
    {
        return $this->roles()->get()->pluck('hId');
    }

    public function getSelectedSettingsAttribute()
    {
        $settings = array();
        foreach ($this->settings as $s) {
            $skey = '';
            switch ($s->key) {
                case 'THEME.CODEBASE':
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

    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    public function settings()
    {
        return $this->hasMany(Setting::class);
    }

    public function getSetting($key)
    {
        return $this->settings()->where('key', $key)->pluck('value')->first();
    }
}
