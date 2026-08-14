<?php

namespace App\Models;

use App\Traits\BootableModel;
use App\Traits\ScopeableByBranch;
use App\Traits\ScopeableByCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockAdjustmentInItemSerial extends Model
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
        'stock_adjustment_in_item_id',
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

    public function stockAdjustmentInItem()
    {
        return $this->belongsTo(StockAdjustmentInItem::class)->withTrashed();
    }

    public function stockSerialTransaction()
    {
        return $this->morphOne(StockSerialTransaction::class, 'referable');
    }
}
