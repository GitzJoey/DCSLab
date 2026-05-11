<?php

namespace App\Models;

use App\Traits\ScopeableByBranch;
use App\Traits\ScopeableByCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use InvalidArgumentException;

class IncomePayment extends Model
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
        'income_id',
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
        $validateRelations = static function (self $incomePayment): void {
            $validateCompanyRelation = static function (
                ?int $relationId,
                string $modelClass,
                string $errorMessage
            ) use ($incomePayment): void {
                if (is_null($relationId)) {
                    return;
                }

                $relation = $modelClass::find($relationId);

                if (! $relation || (int) $relation->company_id !== (int) $incomePayment->company_id) {
                    throw new InvalidArgumentException($errorMessage);
                }
            };

            $validateCompanyRelation(
                relationId: $incomePayment->branch_id,
                modelClass: Branch::class,
                errorMessage: 'Income payment branch must exist in the same company.',
            );

            $validateCompanyRelation(
                relationId: $incomePayment->income_id,
                modelClass: Income::class,
                errorMessage: 'Income payment income must exist in the same company.',
            );

            $validateCompanyRelation(
                relationId: $incomePayment->cash_account_id,
                modelClass: CashAccount::class,
                errorMessage: 'Income payment cash account must exist in the same company.',
            );
        };

        static::creating(function (self $incomePayment) use ($validateRelations) {
            $incomePayment->ulid = Str::ulid()->generate();

            if (auth()->check()) {
                $incomePayment->created_by = auth()->id();
                $incomePayment->updated_by = auth()->id();
            }

            $validateRelations($incomePayment);
        });

        static::updating(function (self $incomePayment) use ($validateRelations) {
            if (auth()->check()) {
                $incomePayment->updated_by = auth()->id();
            }

            $validateRelations($incomePayment);
        });

        static::deleting(function (self $incomePayment) {
            if (! auth()->check()) {
                return;
            }

            $incomePayment->deleted_by = auth()->id();
            $incomePayment->save();
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

    public function income()
    {
        return $this->belongsTo(Income::class)->withTrashed();
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
