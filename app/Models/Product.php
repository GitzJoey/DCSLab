<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'group_id',
        'brand_id',
        'name',
        'unit_id',
        'price',
        'tax',
        'information',
        'estimated_capital_price',
        'is_use_serial',
        'is_buy',
        'is_production_material',
        'is_production_result',
        'is_sell',
        'is_active'
    ];

    protected $hidden = [
        'created_by',
        'updated_by',
        'deleted_by',
        'created_at',
        'updated_at',
        'deleted_at'
    ];
}
