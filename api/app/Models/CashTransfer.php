<?php

namespace App\Models;

use App\Traits\BootableModel;
use App\Traits\ScopeableByBranch;
use App\Traits\ScopeableByCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CashTransfer extends Model
{
    use BootableModel;
    use HasFactory;
    use ScopeableByBranch;
    use ScopeableByCompany;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'branch_id',
        'code',
        'date',
        'source_cash_account_id',
        'destination_cash_account_id',
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

    public function sourceCashAccount()
    {
        return $this->belongsTo(CashAccount::class, 'source_cash_account_id')->withTrashed();
    }

    public function destinationCashAccount()
    {
        return $this->belongsTo(CashAccount::class, 'destination_cash_account_id')->withTrashed();
    }

    public function sourceCashTransaction()
    {
        return $this->morphOne(CashTransaction::class, 'referable')
            ->where('cash_account_id', $this->source_cash_account_id);
    }

    public function destinationCashTransaction()
    {
        return $this->morphOne(CashTransaction::class, 'referable')
            ->where('cash_account_id', $this->destination_cash_account_id);
    }
}
