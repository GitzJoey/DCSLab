<?php

namespace App\Models;

use App\Enums\DiscountType;
use App\Traits\BootableModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrderDiscount extends Model
{
    use BootableModel;
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'branch_id',
        'purchase_order_id',
        'purchase_order_product_unit_id',
        'order',
        'discount_type',
        'amount',
    ];

    protected $casts = [
        'discount_type' => DiscountType::class,
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
