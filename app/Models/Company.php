<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Branch;
use App\Models\Warehouse;
use App\Models\Cash;
use App\Models\Product;
use App\Models\ProductBrand;
use App\Models\ProductGroup;
use App\Models\ProductUnit;
use App\Models\Unit;
use App\Models\Customer;
use App\Models\CustomerGroup;
use App\Models\Investor;
use App\Models\Capital;
use App\Models\CapitalGroup;
use App\Models\ExpenseGroup;
use App\Models\IncomeGroup;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Support\Facades\Auth;

class Company extends Model
{
    use HasFactory, LogsActivity;
    use SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'default',
        'status'
    ];

    protected static $logAttributes = ['code', 'name', 'default', 'status'];

    protected static $logOnlyDirty = true;

    protected $hidden = [
        'id',
        'created_by',
        'updated_by',
        'deleted_by',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $appends = ['hId'];

    public function getHIdAttribute() : string
    {
        return HashIds::encode($this->attributes['id']);
    }

    public function branches()
    {
        return $this->hasMany(Branch::class);
    }

    public function warehouses()
    {
        return $this->hasMany(Warehouse::class);
    }

    public function cashes()
    {
        return $this->hasMany(Cash::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function productbrands()
    {
        return $this->hasMany(ProductBrand::class);
    }

    public function productgroups()
    {
        return $this->hasMany(ProductGroup::class);
    }

    public function productunits()
    {
        return $this->hasMany(ProductUnit::class);
    }

    public function units()
    {
        return $this->hasMany(Unit::class);
    }

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }

    public function customergroups()
    {
        return $this->hasMany(CustomerGroup::class);
    }

    public function expensegroups()
    {
        return $this->hasMany(ExpenseGroup::class);
    }

    public function incomegroups()
    {
        return $this->hasMany(IncomeGroup::class);
    }

    public function investors()
    {
        return $this->hasMany(Investor::class);
    }

    public function capitals()
    {
        return $this->hasMany(Capital::class);
    }

    public function capital_groups()
    {
        return $this->hasMany(CapitalGroup::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    public function suppliers()
    {
        return $this->hasMany(Supplier::class);
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $user = Auth::user();
            if ($user) {
                $model->created_by = $user->id;
                $model->updated_by = $user->id;
            }
        });

        static::updating(function ($model) {
            $user = Auth::user();
            if ($user) {
                $model->updated_by = $user->id;
            }
        });

        static::deleting(function ($model) {
            $user = Auth::user();
            if ($user) {
                $model->deleted_by = $user->id;
                $model->save();
            }
        });
    }
}
