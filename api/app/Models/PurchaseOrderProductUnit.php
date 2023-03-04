<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderProductUnit extends Model
{
    protected $fillable = [
        'company_id',
        'branch_id',
        'purchase_order_id',
        'product_id',
        'product_unit_id',
        'qty',
        'product_unit_amount_per_unit',
        'product_unit_amount_total',
        'product_unit_initial_price',
        'product_unit_per_unit_discount',
        'product_unit_sub_total',
        'product_unit_per_unit_sub_total_discount',
        'product_unit_total',
        'product_unit_global_discount_percent',
        'product_unit_global_discount_nominal',
        'product_unit_final_price',
        'vat_status',
        'vat_rate',
        'vat_amount',
        'remarks',
    ];
    
    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function productUnit()
    {
        return $this->belongsTo(ProductUnit::class);
    }

    public function purchaseOrderDiscounts()
    {
        return $this->hasMany(purchaseOrderDiscounts::class);
    }
}
