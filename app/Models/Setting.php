<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Vinkla\Hashids\Facades\Hashids;

class Setting extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $table = 'settings';

    protected $fillable = [
        'type',
        'key',
        'value',
    ];

    protected static $logAttributes = ['value'];

    protected static $logOnlyDirty = true;

    protected $hidden = [];

    public function hId() : Attribute
    {
        return Attribute::make(
            get: fn () => HashIds::encode($this->attributes['id'])
        );
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $user = Auth::check();
            if ($user) {
                $model->created_by = Auth::id();
                $model->updated_by = Auth::id();
            }
        });

        static::updating(function ($model) {
            $user = Auth::check();
            if ($user) {
                $model->updated_by = Auth::id();
            }
        });
    }
}
