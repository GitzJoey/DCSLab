<?php

namespace App\Models;

use App\Enums\PurchaseProgressStatusEnum;
use App\Enums\PurchaseReceiptModeEnum;
use App\Traits\ScopeableByBranch;
use App\Traits\ScopeableByCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use InvalidArgumentException;

class Purchase extends Model
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
        'receipt_mode',
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
        'additional_cost',
        'rounding',
        'amount_payable',
        'amount_paid_by_purchase_order_down_payment',
        'amount_paid_by_purchase_return',
        'amount_paid_total',
        'amount_due',
        'is_paid_off',

        'progress_status',
        'item_total_count',
        'item_matched_count',
        'item_less_count',
        'item_more_count',
        'item_unlinked_count',
    ];

    protected $casts = [
        'date' => 'datetime',
        'due_days' => 'integer',
        'receipt_mode' => PurchaseReceiptModeEnum::class,
        'tax_invoice_vat_base' => 'decimal:8',
        'tax_invoice_vat' => 'decimal:8',
        'is_posted' => 'boolean',
        'item_total_before_global_discount' => 'decimal:8',
        'global_discount' => 'decimal:8',
        'item_total_after_global_discount' => 'decimal:8',
        'vat_base' => 'decimal:8',
        'vat' => 'decimal:8',
        'item_total_after_vat' => 'decimal:8',
        'additional_cost' => 'decimal:8',
        'rounding' => 'decimal:8',
        'amount_payable' => 'decimal:8',
        'amount_paid_by_purchase_order_down_payment' => 'decimal:8',
        'amount_paid_by_purchase_return' => 'decimal:8',
        'amount_paid_total' => 'decimal:8',
        'amount_due' => 'decimal:8',
        'is_paid_off' => 'boolean',
        'progress_status' => PurchaseProgressStatusEnum::class,
        'item_total_count' => 'integer',
        'item_matched_count' => 'integer',
        'item_less_count' => 'integer',
        'item_more_count' => 'integer',
        'item_unlinked_count' => 'integer',
    ];

    protected static function booted(): void
    {
        $validateRelations = static function (self $purchase): void {
            $validateCompanyRelation = static function (
                ?int $relationId,
                string $modelClass,
                string $errorMessage
            ) use ($purchase): void {
                if (is_null($relationId)) {
                    return;
                }

                $relation = $modelClass::find($relationId);

                if (! $relation || (int) $relation->company_id !== (int) $purchase->company_id) {
                    throw new InvalidArgumentException($errorMessage);
                }
            };

            $validateCompanyRelation(
                relationId: $purchase->branch_id,
                modelClass: Branch::class,
                errorMessage: 'Purchase branch must exist in the same company.',
            );

            $validateCompanyRelation(
                relationId: $purchase->supplier_id,
                modelClass: Supplier::class,
                errorMessage: 'Purchase supplier must exist in the same company.',
            );

            $validateCompanyRelation(
                relationId: $purchase->purchase_order_id,
                modelClass: PurchaseOrder::class,
                errorMessage: 'Purchase order must exist in the same company.',
            );
        };

        static::creating(function (self $purchase) use ($validateRelations) {
            $purchase->ulid = Str::ulid()->generate();

            if (auth()->check()) {
                $purchase->created_by = auth()->id();
                $purchase->updated_by = auth()->id();
            }

            $validateRelations($purchase);
        });

        static::updating(function (self $purchase) use ($validateRelations) {
            if (auth()->check()) {
                $purchase->updated_by = auth()->id();
            }

            $validateRelations($purchase);
        });

        static::deleting(function (self $purchase) {
            if (! auth()->check()) {
                return;
            }

            $purchase->deleted_by = auth()->id();
            $purchase->save();
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
        return $this->hasMany(PurchaseItem::class);
    }

    public function receipts()
    {
        return $this->hasMany(PurchaseReceipt::class);
    }

    public function directReceipt()
    {
        return $this->hasOne(PurchaseReceipt::class)->where('is_from_direct_purchase', true);
    }

    public function manualReceipts()
    {
        return $this->hasMany(PurchaseReceipt::class)->where('is_from_direct_purchase', false);
    }

    public function receiptItems(): HasManyThrough
    {
        return $this->hasManyThrough(
            PurchaseReceiptItem::class,
            PurchaseReceipt::class,
            'purchase_id',
            'purchase_receipt_id',
            'id',
            'id'
        );
    }

    public function payments()
    {
        return $this->hasMany(PurchasePayment::class);
    }

    public function additionalCosts()
    {
        return $this->hasMany(PurchaseAdditionalCost::class);
    }

    public function globalDiscounts()
    {
        return $this->hasMany(PurchaseGlobalDiscount::class);
    }

    public function purchaseOrderDownPaymentAllocations()
    {
        return $this->hasMany(PurchaseOrderDownPaymentAllocation::class);
    }

    public function purchaseReturns()
    {
        return $this->hasMany(PurchaseReturn::class);
    }

    public function purchaseReturnAllocations()
    {
        return $this->hasMany(PurchaseReturnAllocation::class);
    }
}
