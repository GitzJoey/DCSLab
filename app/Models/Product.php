<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

use App\Models\ProductGroup;
use App\Models\ProductBrand;
use App\Models\Unit;

use Spatie\Activitylog\Traits\LogsActivity;
use Vinkla\Hashids\Facades\Hashids;

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
        'tax_status',
        'remarks',
        'estimated_capital_price',
        'point',
        'is_use_serial',
        'product_type',
        'status'
    ];

    protected static $logAttributes = [
        'code',
        'group_id',
        'brand_id',
        'name',
        'unit_id',
        'tax_status',
        'remarks',
        'estimated_capital_price',
        'point',
        'is_use_serial',
        'product_type',
        'status'
    ];

    protected static $logOnlyDirty = true;

    protected $hidden = [
        'id',
        'group_id',
        'brand_id',
        'unit_id',
        'created_by',
        'updated_by',
        'deleted_by',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $appends = ['hId'];

    public function getHIdAttribute() : string
    {
        return HashIds::encode($this->attributes['id']);
    }

    public function groups()
    {
        return $this->hasMany(ProductGroup::class);
    }

    public function brands()
    {
        return $this->hasMany(ProductBrand::class);
    }

    public function units()
    {
        return $this->hasMany(Unit::class);
    }
}
