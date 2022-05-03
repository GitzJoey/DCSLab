<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Laratrust\Models\LaratrustRole;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Role extends LaratrustRole
{
    use HasFactory;

    public $guarded = [];

    protected $hidden = [
        'id',
        'description',
        'created_at',
        'updated_at',
    ];

    public function hId() : Attribute
    {
        return Attribute::make(
            get: fn () => HashIds::encode($this->attributes['id'])
        );
    }
}
