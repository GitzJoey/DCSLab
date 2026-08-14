<?php

namespace App\Models;

use App\Traits\BootableModel;
use App\Traits\ScopeableByBranch;
use App\Traits\ScopeableByCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesOrderDeliveryItem extends Model
{
    use BootableModel;
    use HasFactory;
    use ScopeableByBranch;
    use ScopeableByCompany;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'branch_id',
        'sales_order_delivery_id',
        'sales_order_item_id',
        'has_sales_order_item_product',
        'qty',
        'product_unit_id',
        'product_id',
        'product_unit_conversion_value',
        'product_unit_qty_base',
        'base_unit_cogs',
        'total_cogs',
        'remarks',
    ];

    protected $casts = [
        'has_sales_order_item_product' => 'boolean',
        'qty' => 'decimal:8',
        'product_unit_conversion_value' => 'decimal:8',
        'product_unit_qty_base' => 'decimal:8',
        'base_unit_cogs' => 'decimal:8',
        'total_cogs' => 'decimal:8',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class)->withTrashed();
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class)->withTrashed();
    }

    public function salesOrderDelivery()
    {
        return $this->belongsTo(SalesOrderDelivery::class)->withTrashed();
    }

    /**
     * Only filled when this delivery item maps to a sales order line.
     */
    public function salesOrderItem()
    {
        return $this->belongsTo(SalesOrderItem::class)->withTrashed();
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
        return $this->hasMany(SalesOrderDeliveryItemSerial::class);
    }

    public function returnItems()
    {
        return $this->hasMany(SalesReturnItem::class);
    }
}
