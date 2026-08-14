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

class PurchaseInvoice extends Model
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
        'due_days',
        'supplier_id',
        'purchase_order_id',
        'tax_invoice_number',
        'tax_invoice_vat_base',
        'tax_invoice_vat',
        'remarks',
        'is_posted',

        'item_total_before_global_discount',
        'global_discount',
        'item_total_after_global_discount',
        'vat_base',
        'vat',
        'item_total_after_vat',
        'rounding',
        'amount_payable',
        'amount_paid_down_payment',
        'amount_paid_return',
        'amount_paid_total',
        'amount_due',
        'is_paid_off',
    ];

    protected $casts = [
        'date' => 'datetime',
        'due_days' => 'integer',
        'tax_invoice_vat_base' => 'decimal:8',
        'tax_invoice_vat' => 'decimal:8',
        'is_posted' => 'boolean',
        'item_total_before_global_discount' => 'decimal:8',
        'global_discount' => 'decimal:8',
        'item_total_after_global_discount' => 'decimal:8',
        'vat_base' => 'decimal:8',
        'vat' => 'decimal:8',
        'item_total_after_vat' => 'decimal:8',
        'rounding' => 'decimal:8',
        'amount_payable' => 'decimal:8',
        'amount_paid_down_payment' => 'decimal:8',
        'amount_paid_return' => 'decimal:8',
        'amount_paid_total' => 'decimal:8',
        'amount_due' => 'decimal:8',
        'is_paid_off' => 'boolean',
    ];

    protected static function booted(): void
    {
        $validateRelations = static function (self $purchaseInvoice): void {
            $validateCompanyRelation = static function (
                ?int $relationId,
                string $modelClass,
                string $errorMessage
            ) use ($purchaseInvoice): void {
                if (is_null($relationId)) {
                    return;
                }

                $relation = $modelClass::find($relationId);

                if (! $relation || (int) $relation->company_id !== (int) $purchaseInvoice->company_id) {
                    throw new InvalidArgumentException($errorMessage);
                }
            };

            $validateCompanyRelation(
                relationId: $purchaseInvoice->branch_id,
                modelClass: Branch::class,
                errorMessage: 'Purchase invoice branch must exist in the same company.',
            );

            $validateCompanyRelation(
                relationId: $purchaseInvoice->supplier_id,
                modelClass: Supplier::class,
                errorMessage: 'Purchase invoice supplier must exist in the same company.',
            );

            $validateCompanyRelation(
                relationId: $purchaseInvoice->purchase_order_id,
                modelClass: PurchaseOrder::class,
                errorMessage: 'Purchase invoice purchase order must exist in the same company.',
            );
        };

        static::creating(function (self $purchaseInvoice) use ($validateRelations) {
            $purchaseInvoice->ulid = Str::ulid()->generate();

            if (auth()->check()) {
                $purchaseInvoice->created_by = auth()->id();
                $purchaseInvoice->updated_by = auth()->id();
            }

            $validateRelations($purchaseInvoice);
        });

        static::updating(function (self $purchaseInvoice) use ($validateRelations) {
            if (auth()->check()) {
                $purchaseInvoice->updated_by = auth()->id();
            }

            $validateRelations($purchaseInvoice);
        });

        static::deleting(function (self $purchaseInvoice) {
            if (! auth()->check()) {
                return;
            }

            $purchaseInvoice->deleted_by = auth()->id();
            $purchaseInvoice->save();
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

    public function supplier()
    {
        return $this->belongsTo(Supplier::class)->withTrashed();
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class)->withTrashed();
    }

    public function items()
    {
        return $this->hasMany(PurchaseInvoiceItem::class);
    }

    public function payments()
    {
        return $this->hasMany(PurchaseInvoicePayment::class);
    }

    public function returns()
    {
        return $this->hasMany(PurchaseReturn::class);
    }

    public function journalEntry()
    {
        return $this->morphOne(JournalEntry::class, 'source')
            ->where('journal_type', JournalEntryTypeEnum::TRANSACTION->value);
    }
}
