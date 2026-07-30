<?php

namespace App\Models;

use App\Traits\BootableModel;
use App\Traits\ScopeableByBranch;
use App\Traits\ScopeableByCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrderReceiptItem extends Model
{
    use BootableModel;
    use HasFactory;
    use ScopeableByBranch;
    use ScopeableByCompany;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'branch_id',
        'purchase_order_receipt_id',
        'purchase_order_item_id',
        'has_purchase_order_item_product',
        'qty',
        'product_unit_id',
        'product_id',
        'product_unit_conversion_value',
        'product_unit_qty_base',
        'base_unit_value',
        'total_value',
        'remarks',
    ];

    protected $casts = [
        'has_purchase_order_item_product' => 'boolean',
        'qty' => 'decimal:8',
        'product_unit_conversion_value' => 'decimal:8',
        'product_unit_qty_base' => 'decimal:8',
        'base_unit_value' => 'decimal:8',
        'total_value' => 'decimal:8',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class)->withTrashed();
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class)->withTrashed();
    }

    public function purchaseOrderReceipt()
    {
        return $this->belongsTo(PurchaseOrderReceipt::class)->withTrashed();
    }

    /**
     * Only filled when this receipt item maps to a purchase order line (matched by product).
     */
    public function purchaseOrderItem()
    {
        return $this->belongsTo(PurchaseOrderItem::class)->withTrashed();
    }

    public function productUnit()
    {
        return $this->belongsTo(ProductUnit::class)->withTrashed();
    }

    public function product()
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    public function stockTransaction()
    {
        return $this->morphOne(StockTransaction::class, 'referable');
    }

    public function serials()
    {
        return $this->hasMany(PurchaseOrderReceiptItemSerial::class);
    }

    public function returnItems()
    {
        return $this->hasMany(PurchaseReturnItem::class);
    }
}
