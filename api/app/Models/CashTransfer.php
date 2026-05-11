<?php

namespace App\Models;

use App\Traits\ScopeableByBranch;
use App\Traits\ScopeableByCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use InvalidArgumentException;

class CashTransfer extends Model
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
        'source_cash_account_id',
        'destination_cash_account_id',
        'amount',
        'remarks',
    ];

    protected $casts = [
        'date' => 'datetime',
        'amount' => 'decimal:8',
    ];

    protected static function booted(): void
    {
        $validateRelations = static function (self $cashTransfer): void {
            $validateCompanyRelation = static function (
                ?int $relationId,
                string $modelClass,
                string $errorMessage
            ) use ($cashTransfer): void {
                if (is_null($relationId)) {
                    return;
                }

                $relation = $modelClass::find($relationId);

                if (! $relation || (int) $relation->company_id !== (int) $cashTransfer->company_id) {
                    throw new InvalidArgumentException($errorMessage);
                }
            };

            $validateCompanyRelation(
                relationId: $cashTransfer->branch_id,
                modelClass: Branch::class,
                errorMessage: 'Cash transfer branch must exist in the same company.',
            );

            $validateCompanyRelation(
                relationId: $cashTransfer->source_cash_account_id,
                modelClass: CashAccount::class,
                errorMessage: 'Cash transfer source cash account must exist in the same company.',
            );

            $validateCompanyRelation(
                relationId: $cashTransfer->destination_cash_account_id,
                modelClass: CashAccount::class,
                errorMessage: 'Cash transfer destination cash account must exist in the same company.',
            );
        };

        static::creating(function (self $cashTransfer) use ($validateRelations) {
            $cashTransfer->ulid = Str::ulid()->generate();

            if (auth()->check()) {
                $cashTransfer->created_by = auth()->id();
                $cashTransfer->updated_by = auth()->id();
            }

            $validateRelations($cashTransfer);
        });

        static::updating(function (self $cashTransfer) use ($validateRelations) {
            if (auth()->check()) {
                $cashTransfer->updated_by = auth()->id();
            }

            $validateRelations($cashTransfer);
        });

        static::deleting(function (self $cashTransfer) {
            if (! auth()->check()) {
                return;
            }

            $cashTransfer->deleted_by = auth()->id();
            $cashTransfer->save();
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

    public function sourceCashAccount()
    {
        return $this->belongsTo(CashAccount::class, 'source_cash_account_id')->withTrashed();
    }

    public function destinationCashAccount()
    {
        return $this->belongsTo(CashAccount::class, 'destination_cash_account_id')->withTrashed();
    }

    public function sourceCashTransaction()
    {
        return $this->morphOne(CashTransaction::class, 'referable')
            ->where('cash_account_id', $this->source_cash_account_id);
    }

    public function destinationCashTransaction()
    {
        return $this->morphOne(CashTransaction::class, 'referable')
            ->where('cash_account_id', $this->destination_cash_account_id);
    }
}
