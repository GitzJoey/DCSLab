<?php

namespace App\Models;

use App\Enums\ProductType;
use App\Enums\RecordStatus;
use App\Traits\BootableModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use BootableModel;
    use HasFactory;
    use SoftDeletes;

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
        'remarks',
    ];

    protected $casts = [
        'use_serial_number' => 'boolean',
        'has_expiry_date' => 'boolean',
        'product_type' => ProductType::class,
        'status' => RecordStatus::class,
    ];

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
}
