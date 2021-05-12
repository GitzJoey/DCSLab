<?php

namespace App\Models;

use Laratrust\Models\LaratrustRole;
use Spatie\Activitylog\Traits\LogsActivity;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Role extends LaratrustRole
{
    use HasFactory, LogsActivity;

    public $guarded = [];

    protected static $logAttributes = ['display_name', 'description'];

    protected static $logOnlyDirty = true;

    protected $hidden = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected $appends = ['hId'];

    public function getHIdAttribute() : string
    {
        return HashIds::encode($this->attributes['id']);
    }
}
