<?php

namespace App\Models;

use Laratrust\Models\LaratrustPermission;
use Spatie\Activitylog\Traits\LogsActivity;
use Vinkla\Hashids\Facades\Hashids;

class Permission extends LaratrustPermission
{
    public $guarded = [];

    protected $hidden = [
        'id',
        'name',
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
