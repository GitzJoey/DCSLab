<?php

namespace App\Models;

use App\Traits\BootableModel;
use App\Traits\ScopeableByBranch;
use App\Traits\ScopeableByCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Purchase extends Model
{
    use BootableModel;
    use HasFactory;
    use ScopeableByBranch;
    use ScopeableByCompany;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'branch_id',
        'code',
        'date',
        'due_days',
        'warehouse_id',
        'supplier_id',
        'purchase_order_id',
        'tax_invoice_number',
        'tax_invoice_vat_base',
        'tax_invoice_vat',
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
        'amount_paid_by_purchase_order_down_payment',
        'amount_paid_by_purchase_return',
        'amount_paid_total',
        'amount_due',
        'is_paid_off',
    ];

    protected $casts = [
        'date' => 'datetime',
        'due_days' => 'integer',
        'tax_invoice_vat_base' => 'decimal:8',
        'tax_invoice_vat' => 'decimal:8',
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
        'amount_paid_by_purchase_order_down_payment' => 'decimal:8',
        'amount_paid_by_purchase_return' => 'decimal:8',
        'amount_paid_total' => 'decimal:8',
        'amount_due' => 'decimal:8',
        'is_paid_off' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class)->withTrashed();
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class)->withTrashed();
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class)->withTrashed();
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class)->withTrashed();
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class)->withTrashed();
    }

    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function receipts()
    {
        return $this->hasMany(PurchaseReceipt::class);
    }

    public function payments()
    {
        return $this->hasMany(PurchasePayment::class);
    }

    public function globalDiscounts()
    {
        return $this->hasMany(PurchaseGlobalDiscount::class);
    }

    public function purchaseOrderDownPaymentAllocations()
    {
        return $this->hasMany(PurchaseOrderDownPaymentAllocation::class);
    }

    public function purchaseReturns()
    {
        return $this->hasMany(PurchaseReturn::class);
    }

    public function purchaseReturnAllocations()
    {
        return $this->hasMany(PurchaseReturnAllocation::class);
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($query) use ($search) {
            $query->where('purchases.code', 'like', '%'.$search.'%')
                ->orWhere('purchases.tax_invoice_number', 'like', '%'.$search.'%')
                ->orWhere('purchases.remarks', 'like', '%'.$search.'%');
        });
    }
}
