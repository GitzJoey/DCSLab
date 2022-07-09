<?php

namespace App\Models;

use App\Models\Unit;
use App\Models\User;
use App\Models\Branch;
use App\Models\Product;
use App\Models\Employee;
use App\Models\Warehouse;
use App\Enums\RecordStatus;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Company extends Model
{
    use HasFactory, LogsActivity;
    use SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'address',
        'default',
        'status',
    ];

    protected static $logAttributes = ['code', 'name', 'address', 'default', 'status'];

    protected static $logOnlyDirty = true;

    protected $hidden = [];

    protected $casts = [
        'default' => 'boolean',
        'status' => RecordStatus::class,
    ];

    public function hId() : Attribute
    {
        return Attribute::make(
            get: fn () => HashIds::encode($this->attributes['id'])
        );
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function branches()
    {
        return $this->hasMany(Branch::class);
    }

    public function warehouses()
    {
        return $this->hasMany(Warehouse::class);
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    public function productGroups()
    {
        return $this->hasMany(ProductGroup::class);
    }

    public function brands()
    {
        return $this->hasMany(Brand::class);
    }

    public function units()
    {
        return $this->hasMany(Unit::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uuid = Str::uuid();

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
