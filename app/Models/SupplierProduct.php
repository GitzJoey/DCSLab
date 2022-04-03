<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Company;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\SoftDeletes;
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

    protected $hidden = [
        'id',
        'company_id',
        'supplier_id',
        'product_id',
        'created_by',
        'updated_by',
        'deleted_by',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public function hId() : Attribute
    {
        return Attribute::make(
            get: fn () => HashIds::encode($this->attributes['id'])
        );
    }
    
    public function supplier_hId() : Attribute
    {
        return Attribute::make(
            get: fn () => HashIds::encode($this->attributes['supplier_id'])
        );
    }

    public function product_hId() : Attribute
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
}
