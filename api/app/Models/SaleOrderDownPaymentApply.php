<?php

namespace App\Models;

use App\Traits\BootableModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SaleOrderDownPaymentApply extends Model
{
    use BootableModel;
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'branch_id',
        'sale_order_id',
        'code',
        'date',
        'cash_account_id',
        'amount',
        'remarks',
    ];

    protected $casts = [
        'date' => 'datetime',
        'amount' => 'decimal:8',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function saleOrder()
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function cashAccount()
    {
        return $this->belongsTo(CashAccount::class);
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where('sale_order_down_payment_applies.code', 'like', '%'.$search.'%')
            ->orWhere('sale_order_down_payment_applies.date', 'like', '%'.$search.'%')
            ->orWhere('sale_order_down_payment_applies.remarks', 'like', '%'.$search.'%');
    }
}
