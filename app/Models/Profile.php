<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Vinkla\Hashids\Facades\Hashids;

class Profile extends Model
{
    use HasFactory, LogsActivity;

    protected $table="profiles";

    protected $fillable = [
        'first_name',
        'last_name',
        'address',
        'city',
        'postal_code',
        'country',
        'status',
        'tax_id',
        'ic_num',
        'img_path',
        'remarks',
    ];

    protected static $logAttributes = [
        'first_name',
        'last_name',
        'address',
        'city',
        'postal_code',
        'country',
        'status',
        'tax_id',
        'ic_num',
        'img_path',
        'remarks',
    ];

    protected static $logOnlyDirty = true;

    protected $hidden = [
        'id',
        'user_id',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
        'deleted_by',
        'deleted_at',
    ];

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
}
