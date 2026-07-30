<?php

namespace App\Models;

use App\Enums\JournalEntryTypeEnum;
use App\Traits\ScopeableByBranch;
use App\Traits\ScopeableByCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use InvalidArgumentException;

class PurchaseReturnRefund extends Model
{
    use HasFactory;
    use ScopeableByBranch;
    use ScopeableByCompany;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'branch_id',
        'purchase_return_id',
        'code',
        'date',
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
        $validateRelations = static function (self $purchaseReturnRefund): void {
            $validateCompanyRelation = static function (
                ?int $relationId,
                string $modelClass,
                string $errorMessage
            ) use ($purchaseReturnRefund): void {
                if (is_null($relationId)) {
                    return;
                }

                $relation = $modelClass::find($relationId);

                if (! $relation || (int) $relation->company_id !== (int) $purchaseReturnRefund->company_id) {
                    throw new InvalidArgumentException($errorMessage);
                }
            };

            $validateCompanyRelation(
                relationId: $purchaseReturnRefund->branch_id,
                modelClass: Branch::class,
                errorMessage: 'Purchase return refund branch must exist in the same company.',
            );

            $validateCompanyRelation(
                relationId: $purchaseReturnRefund->purchase_return_id,
                modelClass: PurchaseReturn::class,
                errorMessage: 'Purchase return refund purchase return must exist in the same company.',
            );

            $validateCompanyRelation(
                relationId: $purchaseReturnRefund->cash_account_id,
                modelClass: CashAccount::class,
                errorMessage: 'Purchase return refund cash account must exist in the same company.',
            );
        };

        static::creating(function (self $purchaseReturnRefund) use ($validateRelations) {
            $purchaseReturnRefund->ulid = Str::ulid()->generate();

            if (auth()->check()) {
                $purchaseReturnRefund->created_by = auth()->id();
                $purchaseReturnRefund->updated_by = auth()->id();
            }

            $validateRelations($purchaseReturnRefund);
        });

        static::updating(function (self $purchaseReturnRefund) use ($validateRelations) {
            if (auth()->check()) {
                $purchaseReturnRefund->updated_by = auth()->id();
            }

            $validateRelations($purchaseReturnRefund);
        });

        static::deleting(function (self $purchaseReturnRefund) {
            if (! auth()->check()) {
                return;
            }

            $purchaseReturnRefund->deleted_by = auth()->id();
            $purchaseReturnRefund->save();
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

    public function purchaseReturn()
    {
        return $this->belongsTo(PurchaseReturn::class)->withTrashed();
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
        return $this->morphOne(JournalEntry::class, 'source')
            ->where('journal_type', JournalEntryTypeEnum::TRANSACTION->value);
    }
}
