<?php

namespace App\Models;

use App\Traits\ScopeableByBranch;
use App\Traits\ScopeableByCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use InvalidArgumentException;

class PrepaidIncomePayment extends Model
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
        'prepaid_income_id',
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
        $validateRelations = static function (self $prepaidIncomePayment): void {
            $validateCompanyRelation = static function (
                ?int $relationId,
                string $modelClass,
                string $errorMessage
            ) use ($prepaidIncomePayment): void {
                if (is_null($relationId)) {
                    return;
                }

                $relation = $modelClass::find($relationId);

                if (! $relation || (int) $relation->company_id !== (int) $prepaidIncomePayment->company_id) {
                    throw new InvalidArgumentException($errorMessage);
                }
            };

            $validateCompanyRelation(
                relationId: $prepaidIncomePayment->branch_id,
                modelClass: Branch::class,
                errorMessage: 'Prepaid income payment branch must exist in the same company.',
            );

            $validateCompanyRelation(
                relationId: $prepaidIncomePayment->prepaid_income_id,
                modelClass: PrepaidIncome::class,
                errorMessage: 'Prepaid income payment prepaid income must exist in the same company.',
            );

            $validateCompanyRelation(
                relationId: $prepaidIncomePayment->cash_account_id,
                modelClass: CashAccount::class,
                errorMessage: 'Prepaid income payment cash account must exist in the same company.',
            );
        };

        static::creating(function (self $prepaidIncomePayment) use ($validateRelations) {
            $prepaidIncomePayment->ulid = Str::ulid()->generate();

            if (auth()->check()) {
                $prepaidIncomePayment->created_by = auth()->id();
                $prepaidIncomePayment->updated_by = auth()->id();
            }

            $validateRelations($prepaidIncomePayment);
        });

        static::updating(function (self $prepaidIncomePayment) use ($validateRelations) {
            if (auth()->check()) {
                $prepaidIncomePayment->updated_by = auth()->id();
            }

            $validateRelations($prepaidIncomePayment);
        });

        static::deleting(function (self $prepaidIncomePayment) {
            if (! auth()->check()) {
                return;
            }

            $prepaidIncomePayment->deleted_by = auth()->id();
            $prepaidIncomePayment->save();
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

    public function prepaidIncome()
    {
        return $this->belongsTo(PrepaidIncome::class)->withTrashed();
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
