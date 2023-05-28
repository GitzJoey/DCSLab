<?php

namespace App\Models;

use App\Enums\RecordStatus;
use App\Traits\BootableModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Branch extends Model
{
    use HasFactory;
    use SoftDeletes;
    use BootableModel;

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

    protected $casts = [
        'is_main' => 'boolean',
        'status' => RecordStatus::class,
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

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }
}
