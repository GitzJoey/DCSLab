<?php

namespace App\Models;

use App\Enums\DiscountTypeEnum;
use App\Traits\BootableModel;
use App\Traits\ScopeableByBranch;
use App\Traits\ScopeableByCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrderItemProductUnitPriceDiscount extends Model
{
    use BootableModel;
    use HasFactory;
    use ScopeableByBranch;
    use ScopeableByCompany;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'branch_id',
        'purchase_order_item_id',
        'sequence',
        'discount_type',
        'discount_value',
    ];

    protected $casts = [
        'sequence' => 'integer',
        'discount_type' => DiscountTypeEnum::class,
        'discount_value' => 'decimal:8',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class)->withTrashed();
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class)->withTrashed();
    }

    public function purchaseOrderItem()
    {
        return $this->belongsTo(PurchaseOrderItem::class)->withTrashed();
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($query) use ($search) {
            $query->where('purchase_order_item_product_unit_price_discounts.discount_type', 'like', '%'.$search.'%');
        });
    }
}
