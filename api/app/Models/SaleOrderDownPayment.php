<?php

namespace App\Models;

use App\Traits\BootableModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SaleOrderDownPayment extends Model
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
        return $this->belongsTo(Company::class)->withTrashed();
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class)->withTrashed();
    }

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class, 'sale_order_id')->withTrashed();
    }

    public function cashAccount()
    {
        return $this->belongsTo(CashAccount::class)->withTrashed();
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($query) use ($search) {
            $query->where('sale_order_down_payments.code', 'like', '%'.$search.'%')
                ->orWhere('sale_order_down_payments.date', 'like', '%'.$search.'%')
                ->orWhere('sale_order_down_payments.remarks', 'like', '%'.$search.'%');
        });
    }
}
