<?php

namespace App\Models;

use App\Enums\JournalEntryTypeEnum;
use App\Enums\PaymentTypeEnum;
use App\Traits\BootableModel;
use App\Traits\ScopeableByBranch;
use App\Traits\ScopeableByCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesInvoicePayment extends Model
{
    use BootableModel;
    use HasFactory;
    use ScopeableByBranch;
    use ScopeableByCompany;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'branch_id',
        'sales_invoice_id',
        'code',
        'date',
        'payment_type',
        'cash_account_id',
        'sales_order_payment_id',
        'sales_return_id',
        'amount',
        'remarks',
    ];

    protected $casts = [
        'date' => 'datetime',
        'payment_type' => PaymentTypeEnum::class,
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

    public function salesInvoice()
    {
        return $this->belongsTo(SalesInvoice::class)->withTrashed();
    }

    public function cashAccount()
    {
        return $this->belongsTo(CashAccount::class)->withTrashed();
    }

    public function salesOrderPayment()
    {
        return $this->belongsTo(SalesOrderPayment::class)->withTrashed();
    }

    public function salesReturn()
    {
        return $this->belongsTo(SalesReturn::class)->withTrashed();
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
}
