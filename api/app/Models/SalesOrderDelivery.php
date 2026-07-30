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

class SalesOrderDelivery extends Model
{
    use HasFactory;
    use ScopeableByBranch;
    use ScopeableByCompany;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'branch_id',
        'customer_id',
        'sales_order_id',
        'code',
        'date',
        'warehouse_id',
        'remarks',
        'is_posted',
        'total_cogs',
        'total_cost',
    ];

    protected $casts = [
        'date' => 'datetime',
        'is_posted' => 'boolean',
        'total_cogs' => 'decimal:8',
        'total_cost' => 'decimal:8',
    ];

    protected static function booted(): void
    {
        $validateRelations = static function (self $salesOrderDelivery): void {
            $validateCompanyRelation = static function (
                ?int $relationId,
                string $modelClass,
                string $errorMessage
            ) use ($salesOrderDelivery): void {
                if (is_null($relationId)) {
                    return;
                }

                $relation = $modelClass::find($relationId);

                if (! $relation || (int) $relation->company_id !== (int) $salesOrderDelivery->company_id) {
                    throw new InvalidArgumentException($errorMessage);
                }
            };

            $validateCompanyRelation(
                relationId: $salesOrderDelivery->branch_id,
                modelClass: Branch::class,
                errorMessage: 'Sales order delivery branch must exist in the same company.',
            );

            $validateCompanyRelation(
                relationId: $salesOrderDelivery->customer_id,
                modelClass: Customer::class,
                errorMessage: 'Sales order delivery customer must exist in the same company.',
            );

            $validateCompanyRelation(
                relationId: $salesOrderDelivery->sales_order_id,
                modelClass: SalesOrder::class,
                errorMessage: 'Sales order delivery sales order must exist in the same company.',
            );

            $validateCompanyRelation(
                relationId: $salesOrderDelivery->warehouse_id,
                modelClass: Warehouse::class,
                errorMessage: 'Sales order delivery warehouse must exist in the same company.',
            );
        };

        static::creating(function (self $salesOrderDelivery) use ($validateRelations) {
            $salesOrderDelivery->ulid = Str::ulid()->generate();

            if (auth()->check()) {
                $salesOrderDelivery->created_by = auth()->id();
                $salesOrderDelivery->updated_by = auth()->id();
            }

            $validateRelations($salesOrderDelivery);
        });

        static::updating(function (self $salesOrderDelivery) use ($validateRelations) {
            if (auth()->check()) {
                $salesOrderDelivery->updated_by = auth()->id();
            }

            $validateRelations($salesOrderDelivery);
        });

        static::deleting(function (self $salesOrderDelivery) {
            if (! auth()->check()) {
                return;
            }

            $salesOrderDelivery->deleted_by = auth()->id();
            $salesOrderDelivery->save();
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

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class)->withTrashed();
    }

    public function items()
    {
        return $this->hasMany(SalesOrderDeliveryItem::class);
    }

    public function itemSerials()
    {
        return $this->hasMany(SalesOrderDeliveryItemSerial::class);
    }

    public function costs()
    {
        return $this->hasMany(SalesOrderDeliveryCost::class);
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
