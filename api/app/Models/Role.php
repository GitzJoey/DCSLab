<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laratrust\Models\LaratrustRole;
use Vinkla\Hashids\Facades\Hashids;

class Role extends LaratrustRole
{
    use HasFactory;
    
    public $guarded = [];

    public function hId() : Attribute
    {
        return Attribute::make(
            get: fn () => Hashids::encode($this->attributes['id'])
        );
    }
}
