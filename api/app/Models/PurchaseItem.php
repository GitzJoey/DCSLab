<?php

namespace App\Models;

use App\Traits\BootableModel;
use App\Traits\ScopeableByBranch;
use App\Traits\ScopeableByCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseItem extends Model
{
    use BootableModel;
    use HasFactory;
    use ScopeableByBranch;
    use ScopeableByCompany;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'branch_id',
        'purchase_id',
        'purchase_order_item_id',
        'qty',
        'product_unit_id',
        'product_unit_conversion_value',
        'product_unit_qty_base',
        'qty_received_base',
        'qty_outstanding_base',
        'qty_excess_base',
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
        'additional_cost',
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
        'qty_received_base' => 'decimal:8',
        'qty_outstanding_base' => 'decimal:8',
        'qty_excess_base' => 'decimal:8',
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
        'additional_cost' => 'decimal:8',
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

    public function purchase()
    {
        return $this->belongsTo(Purchase::class)->withTrashed();
    }

    public function purchaseOrderItem()
    {
        return $this->belongsTo(PurchaseOrderItem::class)->withTrashed();
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
        return $this->hasMany(PurchaseItemProductUnitPriceDiscount::class);
    }

    public function subtotalDiscounts()
    {
        return $this->hasMany(PurchaseItemSubtotalDiscount::class);
    }

    public function receiptItemsWithSameProduct(): HasManyThrough
    {
        $query = $this->hasManyThrough(
            PurchaseReceiptItem::class,
            PurchaseReceipt::class,
            'purchase_id',
            'purchase_receipt_id',
            'purchase_id',
            'id'
        );
        $productId = $this->productUnit?->product_id;

        if (is_null($productId)) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereHas('productUnit', function ($query) use ($productId) {
            $query->where('product_id', $productId);
        });
    }

    public function returnItems()
    {
        return $this->hasMany(PurchaseReturnItem::class);
    }
}
