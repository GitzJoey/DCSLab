<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\User;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\Warehouse;

use App\Models\ChartOfAccount;
use App\Models\AccountingJournal;

use App\Models\ProductGroup;
use App\Models\Brand;
use App\Models\Unit;
use App\Models\Product;
use App\Models\ProductUnit;

use App\Models\Supplier;

use App\Models\CustomerGroup;
use App\Models\Customer;
use App\Models\CustomerAddress;

class Company extends Model
{
    protected $fillable = [
        'code',
        'name',
        'address',
        'default',
        'status',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function branches()
    {
        return $this->hasMany(Branch::class);
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    public function warehouses()
    {
        return $this->hasMany(Warehouse::class);
    }

    public function chartOfAccounts()
    {
        return $this->hasMany(chartOfAccount::class);
    }

    public function accountingJournals()
    {
        return $this->hasMany(AccountingJournal::class);
    }

    public function productGroups()
    {
        return $this->hasMany(ProductGroup::class);
    }

    public function brands()
    {
        return $this->hasMany(Brand::class);
    }

    public function units()
    {
        return $this->hasMany(Unit::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function productUnits()
    {
        return $this->hasMany(ProductUnit::class);
    }

    public function suppliers()
    {
        return $this->hasMany(Supplier::class);
    }

    public function customerGroups()
    {
        return $this->hasMany(CustomerGroup::class);
    }
    
    public function customers()
    {
        return $this->hasMany(Customer::class);
    }

    public function customerAddresses()
    {
        return $this->hasMany(CustomerAddress::class);
    }
}
