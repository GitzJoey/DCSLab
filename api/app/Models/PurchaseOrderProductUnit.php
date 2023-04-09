<?php

namespace App\Models;

use App\Enums\DiscountType;
use App\Traits\BootableModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderProductUnit extends Model
{
    use HasFactory;
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

    public function productUnitPerUnitDiscount()
    {
        $result = $this->hasMany(PurchaseOrderDiscount::class)
        ->where(function ($query) {
            $query->where('discount_type', '=', DiscountType::PER_UNIT_PERCENT_DISCOUNT->value)
                ->orWhere('discount_type', '=', DiscountType::PER_UNIT_NOMINAL_DISCOUNT->value);
        });

        return $result;
    }

    public function productUnitPerUnitSubTotalDiscount()
    {
        $result = $this->hasMany(PurchaseOrderDiscount::class)
        ->where(function ($query) {
            $query->where('discount_type', '=', DiscountType::PER_UNIT_SUBTOTAL_PERCENT_DISCOUNT->value)
                ->orWhere('discount_type', '=', DiscountType::PER_UNIT_SUBTOTAL_NOMINAL_DISCOUNT->value);
        });

        return $result;
    }
}
