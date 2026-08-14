<?php

namespace App\Models;

use App\Traits\BootableModel;
use App\Traits\ScopeableByBranch;
use App\Traits\ScopeableByCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesReturnItemSerial extends Model
{
    use BootableModel;
    use HasFactory;
    use ScopeableByBranch;
    use ScopeableByCompany;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'branch_id',
        'sales_return_id',
        'sales_return_item_id',
        'serial',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class)->withTrashed();
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class)->withTrashed();
    }

    public function salesReturn()
    {
        return $this->belongsTo(SalesReturn::class)->withTrashed();
    }

    public function salesReturnItem()
    {
        return $this->belongsTo(SalesReturnItem::class)->withTrashed();
    }

    public function stockSerialTransaction()
    {
        return $this->morphOne(StockSerialTransaction::class, 'referable');
    }
}
