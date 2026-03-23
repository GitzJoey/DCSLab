<?php

namespace App\Models;

use App\Traits\BootableModel;
use App\Traits\ScopeableByBranch;
use App\Traits\ScopeableByCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockTransferProductUnitSerial extends Model
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
        'stock_transfer_product_unit_id',
        'serial',
    ];

    protected $casts = [

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

    public function stockTransferProductUnit()
    {
        return $this->belongsTo(StockTransferProductUnit::class)->withTrashed();
    }

    public function stockSerialTransactionSource()
    {
        return $this->morphOne(StockSerialTransaction::class, 'referable')->where('direction', -1);
    }

    public function stockSerialTransactionDestination()
    {
        return $this->morphOne(StockSerialTransaction::class, 'referable')->where('direction', 1);
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where('serial', 'like', '%'.$search.'%');
    }
}
