<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Vinkla\Hashids\Facades\Hashids;

class Setting extends Model
{
    use LogsActivity;

    protected $table="settings";

    protected $fillable = [
        'type',
        'key',
        'value',
    ];

    protected static $logAttributes = ['value'];

    protected static $logOnlyDirty = true;

    protected $hidden = [
        'id',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
        'deleted_by',
        'deleted_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function hId() {
        return HashIds::encode($this->attributes['id']);
    }
}
