<?php

namespace App\Models;

use App\Traits\BootableModel;
use Illuminate\Database\Eloquent\Model;

use App\Models\Branch;
use App\Models\Company;
use App\Models\PurchaseOrder;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\PurchaseOrderDiscount;

class PurchaseOrderProductUnit extends Model
{
    use BootableModel;

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
        return $this->hasMany(PurchaseOrderDiscount::class);
    }
}
