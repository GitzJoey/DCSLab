<?php

namespace App\Models;

use App\Traits\BootableModel;
use App\Traits\ScopeableByBranch;
use App\Traits\ScopeableByCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseReturnShipmentItem extends Model
{
    use BootableModel;
    use HasFactory;
    use ScopeableByBranch;
    use ScopeableByCompany;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'branch_id',
        'purchase_return_shipment_id',
        'purchase_return_item_id',
        'qty',
        'product_unit_id',
        'product_unit_conversion_value',
        'product_unit_qty_base',
        'remarks',
    ];

    protected $casts = [
        'qty' => 'decimal:8',
        'product_unit_conversion_value' => 'decimal:8',
        'product_unit_qty_base' => 'decimal:8',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class)->withTrashed();
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class)->withTrashed();
    }

    public function purchaseReturnShipment()
    {
        return $this->belongsTo(PurchaseReturnShipment::class)->withTrashed();
    }

    public function purchaseReturnItem()
    {
        return $this->belongsTo(PurchaseReturnItem::class)->withTrashed();
    }

    public function productUnit()
    {
        return $this->belongsTo(ProductUnit::class)->withTrashed();
    }

    public function itemSerials()
    {
        return $this->hasMany(PurchaseReturnShipmentItemSerial::class);
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($query) use ($search) {
            $query->where('purchase_return_shipment_items.remarks', 'like', '%'.$search.'%');
        });
    }
}
