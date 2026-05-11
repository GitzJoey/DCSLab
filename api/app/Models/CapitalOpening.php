<?php

namespace App\Models;

use App\Traits\ScopeableByBranch;
use App\Traits\ScopeableByCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use InvalidArgumentException;

class CapitalOpening extends Model
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
        'amount',
        'remarks',
    ];

    protected $casts = [
        'date' => 'datetime',
        'amount' => 'decimal:8',
    ];

    protected static function booted(): void
    {
        $validateRelations = static function (self $capitalOpening): void {
            $validateCompanyRelation = static function (
                ?int $relationId,
                string $modelClass,
                string $errorMessage
            ) use ($capitalOpening): void {
                if (is_null($relationId)) {
                    return;
                }

                $relation = $modelClass::find($relationId);

                if (! $relation || (int) $relation->company_id !== (int) $capitalOpening->company_id) {
                    throw new InvalidArgumentException($errorMessage);
                }
            };

            $validateCompanyRelation(
                relationId: $capitalOpening->branch_id,
                modelClass: Branch::class,
                errorMessage: 'Capital opening branch must exist in the same company.',
            );

            $validateCompanyRelation(
                relationId: $capitalOpening->investor_id,
                modelClass: Investor::class,
                errorMessage: 'Capital opening investor must exist in the same company.',
            );

            $validateCompanyRelation(
                relationId: $capitalOpening->cash_account_id,
                modelClass: CashAccount::class,
                errorMessage: 'Capital opening cash account must exist in the same company.',
            );
        };

        static::creating(function (self $capitalOpening) use ($validateRelations) {
            $capitalOpening->ulid = Str::ulid()->generate();

            if (auth()->check()) {
                $capitalOpening->created_by = auth()->id();
                $capitalOpening->updated_by = auth()->id();
            }

            $validateRelations($capitalOpening);
        });

        static::updating(function (self $capitalOpening) use ($validateRelations) {
            if (auth()->check()) {
                $capitalOpening->updated_by = auth()->id();
            }

            $validateRelations($capitalOpening);
        });

        static::deleting(function (self $capitalOpening) {
            if (! auth()->check()) {
                return;
            }

            $capitalOpening->deleted_by = auth()->id();
            $capitalOpening->save();
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
