<?php

namespace App\Models;

use App\Enums\ActiveStatus;
use App\Enums\ProductType;
use App\Traits\ScopeableByCompany;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

use App\Models\Company;
use App\Models\ProductGroup;
use App\Models\Brand;
use App\Models\ProductUnit;
use App\Models\Supplier;

use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Vinkla\Hashids\Facades\Hashids;

class Product extends Model
{
    use HasFactory, LogsActivity;
    use SoftDeletes;

    use ScopeableByCompany;

    protected $fillable = [
        'company_id',
        'product group_id',
        'brand_id',
        'code',
        'name',
        'product_type',
        'taxable_supply',
        'standard_rated_supply',
        'price_include_vat',
        'point',
        'use_serial_number',
        'has_expiry_date',
        'status',       
        'remarks'
    ];

    protected static $logAttributes = [
        'company_id',
        'product group_id',
        'brand_id',
        'code',
        'name',
        'product_type',
        'taxable_supply',
        'standard_rated_supply',
        'price_include_vat',
        'point',
        'use_serial_number',
        'has_expiry_date',
        'status',       
        'remarks'
    ];

    protected static $logOnlyDirty = true;

    protected $hidden = [
        'id',
        'company_id',
        'product_group_id',
        'brand_id',
        'created_by',
        'updated_by',
        'deleted_by',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'taxable_supply' => 'boolean',
        'price_include_vat' => 'boolean',
        'use_serial_number' => 'boolean',
        'has_expiry_date' => 'boolean',
        'status' => ActiveStatus::class,
        'product_type' => ProductType::class
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

    public function productGroup()
    {
        return $this->belongsTo(ProductGroup::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function productUnits()
    {
        return $this->hasMany(ProductUnit::class);
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
