<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductUnit extends Model
{

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
}
