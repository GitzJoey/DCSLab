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

class PurchaseReturn extends Model
{
    use HasFactory;
    use ScopeableByBranch;
    use ScopeableByCompany;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'branch_id',
        'purchase_invoice_id',
        'supplier_id',
        'warehouse_id',
        'code',
        'date',
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
        'amount_allocated_to_invoice',
        'amount_received_total',
        'amount_settled_total',
        'amount_available',
        'is_settled',
    ];

    protected $casts = [
        'date' => 'datetime',
        'is_posted' => 'boolean',
        'item_total_before_global_discount' => 'decimal:8',
        'global_discount' => 'decimal:8',
        'item_total_after_global_discount' => 'decimal:8',
        'vat_base' => 'decimal:8',
        'vat' => 'decimal:8',
        'item_total_after_vat' => 'decimal:8',
        'rounding' => 'decimal:8',
        'amount_payable' => 'decimal:8',
        'amount_allocated_to_invoice' => 'decimal:8',
        'amount_received_total' => 'decimal:8',
        'amount_settled_total' => 'decimal:8',
        'amount_available' => 'decimal:8',
        'is_settled' => 'boolean',
    ];

    protected static function booted(): void
    {
        $validateRelations = static function (self $purchaseReturn): void {
            $validateCompanyRelation = static function (
                ?int $relationId,
                string $modelClass,
                string $errorMessage
            ) use ($purchaseReturn): void {
                if (is_null($relationId)) {
                    return;
                }

                $relation = $modelClass::find($relationId);

                if (! $relation || (int) $relation->company_id !== (int) $purchaseReturn->company_id) {
                    throw new InvalidArgumentException($errorMessage);
                }
            };

            $validateCompanyRelation(
                relationId: $purchaseReturn->branch_id,
                modelClass: Branch::class,
                errorMessage: 'Purchase return branch must exist in the same company.',
            );

            $validateCompanyRelation(
                relationId: $purchaseReturn->purchase_invoice_id,
                modelClass: PurchaseInvoice::class,
                errorMessage: 'Purchase return purchase invoice must exist in the same company.',
            );

            $validateCompanyRelation(
                relationId: $purchaseReturn->supplier_id,
                modelClass: Supplier::class,
                errorMessage: 'Purchase return supplier must exist in the same company.',
            );

            $validateCompanyRelation(
                relationId: $purchaseReturn->warehouse_id,
                modelClass: Warehouse::class,
                errorMessage: 'Purchase return warehouse must exist in the same company.',
            );
        };

        static::creating(function (self $purchaseReturn) use ($validateRelations) {
            $purchaseReturn->ulid = Str::ulid()->generate();

            if (auth()->check()) {
                $purchaseReturn->created_by = auth()->id();
                $purchaseReturn->updated_by = auth()->id();
            }

            $validateRelations($purchaseReturn);
        });

        static::updating(function (self $purchaseReturn) use ($validateRelations) {
            if (auth()->check()) {
                $purchaseReturn->updated_by = auth()->id();
            }

            $validateRelations($purchaseReturn);
        });

        static::deleting(function (self $purchaseReturn) {
            if (! auth()->check()) {
                return;
            }

            $purchaseReturn->deleted_by = auth()->id();
            $purchaseReturn->save();
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

    public function supplier()
    {
        return $this->belongsTo(Supplier::class)->withTrashed();
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class)->withTrashed();
    }

    public function items()
    {
        return $this->hasMany(PurchaseReturnItem::class);
    }

    public function itemSerials()
    {
        return $this->hasMany(PurchaseReturnItemSerial::class);
    }

    public function refunds()
    {
        return $this->hasMany(PurchaseReturnRefund::class);
    }

    public function invoicePayments()
    {
        return $this->hasMany(PurchaseInvoicePayment::class);
    }

    public function journalEntry()
    {
        return $this->morphOne(JournalEntry::class, 'source')
            ->where('journal_type', JournalEntryTypeEnum::TRANSACTION->value);
    }
}
