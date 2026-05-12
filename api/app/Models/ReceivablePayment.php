<?php

namespace App\Models;

use App\Traits\ScopeableByBranch;
use App\Traits\ScopeableByCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use InvalidArgumentException;

class ReceivablePayment extends Model
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
        'receivable_id',
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
        $validateRelations = static function (self $receivablePayment): void {
            $validateCompanyRelation = static function (
                ?int $relationId,
                string $modelClass,
                string $errorMessage
            ) use ($receivablePayment): void {
                if (is_null($relationId)) {
                    return;
                }

                $relation = $modelClass::find($relationId);

                if (! $relation || (int) $relation->company_id !== (int) $receivablePayment->company_id) {
                    throw new InvalidArgumentException($errorMessage);
                }
            };

            $validateCompanyRelation(
                relationId: $receivablePayment->branch_id,
                modelClass: Branch::class,
                errorMessage: 'Receivable payment branch must exist in the same company.',
            );

            $validateCompanyRelation(
                relationId: $receivablePayment->receivable_id,
                modelClass: Receivable::class,
                errorMessage: 'Receivable payment receivable must exist in the same company.',
            );

            $validateCompanyRelation(
                relationId: $receivablePayment->cash_account_id,
                modelClass: CashAccount::class,
                errorMessage: 'Receivable payment cash account must exist in the same company.',
            );
        };

        static::creating(function (self $receivablePayment) use ($validateRelations) {
            $receivablePayment->ulid = Str::ulid()->generate();

            if (auth()->check()) {
                $receivablePayment->created_by = auth()->id();
                $receivablePayment->updated_by = auth()->id();
            }

            $validateRelations($receivablePayment);
        });

        static::updating(function (self $receivablePayment) use ($validateRelations) {
            if (auth()->check()) {
                $receivablePayment->updated_by = auth()->id();
            }

            $validateRelations($receivablePayment);
        });

        static::deleting(function (self $receivablePayment) {
            if (! auth()->check()) {
                return;
            }

            $receivablePayment->deleted_by = auth()->id();
            $receivablePayment->save();
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

    public function receivable()
    {
        return $this->belongsTo(Receivable::class)->withTrashed();
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
