<?php

namespace App\Models;

use App\Traits\BootableModel;
use App\Traits\ScopeableByBranch;
use App\Traits\ScopeableByCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockTransfer extends Model
{
    use BootableModel;
    use HasFactory;
    use ScopeableByBranch;
    use ScopeableByCompany;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'branch_id',
        'code',
        'date',
        'source_warehouse_id',
        'destination_warehouse_id',
        'remarks',
        'is_posted',
    ];

    protected $casts = [
        'date' => 'datetime',
        'is_posted' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class)->withTrashed();
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class)->withTrashed();
    }

    public function sourceWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'source_warehouse_id')->withTrashed();
    }

    public function destinationWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'destination_warehouse_id')->withTrashed();
    }

    public function stockTransferProductUnits()
    {
        return $this->hasMany(StockTransferProductUnit::class);
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($query) use ($search) {
            $query->where('stock_transfers.code', 'like', '%'.$search.'%')
                ->orWhere('stock_transfers.remarks', 'like', '%'.$search.'%');
        });
    }
}
