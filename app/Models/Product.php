<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

use App\Models\Company;
use App\Models\ProductGroup;
use App\Models\Brand;
use App\Models\Unit;
use App\Models\ProductUnit;
use App\Models\Supplier;

use Spatie\Activitylog\LogOptions;
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
        'product_unit',
        'tax_status',
        'supplier_id',
        'remarks',
        'point',
        'use_serial_number',
        'has_expiry_date',
        'product_type',
        'status'
    ];

    protected static $logAttributes = [
        'code',
        'group_id',
        'brand_id',
        'name',
        'product_unit',
        'unit',
        'tax_status',
        'supplier_id',
        'remarks',
        'point',
        'use_serial_number',
        'has_expiry_date',
        'product_type',
        'status'
    ];

    protected static $logOnlyDirty = true;

    protected $hidden = [
        'id',
        'group_id',
        'brand_id',
        'unit_id',
        'supplier_id',
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

    public function mainSupplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function scopeWhereCompanyId($query, $companyId = null)
    {
        if ($companyId != null) {
            if (is_array($companyId)) {
                $query->whereIn('company_id', $companyId);
            } else {
                $query->where('company_id', '=', $companyId);
            }
        }
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }
}
