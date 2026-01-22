<?php

namespace App\Models;

use App\Traits\BootableModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesOrder extends Model
{
    use BootableModel;
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'branch_id',
        'code',
        'date',
        'customer_id',
        'customer_address_id',
        'shipping_date',
        'remarks',
        'is_has_invoice',
        'is_sent',
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
        'date' => 'datetime',
        'shipping_date' => 'datetime',
        'is_has_invoice' => 'boolean',
        'is_sent' => 'boolean',
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
        return $this->belongsTo(Company::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function customerAddress()
    {
        return $this->belongsTo(CustomerAddress::class);
    }

    public function saleOrderProductUnits()
    {
        return $this->hasMany(SaleOrderProductUnit::class);
    }

    public function saleOrderDownPayments()
    {
        return $this->hasMany(SaleOrderDownPayment::class);
    }

    public function saleOrderDownPaymentApplies()
    {
        return $this->hasMany(SaleOrderDownPaymentApply::class);
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where('sales_orders.code', 'like', '%'.$search.'%')
            ->orWhere('sales_orders.date', 'like', '%'.$search.'%')
            ->orWhere('sales_orders.remarks', 'like', '%'.$search.'%');
    }
}
