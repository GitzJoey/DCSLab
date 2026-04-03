<?php

namespace App\Models;

use App\Traits\BootableModel;
use App\Traits\ScopeableByBranch;
use App\Traits\ScopeableByCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockAdjustmentOutItemSerial extends Model
{
    use BootableModel;
    use HasFactory;
    use ScopeableByBranch;
    use ScopeableByCompany;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'branch_id',
        'stock_adjustment_id',
        'stock_adjustment_out_item_id',
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

    public function stockAdjustment()
    {
        return $this->belongsTo(StockAdjustment::class)->withTrashed();
    }

    public function stockAdjustmentOutItem()
    {
        return $this->belongsTo(StockAdjustmentOutItem::class)->withTrashed();
    }

    public function stockSerialTransaction()
    {
        return $this->morphOne(StockSerialTransaction::class, 'referable');
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where('serial', 'like', '%'.$search.'%');
    }
}
