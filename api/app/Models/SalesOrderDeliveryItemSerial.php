<?php

namespace App\Models;

use App\Traits\BootableModel;
use App\Traits\ScopeableByBranch;
use App\Traits\ScopeableByCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesOrderDeliveryItemSerial extends Model
{
    use BootableModel;
    use HasFactory;
    use ScopeableByBranch;
    use ScopeableByCompany;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'branch_id',
        'sales_order_delivery_id',
        'sales_order_delivery_item_id',
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

    public function salesOrderDelivery()
    {
        return $this->belongsTo(SalesOrderDelivery::class)->withTrashed();
    }

    public function salesOrderDeliveryItem()
    {
        return $this->belongsTo(SalesOrderDeliveryItem::class)->withTrashed();
    }

    public function stockSerialTransaction()
    {
        return $this->morphOne(StockSerialTransaction::class, 'referable');
    }
}
