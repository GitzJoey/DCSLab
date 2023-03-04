<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Company;
use App\Models\Warehouse;
use App\Models\AccountingJournal;

class Branch extends Model
{

    protected $fillable = [
        'company_id',
        'code',
        'name',
        'address',
        'city',
        'contact',
        'status',
        'is_main',
        'remarks',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function employeeAccesses()
    {
        return $this->hasMany(EmployeeAccess::class);
    }

    public function warehouses()
    {
        return $this->hasMany(Warehouse::class);
    }

    public function accountingJournals()
    {
        return $this->hasMany(AccountingJournal::class);
    }
}
