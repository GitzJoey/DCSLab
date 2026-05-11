<?php

namespace App\Models;

use App\Enums\CapitalTransactionTypeEnum;
use App\Traits\ScopeableByBranch;
use App\Traits\ScopeableByCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use InvalidArgumentException;

class CapitalTransaction extends Model
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

    protected static function booted(): void
    {
        $validateRelations = static function (self $capitalTransaction): void {
            $validateCompanyRelation = static function (
                ?int $relationId,
                string $modelClass,
                string $errorMessage
            ) use ($capitalTransaction): void {
                if (is_null($relationId)) {
                    return;
                }

                $relation = $modelClass::find($relationId);

                if (! $relation || (int) $relation->company_id !== (int) $capitalTransaction->company_id) {
                    throw new InvalidArgumentException($errorMessage);
                }
            };

            $validateCompanyRelation(
                relationId: $capitalTransaction->branch_id,
                modelClass: Branch::class,
                errorMessage: 'Capital transaction branch must exist in the same company.',
            );

            $validateCompanyRelation(
                relationId: $capitalTransaction->investor_id,
                modelClass: Investor::class,
                errorMessage: 'Capital transaction investor must exist in the same company.',
            );

            $validateCompanyRelation(
                relationId: $capitalTransaction->cash_account_id,
                modelClass: CashAccount::class,
                errorMessage: 'Capital transaction cash account must exist in the same company.',
            );
        };

        static::creating(function (self $capitalTransaction) use ($validateRelations) {
            $capitalTransaction->ulid = Str::ulid()->generate();

            if (auth()->check()) {
                $capitalTransaction->created_by = auth()->id();
                $capitalTransaction->updated_by = auth()->id();
            }

            $validateRelations($capitalTransaction);
        });

        static::updating(function (self $capitalTransaction) use ($validateRelations) {
            if (auth()->check()) {
                $capitalTransaction->updated_by = auth()->id();
            }

            $validateRelations($capitalTransaction);
        });

        static::deleting(function (self $capitalTransaction) {
            if (! auth()->check()) {
                return;
            }

            $capitalTransaction->deleted_by = auth()->id();
            $capitalTransaction->save();
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
}
