<?php

namespace App\Models;

use App\Traits\BootableModel;
use App\Traits\ScopeableByBranch;
use App\Traits\ScopeableByCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseReturn extends Model
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
        'supplier_id',
        'code',
        'date',
        'remarks',
        'is_posted',
        'item_total_before_global_discount',
        'global_discount',
        'item_total_after_global_discount',
        'vat_base',
        'vat',
        'item_total_after_vat',
        'additional_cost',
        'rounding',
        'amount_payable',
        'amount_allocated_to_purchase',
        'amount_received_total',
        'amount_settled_total',
        'amount_available',
        'is_settled',
    ];

    protected $casts = [
        'date' => 'datetime',
        'is_posted' => 'boolean',
        'item_total_before_global_discount' => 'decimal:8',
        'global_discount' => 'decimal:8',
        'item_total_after_global_discount' => 'decimal:8',
        'vat_base' => 'decimal:8',
        'vat' => 'decimal:8',
        'item_total_after_vat' => 'decimal:8',
        'additional_cost' => 'decimal:8',
        'rounding' => 'decimal:8',
        'amount_payable' => 'decimal:8',
        'amount_allocated_to_purchase' => 'decimal:8',
        'amount_received_total' => 'decimal:8',
        'amount_settled_total' => 'decimal:8',
        'amount_available' => 'decimal:8',
        'is_settled' => 'boolean',
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

    public function supplier()
    {
        return $this->belongsTo(Supplier::class)->withTrashed();
    }

    public function items()
    {
        return $this->hasMany(PurchaseReturnItem::class);
    }

    public function shipments()
    {
        return $this->hasMany(PurchaseReturnShipment::class);
    }

    public function allocations()
    {
        return $this->hasMany(PurchaseReturnAllocation::class);
    }

    public function payments()
    {
        return $this->hasMany(PurchaseReturnPayment::class);
    }

    public function globalDiscounts()
    {
        return $this->hasMany(PurchaseReturnGlobalDiscount::class);
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($query) use ($search) {
            $query->where('purchase_returns.code', 'like', '%'.$search.'%')
                ->orWhere('purchase_returns.remarks', 'like', '%'.$search.'%');
        });
    }
}
