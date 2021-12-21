<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Company;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Vinkla\Hashids\Facades\Hashids;

class SupplierProduct extends Model
{
    use LogsActivity;
    use SoftDeletes;

    protected $table = 'supplier_products';

    protected $fillable = [
        'company_id',
        'supplier_id',
        'product_id',
        'main_product',
    ];

    protected static $logAttributes = ['company_id', 'supplier_id', 'product_id', 'main_product'];

    protected static $logOnlyDirty = true;

    protected $hidden = [
        'id',
        'company_id',
        'supplier_id',
        'product_id',
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

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }
}
