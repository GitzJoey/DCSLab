<?php

namespace App\Models;

use App\Traits\BootableModel;
use App\Traits\ScopeableByBranch;
use App\Traits\ScopeableByCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseReturnItemSerial extends Model
{
    use BootableModel;
    use HasFactory;
    use ScopeableByBranch;
    use ScopeableByCompany;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'branch_id',
        'purchase_return_id',
        'purchase_return_item_id',
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

    public function purchaseReturn()
    {
        return $this->belongsTo(PurchaseReturn::class)->withTrashed();
    }

    public function purchaseReturnItem()
    {
        return $this->belongsTo(PurchaseReturnItem::class)->withTrashed();
    }

    public function stockSerialTransaction()
    {
        return $this->morphOne(StockSerialTransaction::class, 'referable');
    }
}
