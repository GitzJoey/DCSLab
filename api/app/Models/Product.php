<?php

namespace App\Models;

use App\Traits\BootableModel;
use Illuminate\Database\Eloquent\Model;

use App\Models\Company;
use App\Models\ProductGroup;
use App\Models\Brand;
use App\Models\ProductUnit;
use App\Models\Supplier;

class Product extends Model
{
    use BootableModel;

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
