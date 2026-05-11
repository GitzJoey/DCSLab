<?php

namespace App\Models;

use App\Enums\PurchaseProgressStatusEnum;
use App\Traits\ScopeableByBranch;
use App\Traits\ScopeableByCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use InvalidArgumentException;

class PurchaseOrder extends Model
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
        'remarks',

        'item_total_before_global_discount',
        'global_discount',
        'item_total_after_global_discount',
        'vat_base',
        'vat',
        'item_total_after_vat',
        'rounding',
        'amount_payable',
        'amount_paid_down_payment',
        'amount_allocated_down_payment',
        'amount_refunded_down_payment',
        'amount_available_down_payment',
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
        'item_total_before_global_discount' => 'decimal:8',
        'global_discount' => 'decimal:8',
        'item_total_after_global_discount' => 'decimal:8',
        'vat_base' => 'decimal:8',
        'vat' => 'decimal:8',
        'item_total_after_vat' => 'decimal:8',
        'rounding' => 'decimal:8',
        'amount_payable' => 'decimal:8',
        'amount_paid_down_payment' => 'decimal:8',
        'amount_allocated_down_payment' => 'decimal:8',
        'amount_refunded_down_payment' => 'decimal:8',
        'amount_available_down_payment' => 'decimal:8',
        'progress_status' => PurchaseProgressStatusEnum::class,
        'item_total_count' => 'integer',
        'item_matched_count' => 'integer',
        'item_less_count' => 'integer',
        'item_more_count' => 'integer',
        'item_unlinked_count' => 'integer',
    ];

    protected static function booted(): void
    {
        $validateRelations = static function (self $purchaseOrder): void {
            $validateCompanyRelation = static function (
                ?int $relationId,
                string $modelClass,
                string $errorMessage
            ) use ($purchaseOrder): void {
                if (is_null($relationId)) {
                    return;
                }

                $relation = $modelClass::find($relationId);

                if (! $relation || (int) $relation->company_id !== (int) $purchaseOrder->company_id) {
                    throw new InvalidArgumentException($errorMessage);
                }
            };

            $validateCompanyRelation(
                relationId: $purchaseOrder->branch_id,
                modelClass: Branch::class,
                errorMessage: 'Purchase order branch must exist in the same company.',
            );

            $validateCompanyRelation(
                relationId: $purchaseOrder->supplier_id,
                modelClass: Supplier::class,
                errorMessage: 'Purchase order supplier must exist in the same company.',
            );
        };

        static::creating(function (self $purchaseOrder) use ($validateRelations) {
            $purchaseOrder->ulid = Str::ulid()->generate();

            if (auth()->check()) {
                $purchaseOrder->created_by = auth()->id();
                $purchaseOrder->updated_by = auth()->id();
            }

            $validateRelations($purchaseOrder);
        });

        static::updating(function (self $purchaseOrder) use ($validateRelations) {
            if (auth()->check()) {
                $purchaseOrder->updated_by = auth()->id();
            }

            $validateRelations($purchaseOrder);
        });

        static::deleting(function (self $purchaseOrder) {
            if (! auth()->check()) {
                return;
            }

            $purchaseOrder->deleted_by = auth()->id();
            $purchaseOrder->save();
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

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function globalDiscounts()
    {
        return $this->hasMany(PurchaseOrderGlobalDiscount::class);
    }

    public function downPayments()
    {
        return $this->hasMany(PurchaseOrderDownPayment::class);
    }

    public function refundedDownPayments()
    {
        return $this->hasMany(PurchaseOrderDownPaymentRefund::class);
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function purchaseItems(): HasManyThrough
    {
        return $this->hasManyThrough(
            PurchaseItem::class,
            Purchase::class,
            'purchase_order_id',
            'purchase_id',
            'id',
            'id'
        );
    }
}
