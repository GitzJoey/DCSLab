<?php

namespace App\Models;

use App\Traits\BootableModel;
use App\Traits\ScopeableByBranch;
use App\Traits\ScopeableByCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PrepaidIncome extends Model
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
        'income_category_id',
        'estimated_useful_life',
        'paid_immediately_cash_account_id',
        'amount_paid_immediately',
        'amount_receivable',
        'due_days',
        'amount_receivable_paid',
        'amount_receivable_due',
        'is_amount_receivable_paid_off',
        'amount_total',
        'remarks',
    ];

    protected $casts = [
        'date' => 'datetime',
        'estimated_useful_life' => 'integer',
        'amount_paid_immediately' => 'decimal:8',
        'amount_receivable' => 'decimal:8',
        'due_days' => 'integer',
        'amount_receivable_paid' => 'decimal:8',
        'amount_receivable_due' => 'decimal:8',
        'is_amount_receivable_paid_off' => 'boolean',
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

    public function category()
    {
        return $this->belongsTo(IncomeCategory::class, 'income_category_id')->withTrashed();
    }

    public function paidImmediatelyCashAccount()
    {
        return $this->belongsTo(CashAccount::class, 'paid_immediately_cash_account_id')->withTrashed();
    }

    public function payments()
    {
        return $this->hasMany(PrepaidIncomePayment::class);
    }

    public function images()
    {
        return $this->hasMany(PrepaidIncomeImage::class);
    }

    public function mainImage()
    {
        return $this->hasOne(PrepaidIncomeImage::class)
            ->orderByDesc('is_main')
            ->orderBy('id');
    }

    public function cashTransaction()
    {
        return $this->morphOne(CashTransaction::class, 'referable');
    }
}
