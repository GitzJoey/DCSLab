<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laratrust\Models\Role as RoleModel;

class Role extends RoleModel
{
    use HasFactory;

    public $guarded = [];
}
