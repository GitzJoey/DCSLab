<?php

namespace App\Models;

use App\Traits\BootableModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChartOfAccount extends Model
{
    use HasFactory;
    use SoftDeletes;
    use BootableModel;

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

    protected $casts = [
        'status' => RecordStatus::class,
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
