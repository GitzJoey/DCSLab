<?php

namespace App\Models;

use App\Traits\BootableModel;
use App\Traits\ScopeableByBranch;
use App\Traits\ScopeableByCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseReturnItem extends Model
{
    use BootableModel;
    use HasFactory;
    use ScopeableByBranch;
    use ScopeableByCompany;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'branch_id',
        'purchase_return_id',
        'purchase_order_receipt_item_id',
        'qty',
        'product_unit_id',
        'product_id',
        'product_unit_conversion_value',
        'product_unit_qty_base',
        'product_unit_price',
        'product_unit_is_price_include_vat',
        'price_discount',
        'price_after_discount',
        'subtotal',
        'subtotal_discount',
        'subtotal_after_discount',
        'global_discount',
        'subtotal_after_global_discount',
        'vat_profile_id',
        'vat_rate',
        'vat_base_numerator',
        'vat_base_denominator',
        'vat_base',
        'vat',
        'subtotal_after_vat',
        'rounding',
        'amount_payable',
        'cogs',
        'total_cogs',
        'base_unit_cogs',
        'remarks',
    ];

    protected $casts = [
        'qty' => 'decimal:8',
        'product_unit_conversion_value' => 'decimal:8',
        'product_unit_qty_base' => 'decimal:8',
        'product_unit_price' => 'decimal:8',
        'product_unit_is_price_include_vat' => 'boolean',
        'price_discount' => 'decimal:8',
        'price_after_discount' => 'decimal:8',
        'subtotal' => 'decimal:8',
        'subtotal_discount' => 'decimal:8',
        'subtotal_after_discount' => 'decimal:8',
        'global_discount' => 'decimal:8',
        'subtotal_after_global_discount' => 'decimal:8',
        'vat_rate' => 'decimal:8',
        'vat_base_numerator' => 'integer',
        'vat_base_denominator' => 'integer',
        'vat_base' => 'decimal:8',
        'vat' => 'decimal:8',
        'subtotal_after_vat' => 'decimal:8',
        'rounding' => 'decimal:8',
        'amount_payable' => 'decimal:8',
        'cogs' => 'decimal:8',
        'total_cogs' => 'decimal:8',
        'base_unit_cogs' => 'decimal:8',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class)->withTrashed();
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class)->withTrashed();
    }

    public function purchaseReturn()
    {
        return $this->belongsTo(PurchaseReturn::class)->withTrashed();
    }

    /**
     * Only filled when this return item comes from a purchase order receipt line.
     */
    public function purchaseOrderReceiptItem()
    {
        return $this->belongsTo(PurchaseOrderReceiptItem::class)->withTrashed();
    }

    public function productUnit()
    {
        return $this->belongsTo(ProductUnit::class)->withTrashed();
    }

    public function product()
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    public function vatProfile()
    {
        return $this->belongsTo(VatProfile::class)->withTrashed();
    }

    public function itemSerials()
    {
        return $this->hasMany(PurchaseReturnItemSerial::class);
    }

    public function stockTransaction()
    {
        return $this->morphOne(StockTransaction::class, 'referable');
    }
}
