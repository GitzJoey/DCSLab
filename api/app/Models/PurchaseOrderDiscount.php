<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Company;
use App\Models\Branch;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderProductUnit;

class PurchaseOrderDiscount extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'company_id',
        'branch_id',
        'purchase_order_id',
        'purchase_order_product_unit_id',
        'discount_type',
        'amount',
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
