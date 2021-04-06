<?php

namespace App\Models;

use Laratrust\Models\LaratrustRole;

class Role extends LaratrustRole
{
    public $guarded = [];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
