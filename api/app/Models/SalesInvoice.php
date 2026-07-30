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

class SalesInvoice extends Model
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
        'sales_order_id',
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
        $validateRelations = static function (self $salesInvoice): void {
            $validateCompanyRelation = static function (
                ?int $relationId,
                string $modelClass,
                string $errorMessage
            ) use ($salesInvoice): void {
                if (is_null($relationId)) {
                    return;
                }

                $relation = $modelClass::find($relationId);

                if (! $relation || (int) $relation->company_id !== (int) $salesInvoice->company_id) {
                    throw new InvalidArgumentException($errorMessage);
                }
            };

            $validateCompanyRelation(
                relationId: $salesInvoice->branch_id,
                modelClass: Branch::class,
                errorMessage: 'Sales invoice branch must exist in the same company.',
            );

            $validateCompanyRelation(
                relationId: $salesInvoice->customer_id,
                modelClass: Customer::class,
                errorMessage: 'Sales invoice customer must exist in the same company.',
            );

            $validateCompanyRelation(
                relationId: $salesInvoice->sales_order_id,
                modelClass: SalesOrder::class,
                errorMessage: 'Sales invoice sales order must exist in the same company.',
            );
        };

        static::creating(function (self $salesInvoice) use ($validateRelations) {
            $salesInvoice->ulid = Str::ulid()->generate();

            if (auth()->check()) {
                $salesInvoice->created_by = auth()->id();
                $salesInvoice->updated_by = auth()->id();
            }

            $validateRelations($salesInvoice);
        });

        static::updating(function (self $salesInvoice) use ($validateRelations) {
            if (auth()->check()) {
                $salesInvoice->updated_by = auth()->id();
            }

            $validateRelations($salesInvoice);
        });

        static::deleting(function (self $salesInvoice) {
            if (! auth()->check()) {
                return;
            }

            $salesInvoice->deleted_by = auth()->id();
            $salesInvoice->save();
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

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class)->withTrashed();
    }

    public function items()
    {
        return $this->hasMany(SalesInvoiceItem::class);
    }

    public function payments()
    {
        return $this->hasMany(SalesInvoicePayment::class);
    }

    public function salesReturns()
    {
        return $this->hasMany(SalesReturn::class);
    }

    public function journalEntry()
    {
        return $this->morphOne(JournalEntry::class, 'source')
            ->where('journal_type', JournalEntryTypeEnum::TRANSACTION->value);
    }

    public function currentMonthEarningsJournalEntry()
    {
        return $this->hasOne(JournalEntry::class, 'source_id', 'id')
            ->where('source_type', self::class)
            ->where('journal_type', JournalEntryTypeEnum::CURRENT_MONTH_EARNINGS->value);
    }

    public function monthEndClosingJournalEntry()
    {
        return $this->hasOne(JournalEntry::class, 'source_id', 'id')
            ->where('source_type', self::class)
            ->where('journal_type', JournalEntryTypeEnum::MONTH_END_CLOSING->value);
    }

    public function monthToYearClosingJournalEntry()
    {
        return $this->hasOne(JournalEntry::class, 'source_id', 'id')
            ->where('source_type', self::class)
            ->where('journal_type', JournalEntryTypeEnum::MONTH_TO_YEAR_CLOSING->value);
    }

    public function yearToRetainedEarningsClosingJournalEntry()
    {
        return $this->hasOne(JournalEntry::class, 'source_id', 'id')
            ->where('source_type', self::class)
            ->where('journal_type', JournalEntryTypeEnum::YEAR_TO_RETAINED_EARNINGS_CLOSING->value);
    }
}
