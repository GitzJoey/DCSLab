<?php

namespace App\Models;

use App\Traits\BootableModel;
use App\Traits\ScopeableByBranch;
use App\Traits\ScopeableByCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrderItem extends Model
{
    use BootableModel;
    use HasFactory;
    use ScopeableByBranch;
    use ScopeableByCompany;
    use SoftDeletes;

    protected $fillable = [
        'company_id', // user_input
        'branch_id', // user_input
        'purchase_order_id', // user_input
        'qty', // user_input
        'product_unit_id', // user_input
        'product_unit_conversion_value', // user_input
        'product_unit_qty_base', // calculated_after_save_item_row
        'product_unit_price', // user_input
        'product_unit_price_discount', // calculated_after_save_item_row
        'product_unit_price_after_discount', // calculated_after_save_item_row
        'product_unit_subtotal', // calculated_after_save_item_row
        'product_unit_subtotal_discount', // calculated_after_save_item_row
        'product_unit_subtotal_after_discount', // calculated_after_save_item_row
        'product_unit_global_discount', // calculated_after_save_discount_rows
        'product_unit_total_before_vat', // calculated_after_save_discount_rows
        'is_vat_included', // user_input
        'vat_profile_id', // user_input
        'vat_rate', // user_input
        'vat_base_numerator', // user_input
        'vat_base_denominator', // user_input
        'product_unit_vat_base', // calculated_after_save_discount_rows
        'product_unit_vat', // calculated_after_save_discount_rows
        'product_unit_rounding', // calculated_after_save_discount_rows
        'product_unit_grand_total', // calculated_after_save_discount_rows
        'product_unit_cogs', // calculated_after_save_discount_rows
        'product_unit_total_cogs', // calculated_after_save_discount_rows
        'product_unit_base_unit_cogs', // calculated_after_save_discount_rows
        'remarks', // user_input
    ];

    protected $casts = [
        'qty' => 'decimal:8',
        'product_unit_conversion_value' => 'decimal:8',
        'product_unit_qty_base' => 'decimal:8',
        'product_unit_price' => 'decimal:8',
        'product_unit_price_discount' => 'decimal:8',
        'product_unit_price_after_discount' => 'decimal:8',
        'product_unit_subtotal' => 'decimal:8',
        'product_unit_subtotal_discount' => 'decimal:8',
        'product_unit_subtotal_after_discount' => 'decimal:8',
        'product_unit_global_discount' => 'decimal:8',
        'product_unit_total_before_vat' => 'decimal:8',
        'is_vat_included' => 'boolean',
        'vat_rate' => 'decimal:8',
        'vat_base_numerator' => 'integer',
        'vat_base_denominator' => 'integer',
        'product_unit_vat_base' => 'decimal:8',
        'product_unit_vat' => 'decimal:8',
        'product_unit_rounding' => 'decimal:8',
        'product_unit_grand_total' => 'decimal:8',
        'product_unit_cogs' => 'decimal:8',
        'product_unit_total_cogs' => 'decimal:8',
        'product_unit_base_unit_cogs' => 'decimal:8',
    ];

    public function getVatBaseFactorValue(): float
    {
        if ($this->vat_base_denominator <= 0) {
            return 0;
        }

        return (float) $this->vat_base_numerator / (float) $this->vat_base_denominator;
    }

    public function company()
    {
        return $this->belongsTo(Company::class)->withTrashed();
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class)->withTrashed();
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class)->withTrashed();
    }

    public function productUnit()
    {
        return $this->belongsTo(ProductUnit::class)->withTrashed();
    }

    public function vatProfile()
    {
        return $this->belongsTo(VatProfile::class)->withTrashed();
    }

    public function productUnitPriceDiscounts()
    {
        return $this->hasMany(PurchaseOrderItemProductUnitPriceDiscount::class);
    }

    public function subtotalDiscounts()
    {
        return $this->hasMany(PurchaseOrderItemSubtotalDiscount::class);
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($query) use ($search) {
            $query->where('purchase_order_items.remarks', 'like', '%'.$search.'%');
        });
    }
}
