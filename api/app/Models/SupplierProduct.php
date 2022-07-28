<?php

namespace App\Models;

use App\Models\Company;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Vinkla\Hashids\Facades\Hashids;

class SupplierProduct extends Model
{
    use LogsActivity;
    use SoftDeletes;

    protected $table = 'supplier_products';

    protected $fillable = [
        'main_product',
    ];

    protected static $logAttributes = ['company_id', 'supplier_id', 'product_id', 'main_product'];

    protected static $logOnlyDirty = true;

    protected $hidden = [];

    protected $casts = [
        'main_product' => 'boolean',
    ];

    public function hId(): Attribute
    {
        return Attribute::make(
            get: fn () => HashIds::encode($this->attributes['id'])
        );
    }

    public function supplier_hId(): Attribute
    {
        return Attribute::make(
            get: fn () => HashIds::encode($this->attributes['supplier_id'])
        );
    }

    public function product_hId(): Attribute
    {
        return Attribute::make(
            get: fn () => HashIds::encode($this->attributes['product_id'])
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

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
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
