<?php

namespace App\Models;

use App\Enums\JournalEntryTypeEnum;
use App\Traits\BootableModel;
use App\Traits\ScopeableByBranch;
use App\Traits\ScopeableByCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesOrderDeliveryCost extends Model
{
    use BootableModel;
    use HasFactory;
    use ScopeableByBranch;
    use ScopeableByCompany;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'branch_id',
        'sales_order_delivery_id',
        'code',
        'date',
        'name',
        'cash_account_id',
        'amount',
        'remarks',
    ];

    protected $casts = [
        'date' => 'datetime',
        'amount' => 'decimal:8',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class)->withTrashed();
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class)->withTrashed();
    }

    public function salesOrderDelivery()
    {
        return $this->belongsTo(SalesOrderDelivery::class)->withTrashed();
    }

    public function cashAccount()
    {
        return $this->belongsTo(CashAccount::class)->withTrashed();
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
