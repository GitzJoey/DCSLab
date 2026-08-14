<?php

namespace App\Models;

use App\Enums\ProgressStatusEnum;
use App\Traits\ScopeableByBranch;
use App\Traits\ScopeableByCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use InvalidArgumentException;

class SalesOrder extends Model
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
        'customer_id',
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
        'progress_status' => ProgressStatusEnum::class,
        'item_total_count' => 'integer',
        'item_matched_count' => 'integer',
        'item_less_count' => 'integer',
        'item_more_count' => 'integer',
        'item_unlinked_count' => 'integer',
    ];

    protected static function booted(): void
    {
        $validateRelations = static function (self $salesOrder): void {
            $validateCompanyRelation = static function (
                ?int $relationId,
                string $modelClass,
                string $errorMessage
            ) use ($salesOrder): void {
                if (is_null($relationId)) {
                    return;
                }

                $relation = $modelClass::find($relationId);

                if (! $relation || (int) $relation->company_id !== (int) $salesOrder->company_id) {
                    throw new InvalidArgumentException($errorMessage);
                }
            };

            $validateCompanyRelation(
                relationId: $salesOrder->branch_id,
                modelClass: Branch::class,
                errorMessage: 'Sales order branch must exist in the same company.',
            );

            $validateCompanyRelation(
                relationId: $salesOrder->customer_id,
                modelClass: Customer::class,
                errorMessage: 'Sales order customer must exist in the same company.',
            );
        };

        static::creating(function (self $salesOrder) use ($validateRelations) {
            $salesOrder->ulid = Str::ulid()->generate();

            if (auth()->check()) {
                $salesOrder->created_by = auth()->id();
                $salesOrder->updated_by = auth()->id();
            }

            $validateRelations($salesOrder);
        });

        static::updating(function (self $salesOrder) use ($validateRelations) {
            if (auth()->check()) {
                $salesOrder->updated_by = auth()->id();
            }

            $validateRelations($salesOrder);
        });

        static::deleting(function (self $salesOrder) {
            if (! auth()->check()) {
                return;
            }

            $salesOrder->deleted_by = auth()->id();
            $salesOrder->save();
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

    public function customer()
    {
        return $this->belongsTo(Customer::class)->withTrashed();
    }

    public function items()
    {
        return $this->hasMany(SalesOrderItem::class);
    }

    public function payments()
    {
        return $this->hasMany(SalesOrderPayment::class);
    }

    public function refundedPayments()
    {
        return $this->hasMany(SalesOrderPaymentRefund::class);
    }

    public function deliveries()
    {
        return $this->hasMany(SalesOrderDelivery::class);
    }

    public function deliveryItems(): HasManyThrough
    {
        return $this->hasManyThrough(
            SalesOrderDeliveryItem::class,
            SalesOrderDelivery::class,
            'sales_order_id',
            'sales_order_delivery_id',
            'id',
            'id'
        );
    }

    public function invoices()
    {
        return $this->hasMany(SalesInvoice::class);
    }

    public function invoiceItems(): HasManyThrough
    {
        return $this->hasManyThrough(
            SalesInvoiceItem::class,
            SalesInvoice::class,
            'sales_order_id',
            'sales_invoice_id',
            'id',
            'id'
        );
    }
}
