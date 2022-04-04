<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Vinkla\Hashids\Facades\Hashids;
use Laratrust\Models\LaratrustPermission;

class Permission extends LaratrustPermission
{
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
