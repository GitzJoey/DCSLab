<?php

namespace App\Models;

use App\Models\AccountingJournal;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChartOfAccount extends Model
{
    use HasFactory;
    use SoftDeletes;

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

    public function hId(): Attribute
    {
        return Attribute::make(
            get: fn () => HashIds::encode($this->attributes['id'])
        );
    }

    public function hParentId(): Attribute
    {
        return Attribute::make(
            get: fn () => HashIds::encode($this->attributes['parent_id'])
        );
    }

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
