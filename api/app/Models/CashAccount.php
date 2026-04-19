<?php

namespace App\Models;

use App\Traits\BootableModel;
use App\Traits\ScopeableByCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class CashAccount extends Model
{
    use BootableModel;
    use HasFactory;
    use ScopeableByCompany;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'branch_id',
        'code',
        'name',
        'is_bank',
        'is_active',
        'remarks',
    ];

    protected $casts = [
        'is_bank' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class)->withTrashed();
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class)->withTrashed();
    }

    public function scopeWithRemainingBalance($query, ?string $endDate)
    {
        $cashAccountsWithRemainingBalanceQuery = CashTransaction::select(
            'cash_transactions.cash_account_id',
            DB::raw('SUM(cash_transactions.amount) AS remaining_balance')
        );

        if ($endDate) {
            $cashAccountsWithRemainingBalanceQuery->where('cash_transactions.date', '<=', $endDate);
        }

        $cashAccountsWithRemainingBalanceQuery->groupBy('cash_transactions.cash_account_id');

        $query->leftJoinSub($cashAccountsWithRemainingBalanceQuery, 'cash_accounts_with_remaining_balance', function ($join) {
            $join->on('cash_accounts.id', '=', 'cash_accounts_with_remaining_balance.cash_account_id');
        });

        $query->addSelect(
            DB::raw('COALESCE(cash_accounts_with_remaining_balance.remaining_balance, 0) AS remaining_balance')
        );

        return $query;
    }
}
