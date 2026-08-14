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

class PurchaseOrderReceipt extends Model
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
        'supplier_id',
        'purchase_order_id',
        'warehouse_id',
        'remarks',
        'is_posted',
        'total_value',
        'total_cost',
    ];

    protected $casts = [
        'date' => 'datetime',
        'is_posted' => 'boolean',
        'total_value' => 'decimal:8',
        'total_cost' => 'decimal:8',
    ];

    protected static function booted(): void
    {
        $validateRelations = static function (self $purchaseOrderReceipt): void {
            $validateCompanyRelation = static function (
                ?int $relationId,
                string $modelClass,
                string $errorMessage
            ) use ($purchaseOrderReceipt): void {
                if (is_null($relationId)) {
                    return;
                }

                $relation = $modelClass::find($relationId);

                if (! $relation || (int) $relation->company_id !== (int) $purchaseOrderReceipt->company_id) {
                    throw new InvalidArgumentException($errorMessage);
                }
            };

            $validateCompanyRelation(
                relationId: $purchaseOrderReceipt->branch_id,
                modelClass: Branch::class,
                errorMessage: 'Purchase order receipt branch must exist in the same company.',
            );

            $validateCompanyRelation(
                relationId: $purchaseOrderReceipt->supplier_id,
                modelClass: Supplier::class,
                errorMessage: 'Purchase order receipt supplier must exist in the same company.',
            );

            $validateCompanyRelation(
                relationId: $purchaseOrderReceipt->purchase_order_id,
                modelClass: PurchaseOrder::class,
                errorMessage: 'Purchase order receipt purchase order must exist in the same company.',
            );

            $validateCompanyRelation(
                relationId: $purchaseOrderReceipt->warehouse_id,
                modelClass: Warehouse::class,
                errorMessage: 'Purchase order receipt warehouse must exist in the same company.',
            );
        };

        static::creating(function (self $purchaseOrderReceipt) use ($validateRelations) {
            $purchaseOrderReceipt->ulid = Str::ulid()->generate();

            if (auth()->check()) {
                $purchaseOrderReceipt->created_by = auth()->id();
                $purchaseOrderReceipt->updated_by = auth()->id();
            }

            $validateRelations($purchaseOrderReceipt);
        });

        static::updating(function (self $purchaseOrderReceipt) use ($validateRelations) {
            if (auth()->check()) {
                $purchaseOrderReceipt->updated_by = auth()->id();
            }

            $validateRelations($purchaseOrderReceipt);
        });

        static::deleting(function (self $purchaseOrderReceipt) {
            if (! auth()->check()) {
                return;
            }

            $purchaseOrderReceipt->deleted_by = auth()->id();
            $purchaseOrderReceipt->save();
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

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class)->withTrashed();
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderReceiptItem::class);
    }

    public function itemSerials()
    {
        return $this->hasMany(PurchaseOrderReceiptItemSerial::class);
    }

    public function costs()
    {
        return $this->hasMany(PurchaseOrderReceiptCost::class);
    }

    public function journalEntry()
    {
        return $this->morphOne(JournalEntry::class, 'source')
            ->where('journal_type', JournalEntryTypeEnum::TRANSACTION->value);
    }
}
