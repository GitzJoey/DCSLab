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

class PurchaseOrderReceiptCost extends Model
{
    use HasFactory;
    use ScopeableByBranch;
    use ScopeableByCompany;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'branch_id',
        'purchase_order_receipt_id',
        'code',
        'date',
        'name',
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
        $validateRelations = static function (self $purchaseOrderReceiptCost): void {
            $validateCompanyRelation = static function (
                ?int $relationId,
                string $modelClass,
                string $errorMessage
            ) use ($purchaseOrderReceiptCost): void {
                if (is_null($relationId)) {
                    return;
                }

                $relation = $modelClass::find($relationId);

                if (! $relation || (int) $relation->company_id !== (int) $purchaseOrderReceiptCost->company_id) {
                    throw new InvalidArgumentException($errorMessage);
                }
            };

            $validateCompanyRelation(
                relationId: $purchaseOrderReceiptCost->branch_id,
                modelClass: Branch::class,
                errorMessage: 'Purchase order receipt cost branch must exist in the same company.',
            );

            $validateCompanyRelation(
                relationId: $purchaseOrderReceiptCost->purchase_order_receipt_id,
                modelClass: PurchaseOrderReceipt::class,
                errorMessage: 'Purchase order receipt cost purchase order receipt must exist in the same company.',
            );

            $validateCompanyRelation(
                relationId: $purchaseOrderReceiptCost->cash_account_id,
                modelClass: CashAccount::class,
                errorMessage: 'Purchase order receipt cost cash account must exist in the same company.',
            );
        };

        static::creating(function (self $purchaseOrderReceiptCost) use ($validateRelations) {
            $purchaseOrderReceiptCost->ulid = Str::ulid()->generate();

            if (auth()->check()) {
                $purchaseOrderReceiptCost->created_by = auth()->id();
                $purchaseOrderReceiptCost->updated_by = auth()->id();
            }

            $validateRelations($purchaseOrderReceiptCost);
        });

        static::updating(function (self $purchaseOrderReceiptCost) use ($validateRelations) {
            if (auth()->check()) {
                $purchaseOrderReceiptCost->updated_by = auth()->id();
            }

            $validateRelations($purchaseOrderReceiptCost);
        });

        static::deleting(function (self $purchaseOrderReceiptCost) {
            if (! auth()->check()) {
                return;
            }

            $purchaseOrderReceiptCost->deleted_by = auth()->id();
            $purchaseOrderReceiptCost->save();
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

    public function purchaseOrderReceipt()
    {
        return $this->belongsTo(PurchaseOrderReceipt::class)->withTrashed();
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
