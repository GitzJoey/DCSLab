<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderDiscount extends Model
{
    protected $fillable = [
        'company_id',
        'branch_id',
        'purchase_order_id',
        'purchase_order_product_unit_id',
        'discount_type',
        'amount',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function purchaseOrderProductUnit()
    {
        return $this->belongsTo(PurchaseOrderProductUnit::class);
    }
}
