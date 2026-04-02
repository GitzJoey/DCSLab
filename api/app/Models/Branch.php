<?php

namespace App\Models;

use App\Enums\RecordStatusEnum;
use App\Traits\BootableModel;
use App\Traits\ScopeableByCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Branch extends Model
{
    use BootableModel;
    use HasFactory;
    use ScopeableByCompany;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'code',
        'name',
        'address',
        'city',
        'contact',
        'is_main',
        'remarks',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'is_main' => 'boolean',
            'status' => RecordStatusEnum::class,
        ];
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function warehouses()
    {
        return $this->hasMany(Warehouse::class);
    }

    public function cashAccounts()
    {
        return $this->hasMany(CashAccount::class);
    }

    public function stockTransfers()
    {
        return $this->hasMany(StockTransfer::class);
    }

    public function stockTransferProductUnits()
    {
        return $this->hasMany(StockTransferProductUnit::class);
    }

    public function stockTransferProductUnitSerials()
    {
        return $this->hasMany(StockTransferProductUnitSerial::class);
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($query) use ($search) {
            $query->where('branches.code', 'like', '%'.$search.'%')
                ->orWhere('branches.name', 'like', '%'.$search.'%')
                ->orWhere('branches.address', 'like', '%'.$search.'%')
                ->orWhere('branches.city', 'like', '%'.$search.'%')
                ->orWhere('branches.contact', 'like', '%'.$search.'%')
                ->orWhere('branches.remarks', 'like', '%'.$search.'%');
        });
    }
}
