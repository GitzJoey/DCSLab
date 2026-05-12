<?php

namespace App\Models;

use App\Traits\ScopeableByCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class CashAccount extends Model
{
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

    protected static function booted(): void
    {
        $validateRelations = static function (self $cashAccount): void {
            if (is_null($cashAccount->branch_id)) {
                return;
            }

            $branch = Branch::find($cashAccount->branch_id);

            if (! $branch || (int) $branch->company_id !== (int) $cashAccount->company_id) {
                throw new InvalidArgumentException('Cash account branch must exist in the same company.');
            }
        };

        static::creating(function (self $cashAccount) use ($validateRelations) {
            $cashAccount->ulid = Str::ulid()->generate();

            if (auth()->check()) {
                $cashAccount->created_by = auth()->id();
                $cashAccount->updated_by = auth()->id();
            }

            $validateRelations($cashAccount);
        });

        static::updating(function (self $cashAccount) use ($validateRelations) {
            if (auth()->check()) {
                $cashAccount->updated_by = auth()->id();
            }

            $validateRelations($cashAccount);
        });

        static::deleting(function (self $cashAccount) {
            if (! auth()->check()) {
                return;
            }

            $cashAccount->deleted_by = auth()->id();
            $cashAccount->save();
        });
    }

    public function company()
    {
        return $this->belongsTo(Company::class)->withTrashed();
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class)->withTrashed();
    }

    public function chartOfAccount()
    {
        return $this->morphOne(ChartOfAccount::class, 'source');
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
