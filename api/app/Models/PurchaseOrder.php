<?php

namespace App\Models;

use App\Traits\BootableModel;
use App\Traits\ScopeableByBranch;
use App\Traits\ScopeableByCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model
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
        'remarks',

        'item_total_before_global_discount',
        'global_discount',
        'item_total_after_global_discount',
        'vat_base',
        'vat',
        'rounding',
        'grand_total',
        'amount_paid_down_payment',
        'amount_allocated_down_payment',
        'amount_refunded_down_payment',
        'amount_available_down_payment',
    ];

    protected $casts = [
        'date' => 'datetime',
        'due_days' => 'integer',
        'item_total_before_global_discount' => 'decimal:8',
        'global_discount' => 'decimal:8',
        'item_total_after_global_discount' => 'decimal:8',
        'vat_base' => 'decimal:8',
        'vat' => 'decimal:8',
        'rounding' => 'decimal:8',
        'grand_total' => 'decimal:8',
        'amount_paid_down_payment' => 'decimal:8',
        'amount_allocated_down_payment' => 'decimal:8',
        'amount_refunded_down_payment' => 'decimal:8',
        'amount_available_down_payment' => 'decimal:8',
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

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function globalDiscounts()
    {
        return $this->hasMany(PurchaseOrderGlobalDiscount::class);
    }

    public function downPayments()
    {
        return $this->hasMany(PurchaseOrderDownPayment::class);
    }

    public function refundedDownPayments()
    {
        return $this->hasMany(PurchaseOrderDownPaymentRefund::class);
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($query) use ($search) {
            $query->where('code', 'like', '%'.$search.'%')
                ->orWhere('remarks', 'like', '%'.$search.'%');
        });
    }
}
