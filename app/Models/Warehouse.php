<?php

namespace App\Models;

use App\Enums\ActiveStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

use App\Models\Company;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

use Vinkla\Hashids\Facades\Hashids;

class Warehouse extends Model
{
    use HasFactory, LogsActivity;
    use SoftDeletes;
    
    protected $fillable = [
        'company_id',
        'branch_id',
        'code',
        'name',
        'address',
        'city',
        'contact',
        'status',
        'remarks'
    ];

    protected static $logAttributes = [
        'company_id',
        'branch_id',
        'code',
        'name',
        'address',
        'city',
        'contact',
        'status',
        'remarks'
    ];

    protected static $logOnlyDirty = true;

    protected $hidden = [];

    protected $casts = [
        'status' => ActiveStatus::class
    ];

    public function hId() : Attribute
    {
        return Attribute::make(
            get: fn () => HashIds::encode($this->attributes['id'])
        );
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
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

        static::deleting(function ($model) {
            $user = Auth::check();
            if ($user) {
                $model->deleted_by = Auth::id();
                $model->save();
            }
        });
    }
}