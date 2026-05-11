<?php

namespace App\Models;

use App\Traits\ScopeableByBranch;
use App\Traits\ScopeableByCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use InvalidArgumentException;

class DebtPayment extends Model
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
        'debt_id',
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
        $validateRelations = static function (self $debtPayment): void {
            $validateCompanyRelation = static function (
                ?int $relationId,
                string $modelClass,
                string $errorMessage
            ) use ($debtPayment): void {
                if (is_null($relationId)) {
                    return;
                }

                $relation = $modelClass::find($relationId);

                if (! $relation || (int) $relation->company_id !== (int) $debtPayment->company_id) {
                    throw new InvalidArgumentException($errorMessage);
                }
            };

            $validateCompanyRelation(
                relationId: $debtPayment->branch_id,
                modelClass: Branch::class,
                errorMessage: 'Debt payment branch must exist in the same company.',
            );

            $validateCompanyRelation(
                relationId: $debtPayment->debt_id,
                modelClass: Debt::class,
                errorMessage: 'Debt payment debt must exist in the same company.',
            );

            $validateCompanyRelation(
                relationId: $debtPayment->cash_account_id,
                modelClass: CashAccount::class,
                errorMessage: 'Debt payment cash account must exist in the same company.',
            );
        };

        static::creating(function (self $debtPayment) use ($validateRelations) {
            $debtPayment->ulid = Str::ulid()->generate();

            if (auth()->check()) {
                $debtPayment->created_by = auth()->id();
                $debtPayment->updated_by = auth()->id();
            }

            $validateRelations($debtPayment);
        });

        static::updating(function (self $debtPayment) use ($validateRelations) {
            if (auth()->check()) {
                $debtPayment->updated_by = auth()->id();
            }

            $validateRelations($debtPayment);
        });

        static::deleting(function (self $debtPayment) {
            if (! auth()->check()) {
                return;
            }

            $debtPayment->deleted_by = auth()->id();
            $debtPayment->save();
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

    public function debt()
    {
        return $this->belongsTo(Debt::class)->withTrashed();
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
