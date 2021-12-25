<?php

namespace App\Models;

use App\Models\Company;
use App\Models\Product;
use App\Models\SupplierProduct;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Supplier extends Model
{
    use HasFactory, LogsActivity;
    use SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'payment_term_type',
        'contact',
        'address',
        'city',
        'taxable_enterprise',
        'tax_id',
        'remarks',
        'status'
    ];

    protected static $logAttributes = ['code', 'name', 'payment_term_type', 'contact', 'address', 'city', 'taxable_enterprise', 'tax_id', 'remarks', 'status'];

    protected static $logOnlyDirty = true;

    protected $hidden = [
        'id',
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
        return $this->hasMany(Product::class);
    }

    public function supplier_product()
    {
        return $this->hasMany(SupplierProduct::class);
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