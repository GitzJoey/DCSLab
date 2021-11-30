<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Company;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Vinkla\Hashids\Facades\Hashids;

class ProductUnit extends Model
{
    use LogsActivity;
    use SoftDeletes;

    protected $table = 'product_unit';

    protected $fillable = [
        'product_id',
        'unit_id',
        'is_base',
        'conversion_value',
        'is_primary_unit',
        'remarks'
    ];

    protected static $logAttributes = ['product_id', 'unit_id', 'is_base', 'conversion_value', 'is_primary_unit', 'remarks'];

    protected static $logOnlyDirty = true;

    protected $hidden = [
        'id',
        'product_id',
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
}
