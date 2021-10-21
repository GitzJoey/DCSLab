<?php

namespace App\Models;

use Laratrust\Models\LaratrustRole;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Role extends LaratrustRole
{
    use HasFactory;

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
