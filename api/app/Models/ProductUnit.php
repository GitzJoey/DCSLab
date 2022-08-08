<?php

namespace App\Models;

use App\Models\Company;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Vinkla\Hashids\Facades\Hashids;

class ProductUnit extends Model
{
    use HasFactory, LogsActivity;
    use SoftDeletes;

    protected $table = 'product_units';

    protected $fillable = [
        'company_id',
        'product_id',
        'unit_id',
        'code',
        'is_base',
        'conversion_value',
        'is_primary_unit',
        'remarks',
    ];

    protected static $logAttributes = ['company_id', 'product_id', 'unit_id', 'code', 'is_base', 'conversion_value', 'is_primary_unit', 'remarks'];

    protected static $logOnlyDirty = true;

    protected $hidden = [];

    protected $casts = [
        'is_base' => 'boolean',
        'is_primary_unit' => 'boolean',
    ];

    public function hId(): Attribute
    {
        return Attribute::make(
            get: fn () => HashIds::encode($this->attributes['id'])
        );
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
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
