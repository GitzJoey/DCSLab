<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Company;
use App\Models\User;
use Spatie\Activitylog\Traits\LogsActivity;
use Vinkla\Hashids\Facades\Hashids;

class Employee extends Model
{
    use HasFactory, LogsActivity;

    protected $table="users";

    protected $fillable = [
        'name',
        'email'
    ];

    protected static $logAttributes = ['name'];

    protected static $logOnlyDirty = true;

    protected $hidden = [
        'id',
        'email_verified_at',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected $appends = ['hId'];

    public function getHIdAttribute() : string
    {
        return HashIds::encode($this->attributes['id']);
    }

    public function companies()
    {
        return $this->hasMany(Company::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
