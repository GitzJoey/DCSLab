<?php

namespace App\Models;

use App\Traits\ScopeableByBranch;
use App\Traits\ScopeableByCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use InvalidArgumentException;

class PrepaidExpensePayment extends Model
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
        'prepaid_expense_id',
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
        $validateRelations = static function (self $prepaidExpensePayment): void {
            $validateCompanyRelation = static function (
                ?int $relationId,
                string $modelClass,
                string $errorMessage
            ) use ($prepaidExpensePayment): void {
                if (is_null($relationId)) {
                    return;
                }

                $relation = $modelClass::find($relationId);

                if (! $relation || (int) $relation->company_id !== (int) $prepaidExpensePayment->company_id) {
                    throw new InvalidArgumentException($errorMessage);
                }
            };

            $validateCompanyRelation(
                relationId: $prepaidExpensePayment->branch_id,
                modelClass: Branch::class,
                errorMessage: 'Prepaid expense payment branch must exist in the same company.',
            );

            $validateCompanyRelation(
                relationId: $prepaidExpensePayment->prepaid_expense_id,
                modelClass: PrepaidExpense::class,
                errorMessage: 'Prepaid expense payment prepaid expense must exist in the same company.',
            );

            $validateCompanyRelation(
                relationId: $prepaidExpensePayment->cash_account_id,
                modelClass: CashAccount::class,
                errorMessage: 'Prepaid expense payment cash account must exist in the same company.',
            );
        };

        static::creating(function (self $prepaidExpensePayment) use ($validateRelations) {
            $prepaidExpensePayment->ulid = Str::ulid()->generate();

            if (auth()->check()) {
                $prepaidExpensePayment->created_by = auth()->id();
                $prepaidExpensePayment->updated_by = auth()->id();
            }

            $validateRelations($prepaidExpensePayment);
        });

        static::updating(function (self $prepaidExpensePayment) use ($validateRelations) {
            if (auth()->check()) {
                $prepaidExpensePayment->updated_by = auth()->id();
            }

            $validateRelations($prepaidExpensePayment);
        });

        static::deleting(function (self $prepaidExpensePayment) {
            if (! auth()->check()) {
                return;
            }

            $prepaidExpensePayment->deleted_by = auth()->id();
            $prepaidExpensePayment->save();
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

    public function prepaidExpense()
    {
        return $this->belongsTo(PrepaidExpense::class)->withTrashed();
    }

    public function cashAccount()
    {
        return $this->belongsTo(CashAccount::class)->withTrashed();
    }

    public function cashTransaction()
    {
        return $this->morphOne(CashTransaction::class, 'referable');
    }
}
