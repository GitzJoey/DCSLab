<?php

namespace App\Models;

use App\Traits\BootableModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model
{
    use BootableModel;
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'branch_id',
        'supplier_id',
        'code',
        'date',
        'shipping_date',
        'shipping_address',
        'remarks',
        'is_has_invoice',
        'is_received',
        'total',
        'global_discount_rate',
        'global_discount_fixed',
        'grand_total',
        'down_payment',
        'down_payment_due_days',
        'down_payment_applied',
        'down_payment_remaining',
        'is_down_payment_paid_off',
    ];

    protected $casts = [
        'is_has_invoice' => 'boolean',
        'is_received' => 'boolean',
        'total' => 'decimal:8',
        'global_discount_rate' => 'decimal:8',
        'global_discount_fixed' => 'decimal:8',
        'grand_total' => 'decimal:8',
        'down_payment' => 'decimal:8',
        'down_payment_due_days' => 'integer',
        'down_payment_applied' => 'decimal:8',
        'down_payment_remaining' => 'decimal:8',
        'is_down_payment_paid_off' => 'boolean',
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

    public function purchaseReturnProductUnits()
    {
        return $this->hasMany(PurchaseReturnProductUnit::class);
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where('purchases.code', 'like', '%'.$search.'%')
            ->orWhere('purchases.date', 'like', '%'.$search.'%')
            ->orWhere('purchases.remarks', 'like', '%'.$search.'%');
    }
}
