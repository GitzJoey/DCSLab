<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laratrust\Models\LaratrustRole;

class Role extends LaratrustRole
{
    use HasFactory;

    public $guarded = [];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
