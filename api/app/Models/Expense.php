<?php

namespace App\Models;

use App\Traits\ScopeableByBranch;
use App\Traits\ScopeableByCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use InvalidArgumentException;

class Expense extends Model
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
        'expense_category_id',
        'paid_immediately_cash_account_id',
        'amount_paid_immediately',
        'amount_payable',
        'due_days',
        'amount_payable_paid',
        'amount_payable_due',
        'is_amount_payable_paid_off',
        'amount_total',
        'remarks',
    ];

    protected $casts = [
        'date' => 'datetime',
        'amount_paid_immediately' => 'decimal:8',
        'amount_payable' => 'decimal:8',
        'due_days' => 'integer',
        'amount_payable_paid' => 'decimal:8',
        'amount_payable_due' => 'decimal:8',
        'is_amount_payable_paid_off' => 'boolean',
        'amount_total' => 'decimal:8',
    ];

    protected static function booted(): void
    {
        $validateRelations = static function (self $expense): void {
            $validateCompanyRelation = static function (
                ?int $relationId,
                string $modelClass,
                string $errorMessage
            ) use ($expense): void {
                if (is_null($relationId)) {
                    return;
                }

                $relation = $modelClass::find($relationId);

                if (! $relation || (int) $relation->company_id !== (int) $expense->company_id) {
                    throw new InvalidArgumentException($errorMessage);
                }
            };

            $validateCompanyRelation(
                relationId: $expense->branch_id,
                modelClass: Branch::class,
                errorMessage: 'Expense branch must exist in the same company.',
            );

            $validateCompanyRelation(
                relationId: $expense->expense_category_id,
                modelClass: ExpenseCategory::class,
                errorMessage: 'Expense category must exist in the same company.',
            );

            $validateCompanyRelation(
                relationId: $expense->paid_immediately_cash_account_id,
                modelClass: CashAccount::class,
                errorMessage: 'Paid immediately cash account must exist in the same company.',
            );
        };

        static::creating(function (self $expense) use ($validateRelations) {
            $expense->ulid = Str::ulid()->generate();

            if (auth()->check()) {
                $expense->created_by = auth()->id();
                $expense->updated_by = auth()->id();
            }

            $validateRelations($expense);
        });

        static::updating(function (self $expense) use ($validateRelations) {
            if (auth()->check()) {
                $expense->updated_by = auth()->id();
            }

            $validateRelations($expense);
        });

        static::deleting(function (self $expense) {
            if (! auth()->check()) {
                return;
            }

            $expense->deleted_by = auth()->id();
            $expense->save();
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

    public function category()
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id')->withTrashed();
    }

    public function paidImmediatelyCashAccount()
    {
        return $this->belongsTo(CashAccount::class, 'paid_immediately_cash_account_id')->withTrashed();
    }

    public function payments()
    {
        return $this->hasMany(ExpensePayment::class);
    }

    public function images()
    {
        return $this->hasMany(ExpenseImage::class);
    }

    public function mainImage()
    {
        return $this->hasOne(ExpenseImage::class)
            ->orderByDesc('is_main')
            ->orderBy('id');
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
