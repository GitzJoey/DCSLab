<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
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

    public function purchaseOrderProductUnits()
    {
        return $this->hasMany(PurchaseOrderProductUnit::class);
    }

    public function purchaseOrderDiscounts()
    {
        return $this->hasMany(PurchaseOrderDiscount::class);
    }
}
