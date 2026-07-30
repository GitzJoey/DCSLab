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

class SalesReturn extends Model
{
    use HasFactory;
    use ScopeableByBranch;
    use ScopeableByCompany;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'branch_id',
        'sales_invoice_id',
        'customer_id',
        'code',
        'date',
        'warehouse_id',
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
        $validateRelations = static function (self $salesReturn): void {
            $validateCompanyRelation = static function (
                ?int $relationId,
                string $modelClass,
                string $errorMessage
            ) use ($salesReturn): void {
                if (is_null($relationId)) {
                    return;
                }

                $relation = $modelClass::find($relationId);

                if (! $relation || (int) $relation->company_id !== (int) $salesReturn->company_id) {
                    throw new InvalidArgumentException($errorMessage);
                }
            };

            $validateCompanyRelation(
                relationId: $salesReturn->branch_id,
                modelClass: Branch::class,
                errorMessage: 'Sales return branch must exist in the same company.',
            );

            $validateCompanyRelation(
                relationId: $salesReturn->sales_invoice_id,
                modelClass: SalesInvoice::class,
                errorMessage: 'Sales return sales invoice must exist in the same company.',
            );

            $validateCompanyRelation(
                relationId: $salesReturn->customer_id,
                modelClass: Customer::class,
                errorMessage: 'Sales return customer must exist in the same company.',
            );

            $validateCompanyRelation(
                relationId: $salesReturn->warehouse_id,
                modelClass: Warehouse::class,
                errorMessage: 'Sales return warehouse must exist in the same company.',
            );
        };

        static::creating(function (self $salesReturn) use ($validateRelations) {
            $salesReturn->ulid = Str::ulid()->generate();

            if (auth()->check()) {
                $salesReturn->created_by = auth()->id();
                $salesReturn->updated_by = auth()->id();
            }

            $validateRelations($salesReturn);
        });

        static::updating(function (self $salesReturn) use ($validateRelations) {
            if (auth()->check()) {
                $salesReturn->updated_by = auth()->id();
            }

            $validateRelations($salesReturn);
        });

        static::deleting(function (self $salesReturn) {
            if (! auth()->check()) {
                return;
            }

            $salesReturn->deleted_by = auth()->id();
            $salesReturn->save();
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

    public function salesInvoice()
    {
        return $this->belongsTo(SalesInvoice::class)->withTrashed();
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class)->withTrashed();
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class)->withTrashed();
    }

    public function items()
    {
        return $this->hasMany(SalesReturnItem::class);
    }

    public function itemSerials()
    {
        return $this->hasMany(SalesReturnItemSerial::class);
    }

    public function refunds()
    {
        return $this->hasMany(SalesReturnRefund::class);
    }

    public function invoicePayments()
    {
        return $this->hasMany(SalesInvoicePayment::class);
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
