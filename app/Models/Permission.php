<?php

namespace App\Models;

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

    protected $appends = ['hId'];

    public function getHIdAttribute() : string
    {
        return HashIds::encode($this->attributes['id']);
    }
}
