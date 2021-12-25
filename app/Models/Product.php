<?php

namespace App\Models;

use App\Models\Brand;
use App\Models\Company;
use App\Models\Supplier;

use App\Models\ProductUnit;
use App\Models\ProductGroup;
use App\Models\SupplierProduct;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Support\Facades\Config;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory, LogsActivity;
    use SoftDeletes;

    protected $fillable = [
        'code',
        'product_group_id',
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

    protected static $logAttributes = [
        'code',
        'product_group_id',
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
        'product_group_id',
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

    protected $appends = ['hId', 'defaultSupplier'];

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

    public function productUnit()
    {
        return $this->hasMany(ProductUnit::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function supplier_product()
    {
        return $this->belongsTo(SupplierProduct::class);
    }

    public function getDefaultSupplierAttribute()
    {
        if ($this->supplier) return $this->supplier->hId;
        else return '';
    }

    public function scopeBySelectedCompany($query, $overrideCompanyId = '')
    {
        return $query->where('company_id', '=', empty($overrideCompanyId) ? Hashids::decode(session(Config::get('const.DEFAULT.SESSIONS.SELECTED_COMPANY')))[0]:$overrideCompanyId);
    }

    public function scopeStatusActive($query, $overrideStatus = '')
    {
        return $query->where('status', '=', empty($overrideStatus) ? 1:$overrideStatus);
    }
}