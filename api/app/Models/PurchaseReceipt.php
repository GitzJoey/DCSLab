<?php

namespace App\Models;

use App\Traits\BootableModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseReceipt extends Model
{
    use BootableModel;
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'branch_id',
        'code',
        'purchase_id',
        'warehouse_id',
        'is_posted',
        'is_valid',
    ];

    protected $casts = [
        'is_posted' => 'boolean',
        'is_valid' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class)->withTrashed();
    }

    public function purchase()
    {
        return $this->belongsTo(Purchase::class)->withTrashed();
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class)->withTrashed();
    }

    public function purchaseReceiptProductUnits()
    {
        return $this->hasMany(PurchaseReceiptProductUnit::class);
    }

    public function purchaseReceiptProductUnitSerials()
    {
        return $this->hasMany(PurchaseReceiptProductUnitSerial::class);
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where('purchase_receipts.code', 'like', '%'.$search.'%');
    }
}
