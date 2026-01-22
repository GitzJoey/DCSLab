<?php

namespace App\Models;

use App\Enums\RecordStatusEnum;
use App\Traits\BootableModel;
use App\Traits\ScopeableByCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Warehouse extends Model
{
    use BootableModel;
    use HasFactory;
    use ScopeableByCompany;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'branch_id',
        'code',
        'name',
        'address',
        'city',
        'contact',
        'remarks',
        'status',
    ];

    protected $casts = [
        'status' => RecordStatusEnum::class,
    ];

    public function company()
    {
        return $this->belongsTo(Company::class)->withTrashed();
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class)->withTrashed();
    }

    public function purchaseReceipts()
    {
        return $this->hasMany(PurchaseReceipt::class);
    }

    public function stockTransferSource()
    {
        return $this->hasMany(StockTransfer::class, 'source_warehouse_id');
    }

    public function stockTransferDestination()
    {
        return $this->hasMany(StockTransfer::class, 'destination_warehouse_id');
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function saleProductUnits()
    {
        return $this->hasMany(SaleProductUnit::class);
    }

    public function saleReceipts()
    {
        return $this->hasMany(SaleReceipt::class);
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($query) use ($search) {
            $query->whereHas('branch', fn ($query) => $query->search($search))
                ->orWhere('warehouses.code', 'like', '%'.$search.'%')
                ->orWhere('warehouses.name', 'like', '%'.$search.'%')
                ->orWhere('warehouses.address', 'like', '%'.$search.'%')
                ->orWhere('warehouses.city', 'like', '%'.$search.'%')
                ->orWhere('warehouses.contact', 'like', '%'.$search.'%')
                ->orWhere('warehouses.remarks', 'like', '%'.$search.'%');
        });
    }
}
