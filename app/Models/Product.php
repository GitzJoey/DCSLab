<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ProductGroup;
use App\Models\ProductBrand;
use App\Models\ProductUnit;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;

class Product extends Model
{
    use HasFactory, LogsActivity;
    use SoftDeletes;

    protected $fillable = [
        'code',
        'group_id',
        'brand_id',
        'name',
        'unit_id',
        'price',
        'tax_status',
        'information',
        'estimated_capital_price',
        'is_use_serial',
        'is_buy',
        'is_production_material',
        'is_production_result',
        'is_sell',
        'status'
    ];

    protected static $logAttributes = ['code', 'group_id', 'brand_id', 'name', 'unit_id', 'price', 'tax_status', 'information', 'estimated_capital_price', 'is_use_serial', 'is_buy', 'is_production_material', 'is_production_result', 'is_sell', 'status'];

    protected static $logOnlyDirty = true;

    protected $hidden = [
        'created_by',
        'updated_by',
        'deleted_by',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public function group()
    {
        return $this->belongsTo(ProductGroup::class);
    }

    public function brand()
    {
        return $this->belongsTo(ProductBrand::class);
    }

    public function unit()
    {
        return $this->belongsTo(ProductUnit::class);
    }
}
