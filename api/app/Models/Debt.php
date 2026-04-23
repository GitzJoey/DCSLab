<?php

namespace App\Models;

use App\Traits\BootableModel;
use App\Traits\ScopeableByBranch;
use App\Traits\ScopeableByCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Debt extends Model
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
        'category_id',
        'creditor_id',
        'supplier_id',
        'cash_account_id',
        'direct_amount_received',
        'opening_amount_due',
        'amount_total',
        'amount_paid_by_cash_account',
        'amount_paid_by_stock_adjustment',
        'amount_due',
        'due_days',
        'is_paid_off',
        'remarks',
    ];

    protected $casts = [
        'date' => 'datetime',
        'direct_amount_received' => 'decimal:8',
        'opening_amount_due' => 'decimal:8',
        'amount_total' => 'decimal:8',
        'amount_paid_by_cash_account' => 'decimal:8',
        'amount_paid_by_stock_adjustment' => 'decimal:8',
        'amount_due' => 'decimal:8',
        'due_days' => 'integer',
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

    public function category()
    {
        return $this->belongsTo(DebtCategory::class, 'category_id')->withTrashed();
    }

    public function creditor()
    {
        return $this->belongsTo(DebtCreditor::class)->withTrashed();
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class)->withTrashed();
    }

    public function cashAccount()
    {
        return $this->belongsTo(CashAccount::class)->withTrashed();
    }

    public function payments()
    {
        return $this->hasMany(DebtPayment::class);
    }

    public function cashTransaction()
    {
        return $this->morphOne(CashTransaction::class, 'referable');
    }
}
