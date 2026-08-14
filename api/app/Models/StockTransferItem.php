<?php

namespace App\Models;

use App\Traits\BootableModel;
use App\Traits\ScopeableByBranch;
use App\Traits\ScopeableByCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockTransferItem extends Model
{
    use BootableModel;
    use HasFactory;
    use ScopeableByBranch;
    use ScopeableByCompany;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'branch_id',
        'stock_transfer_id',
        'qty',
        'product_unit_id',
        'product_unit_conversion_value',
        'product_unit_qty_base',
        'remarks',
    ];

    protected $casts = [
        'qty' => 'decimal:8',
        'product_unit_conversion_value' => 'decimal:8',
        'product_unit_qty_base' => 'decimal:8',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class)->withTrashed();
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class)->withTrashed();
    }

    public function stockTransfer()
    {
        return $this->belongsTo(StockTransfer::class)->withTrashed();
    }

    public function productUnit()
    {
        return $this->belongsTo(ProductUnit::class)->withTrashed();
    }

    public function serials()
    {
        return $this->hasMany(StockTransferItemSerial::class);
    }

    public function sourceStockTransaction()
    {
        return $this->morphOne(StockTransaction::class, 'referable')
            ->where('warehouse_id', $this->stockTransfer->source_warehouse_id);
    }

    public function destinationStockTransaction()
    {
        return $this->morphOne(StockTransaction::class, 'referable')
            ->where('warehouse_id', $this->stockTransfer->destination_warehouse_id);
    }
}
