<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laratrust\Models\LaratrustRole;
use Vinkla\Hashids\Facades\Hashids;

class Role extends LaratrustRole
{
    use HasFactory;

    public $guarded = [];

    protected $hidden = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function hId() {
        return HashIds::encode($this->attributes['id']);
    }
}
