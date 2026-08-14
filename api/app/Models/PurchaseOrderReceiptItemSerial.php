<?php

namespace App\Models;

use App\Traits\BootableModel;
use App\Traits\ScopeableByBranch;
use App\Traits\ScopeableByCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrderReceiptItemSerial extends Model
{
    use BootableModel;
    use HasFactory;
    use ScopeableByBranch;
    use ScopeableByCompany;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'branch_id',
        'purchase_order_receipt_id',
        'purchase_order_receipt_item_id',
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

    public function purchaseOrderReceipt()
    {
        return $this->belongsTo(PurchaseOrderReceipt::class)->withTrashed();
    }

    public function purchaseOrderReceiptItem()
    {
        return $this->belongsTo(PurchaseOrderReceiptItem::class)->withTrashed();
    }

    public function stockSerialTransaction()
    {
        return $this->morphOne(StockSerialTransaction::class, 'referable');
    }
}
