<?php

namespace App\Models;

use App\Traits\BootableModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory;
    use BootableModel;

    protected $fillable = [
        'company_id',
        'branch_id',
        'invoice_code',
        'invoice_date',
        'shipping_date',
        'shipping_address',
        'supplier_id',
        'remarks',
        'status',
    ];

    protected $casts = [
        'status' => RecordStatus::class,
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function purchaseOrderDiscounts()
    {
        return $this->hasMany(PurchaseOrderDiscount::class);
    }

    public function purchaseOrderProductUnits()
    {
        return $this->hasMany(PurchaseOrderProductUnit::class);
    }
}
