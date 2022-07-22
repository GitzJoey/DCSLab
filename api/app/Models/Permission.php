<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Laratrust\Models\LaratrustPermission;
use Vinkla\Hashids\Facades\Hashids;

class Permission extends LaratrustPermission
{
    public $guarded = [];

    public function hId(): Attribute
    {
        return Attribute::make(
            get: fn () => Hashids::encode($this->attributes['id'])
        );
    }
}
