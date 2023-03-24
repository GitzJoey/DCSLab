<?php

namespace App\Models;

use App\Traits\BootableModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class Company extends Model
{
    use HasFactory;
    use SoftDeletes;
    use BootableModel;

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

    public function employeeAccesses()
    {
        return $this->hasMany(EmployeeAccess::class);
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

    public function supplierProducts()
    {
        return $this->hasMany(SupplierProduct::class);
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

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->ulid = Str::ulid();

            $user = Auth::check();
            if ($user) {
                $model->created_by = Auth::id();
                $model->updated_by = Auth::id();
            }
        });

        static::updating(function ($model) {
            $user = Auth::check();
            if ($user) {
                $model->updated_by = Auth::id();
            }
        });

        static::deleting(function ($model) {
            $user = Auth::check();
            if ($user) {
                $model->deleted_by = Auth::id();
                $model->save();
            }
        });
    }
}
