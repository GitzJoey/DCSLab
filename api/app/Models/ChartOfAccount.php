<?php

namespace App\Models;

use App\Models\AccountingJournal;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class ChartOfAccount extends Model
{
    protected $fillable = [
        'company_id',
        'branch_id',
        'parent_id',
        'code',
        'name',
        'can_have_child',
        'status',
        'account_type',
        'remarks',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function parentAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'parent_id');
    }

    public function childAccounts()
    {
        return $this->hasMany(ChartOfAccount::class, 'parent_id', 'id');
    }

    public function childrenRecursive()
    {  
        return $this->hasMany(ChartOfAccount::class, 'parent_id', 'id')->with('childrenRecursive');
    }

    public function accountingJournals()
    {
        return $this->hasMany(AccountingJournal::class);
    }
}
