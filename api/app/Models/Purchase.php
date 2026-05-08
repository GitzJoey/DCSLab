<?php

namespace App\Models;

use App\Enums\PurchaseProgressStatusEnum;
use App\Enums\PurchaseReceiptModeEnum;
use App\Traits\BootableModel;
use App\Traits\ScopeableByBranch;
use App\Traits\ScopeableByCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
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
        'supplier_id',
        'purchase_order_id',
        'receipt_mode',
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

        'progress_status',
        'item_total_count',
        'item_matched_count',
        'item_less_count',
        'item_more_count',
        'item_unlinked_count',
    ];

    protected $casts = [
        'date' => 'datetime',
        'due_days' => 'integer',
        'receipt_mode' => PurchaseReceiptModeEnum::class,
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
        'progress_status' => PurchaseProgressStatusEnum::class,
        'item_total_count' => 'integer',
        'item_matched_count' => 'integer',
        'item_less_count' => 'integer',
        'item_more_count' => 'integer',
        'item_unlinked_count' => 'integer',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class)->withTrashed();
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class)->withTrashed();
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

    public function directReceipt()
    {
        return $this->hasOne(PurchaseReceipt::class)->where('is_from_direct_purchase', true);
    }

    public function manualReceipts()
    {
        return $this->hasMany(PurchaseReceipt::class)->where('is_from_direct_purchase', false);
    }

    public function receiptItems(): HasManyThrough
    {
        return $this->hasManyThrough(
            PurchaseReceiptItem::class,
            PurchaseReceipt::class,
            'purchase_id',
            'purchase_receipt_id',
            'id',
            'id'
        );
    }

    public function payments()
    {
        return $this->hasMany(PurchasePayment::class);
    }

    public function additionalCosts()
    {
        return $this->hasMany(PurchaseAdditionalCost::class);
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
}
