<?php

namespace App\Models;

use App\Traits\BootableModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SaleReceipt extends Model
{
    use BootableModel;
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'branch_id',
        'code',
        'sale_id',
        'warehouse_id',
    ];

    protected $casts = [

    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function saleReceiptProductUnits()
    {
        return $this->hasMany(SaleReceiptProductUnit::class);
    }

    public function saleReceiptProductUnitSerials()
    {
        return $this->hasMany(SaleReceiptProductUnitSerial::class);
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where('sale_receipts.code', 'like', '%'.$search.'%');
    }
}
