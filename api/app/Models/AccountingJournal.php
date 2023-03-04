<?php

namespace App\Models;

use App\Models\ChartOfAccount;
use Illuminate\Database\Eloquent\Model;

class AccountingJournal extends Model
{

    protected $fillable = [
        'company_id',
        'branch_id',
        'ref',
        'ref_number',
        'chart_of_account_id',
        'date',
        'transaction_type',
        'value',
        'remarks',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
    
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function chartOfAccount()
    {
        return $this->belongsTo(ChartOfAccount::class);
    }
}
