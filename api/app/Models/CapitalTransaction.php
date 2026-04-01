<?php

namespace App\Models;

use App\Enums\CapitalTransactionTypeEnum;
use App\Traits\BootableModel;
use App\Traits\ScopeableByBranch;
use App\Traits\ScopeableByCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CapitalTransaction extends Model
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
        'investor_id',
        'cash_account_id',
        'type',
        'amount',
        'remarks',
    ];

    protected $casts = [
        'date' => 'datetime',
        'type' => CapitalTransactionTypeEnum::class,
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

    public function investor()
    {
        return $this->belongsTo(Investor::class)->withTrashed();
    }

    public function cashAccount()
    {
        return $this->belongsTo(CashAccount::class)->withTrashed();
    }

    public function cashTransaction()
    {
        return $this->morphOne(CashTransaction::class, 'referable');
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($query) use ($search) {
            $query->where('capital_transactions.code', 'like', '%'.$search.'%')
                ->orWhere('capital_transactions.type', 'like', '%'.$search.'%')
                ->orWhere('capital_transactions.remarks', 'like', '%'.$search.'%');
        });
    }
}
