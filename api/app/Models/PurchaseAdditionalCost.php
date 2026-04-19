<?php

namespace App\Models;

use App\Traits\BootableModel;
use App\Traits\ScopeableByBranch;
use App\Traits\ScopeableByCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseAdditionalCost extends Model
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
        'purchase_additional_cost_category_id',
        'code',
        'date',
        'due_days',
        'paid_immediately_cash_account_id',
        'amount_paid_immediately',
        'amount_payable',
        'amount_payable_paid',
        'amount_payable_due',
        'is_amount_payable_paid_off',
        'amount_total',
        'remarks',
    ];

    protected $casts = [
        'date' => 'datetime',
        'due_days' => 'integer',
        'amount_paid_immediately' => 'decimal:8',
        'amount_payable' => 'decimal:8',
        'amount_payable_paid' => 'decimal:8',
        'amount_payable_due' => 'decimal:8',
        'is_amount_payable_paid_off' => 'boolean',
        'amount_total' => 'decimal:8',
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

    public function category()
    {
        return $this->belongsTo(PurchaseAdditionalCostCategory::class, 'purchase_additional_cost_category_id')->withTrashed();
    }

    public function paidImmediatelyCashAccount()
    {
        return $this->belongsTo(CashAccount::class, 'paid_immediately_cash_account_id')->withTrashed();
    }

    public function payments()
    {
        return $this->hasMany(PurchaseAdditionalCostPayment::class);
    }

    public function cashTransaction()
    {
        return $this->morphOne(CashTransaction::class, 'referable');
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($query) use ($search) {
            $query->where('purchase_additional_costs.code', 'like', '%'.$search.'%')
                ->orWhere('purchase_additional_costs.remarks', 'like', '%'.$search.'%');
        });
    }
}
