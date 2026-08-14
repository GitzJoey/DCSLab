<?php

namespace App\Models;

use App\Enums\JournalEntryTypeEnum;
use App\Enums\PaymentTypeEnum;
use App\Traits\ScopeableByBranch;
use App\Traits\ScopeableByCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use InvalidArgumentException;

class PurchaseInvoicePayment extends Model
{
    use HasFactory;
    use ScopeableByBranch;
    use ScopeableByCompany;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'branch_id',
        'purchase_invoice_id',
        'code',
        'date',
        'payment_type',
        'cash_account_id',
        'purchase_order_payment_id',
        'purchase_return_id',
        'amount',
        'remarks',
    ];

    protected $casts = [
        'date' => 'datetime',
        'payment_type' => PaymentTypeEnum::class,
        'amount' => 'decimal:8',
    ];

    protected static function booted(): void
    {
        $validateRelations = static function (self $purchaseInvoicePayment): void {
            $validateCompanyRelation = static function (
                ?int $relationId,
                string $modelClass,
                string $errorMessage
            ) use ($purchaseInvoicePayment): void {
                if (is_null($relationId)) {
                    return;
                }

                $relation = $modelClass::find($relationId);

                if (! $relation || (int) $relation->company_id !== (int) $purchaseInvoicePayment->company_id) {
                    throw new InvalidArgumentException($errorMessage);
                }
            };

            $validateCompanyRelation(
                relationId: $purchaseInvoicePayment->branch_id,
                modelClass: Branch::class,
                errorMessage: 'Purchase invoice payment branch must exist in the same company.',
            );

            $validateCompanyRelation(
                relationId: $purchaseInvoicePayment->purchase_invoice_id,
                modelClass: PurchaseInvoice::class,
                errorMessage: 'Purchase invoice payment purchase invoice must exist in the same company.',
            );

            $validateCompanyRelation(
                relationId: $purchaseInvoicePayment->cash_account_id,
                modelClass: CashAccount::class,
                errorMessage: 'Purchase invoice payment cash account must exist in the same company.',
            );

            $validateCompanyRelation(
                relationId: $purchaseInvoicePayment->purchase_order_payment_id,
                modelClass: PurchaseOrderPayment::class,
                errorMessage: 'Purchase invoice payment purchase order payment must exist in the same company.',
            );

            $validateCompanyRelation(
                relationId: $purchaseInvoicePayment->purchase_return_id,
                modelClass: PurchaseReturn::class,
                errorMessage: 'Purchase invoice payment purchase return must exist in the same company.',
            );
        };

        static::creating(function (self $purchaseInvoicePayment) use ($validateRelations) {
            $purchaseInvoicePayment->ulid = Str::ulid()->generate();

            if (auth()->check()) {
                $purchaseInvoicePayment->created_by = auth()->id();
                $purchaseInvoicePayment->updated_by = auth()->id();
            }

            $validateRelations($purchaseInvoicePayment);
        });

        static::updating(function (self $purchaseInvoicePayment) use ($validateRelations) {
            if (auth()->check()) {
                $purchaseInvoicePayment->updated_by = auth()->id();
            }

            $validateRelations($purchaseInvoicePayment);
        });

        static::deleting(function (self $purchaseInvoicePayment) {
            if (! auth()->check()) {
                return;
            }

            $purchaseInvoicePayment->deleted_by = auth()->id();
            $purchaseInvoicePayment->save();
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

    public function purchaseInvoice()
    {
        return $this->belongsTo(PurchaseInvoice::class)->withTrashed();
    }

    public function cashAccount()
    {
        return $this->belongsTo(CashAccount::class)->withTrashed();
    }

    public function purchaseOrderPayment()
    {
        return $this->belongsTo(PurchaseOrderPayment::class)->withTrashed();
    }

    public function purchaseReturn()
    {
        return $this->belongsTo(PurchaseReturn::class)->withTrashed();
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
