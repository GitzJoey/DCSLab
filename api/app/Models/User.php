<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laratrust\Traits\HasRolesAndPermissions;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory, Notifiable;
    use HasRolesAndPermissions;
    use TwoFactorAuthenticatable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'timezone',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    public function settings()
    {
        return $this->hasMany(Setting::class);
    }

    public function companies()
    {
        return $this->belongsToMany(Company::class);
    }

    public function employees()
    {
        return $this->belongsToMany(Employee::class);
    }

    public function customers()
    {
        return $this->belongsToMany(Customer::class);
    }

    public function suppliers()
    {
        return $this->belongsToMany(Supplier::class);
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->ulid = Str::ulid()->generate();
        });
    }
}
