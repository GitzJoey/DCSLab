<?php

namespace App\Models;

use App\Traits\BootableModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Purchase extends Model
{
    use BootableModel;
    use HasFactory;
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
        'delivery_note_reference',
        'purchase_tax_invoice_number',
        'purchase_tax_invoice_vat_base',
        'purchase_tax_invoice_vat',
        'return_tax_invoice_number',
        'return_tax_invoice_vat_base',
        'return_tax_invoice_vat',
        'remarks',
        'is_posted',
        'purchase_total',
        'purchase_global_discount_rate',
        'purchase_global_discount_fixed',
        'purchase_additional_cost',
        'purchase_rounding',
        'purchase_grand_total',
        'return_total',
        'return_global_discount_rate',
        'return_global_discount_fixed',
        'return_rounding',
        'return_grand_total',
        'amount_due',
        'amount_paid_by_purchase_order_down_payment',
        'amount_paid_by_purchase_return',
        'amount_paid_before_invoice',
        'amount_paid_on_invoice',
        'amount_paid_after_invoice',
        'amount_paid_total',
        'amount_due',
        'is_paid_off',
        'is_valid',
    ];

    protected $casts = [
        'date' => 'datetime',
        'purchase_tax_invoice_vat_base' => 'decimal:8',
        'purchase_tax_invoice_vat' => 'decimal:8',
        'return_tax_invoice_vat_base' => 'decimal:8',
        'return_tax_invoice_vat' => 'decimal:8',
        'purchase_total' => 'decimal:8',
        'purchase_global_discount_rate' => 'decimal:8',
        'purchase_global_discount_fixed' => 'decimal:8',
        'purchase_additional_cost' => 'decimal:8',
        'purchase_rounding' => 'decimal:8',
        'purchase_grand_total' => 'decimal:8',
        'return_total' => 'decimal:8',
        'return_global_discount_rate' => 'decimal:8',
        'return_global_discount_fixed' => 'decimal:8',
        'return_rounding' => 'decimal:8',
        'return_grand_total' => 'decimal:8',
        'amount_due' => 'decimal:8',
        'amount_paid_by_purchase_order_down_payment' => 'decimal:8',
        'amount_paid_by_purchase_return' => 'decimal:8',
        'amount_paid_before_invoice' => 'decimal:8',
        'amount_paid_on_invoice' => 'decimal:8',
        'amount_paid_after_invoice' => 'decimal:8',
        'amount_paid_total' => 'decimal:8',
        'amount_due' => 'decimal:8',
        'is_paid_off' => 'boolean',
        'is_valid' => 'boolean',
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

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class)->withTrashed();
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class)->withTrashed();
    }

    public function purchaseProductUnitSerials()
    {
        return $this->hasMany(PurchaseProductUnitSerial::class);
    }

    public function purchaseAdditionalCosts()
    {
        return $this->hasMany(PurchaseAdditionalCost::class);
    }

    public function purchaseReturnProductUnitSerials()
    {
        return $this->hasMany(PurchaseReturnProductUnitSerial::class);
    }

    public function purchaseReturnAdditionalCosts()
    {
        return $this->hasMany(PurchaseReturnAdditionalCost::class);
    }

    public function purchasePayments()
    {
        return $this->hasMany(PurchasePayment::class);
    }

    public function purchaseReceipts()
    {
        return $this->hasMany(PurchaseReceipt::class);
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where('purchases.code', 'like', '%'.$search.'%')
            ->orWhere('purchases.date', 'like', '%'.$search.'%')
            ->orWhere('purchases.due_days', 'like', '%'.$search.'%')
            ->orWhere('purchases.remarks', 'like', '%'.$search.'%');
    }
}
