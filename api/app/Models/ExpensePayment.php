<?php

namespace App\Models;

use App\Traits\ScopeableByBranch;
use App\Traits\ScopeableByCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use InvalidArgumentException;

class ExpensePayment extends Model
{
    use HasFactory;
    use ScopeableByBranch;
    use ScopeableByCompany;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'branch_id',
        'code',
        'date',
        'expense_id',
        'cash_account_id',
        'amount',
        'remarks',
    ];

    protected $casts = [
        'date' => 'datetime',
        'amount' => 'decimal:8',
    ];

    protected static function booted(): void
    {
        $validateRelations = static function (self $expensePayment): void {
            $validateCompanyRelation = static function (
                ?int $relationId,
                string $modelClass,
                string $errorMessage
            ) use ($expensePayment): void {
                if (is_null($relationId)) {
                    return;
                }

                $relation = $modelClass::find($relationId);

                if (! $relation || (int) $relation->company_id !== (int) $expensePayment->company_id) {
                    throw new InvalidArgumentException($errorMessage);
                }
            };

            $validateCompanyRelation(
                relationId: $expensePayment->branch_id,
                modelClass: Branch::class,
                errorMessage: 'Expense payment branch must exist in the same company.',
            );

            $validateCompanyRelation(
                relationId: $expensePayment->expense_id,
                modelClass: Expense::class,
                errorMessage: 'Expense payment expense must exist in the same company.',
            );

            $validateCompanyRelation(
                relationId: $expensePayment->cash_account_id,
                modelClass: CashAccount::class,
                errorMessage: 'Expense payment cash account must exist in the same company.',
            );
        };

        static::creating(function (self $expensePayment) use ($validateRelations) {
            $expensePayment->ulid = Str::ulid()->generate();

            if (auth()->check()) {
                $expensePayment->created_by = auth()->id();
                $expensePayment->updated_by = auth()->id();
            }

            $validateRelations($expensePayment);
        });

        static::updating(function (self $expensePayment) use ($validateRelations) {
            if (auth()->check()) {
                $expensePayment->updated_by = auth()->id();
            }

            $validateRelations($expensePayment);
        });

        static::deleting(function (self $expensePayment) {
            if (! auth()->check()) {
                return;
            }

            $expensePayment->deleted_by = auth()->id();
            $expensePayment->save();
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

    public function expense()
    {
        return $this->belongsTo(Expense::class)->withTrashed();
    }

    public function cashAccount()
    {
        return $this->belongsTo(CashAccount::class)->withTrashed();
    }

    public function cashTransaction()
    {
        return $this->morphOne(CashTransaction::class, 'referable');
    }

    public function journalEntry()
    {
        return $this->morphOne(JournalEntry::class, 'source');
    }
}
