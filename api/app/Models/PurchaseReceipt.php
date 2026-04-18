<?php

namespace App\Models;

use App\Traits\BootableModel;
use App\Traits\ScopeableByBranch;
use App\Traits\ScopeableByCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseReceipt extends Model
{
    use BootableModel;
    use HasFactory;
    use ScopeableByBranch;
    use ScopeableByCompany;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'branch_id',
        'purchase_id',
        'code',
        'date',
        'warehouse_id',
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

    public function purchase()
    {
        return $this->belongsTo(Purchase::class)->withTrashed();
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class)->withTrashed();
    }

    public function items()
    {
        return $this->hasMany(PurchaseReceiptItem::class);
    }

    public function itemSerials()
    {
        return $this->hasMany(PurchaseReceiptItemSerial::class);
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($query) use ($search) {
            $query->where('purchase_receipts.code', 'like', '%'.$search.'%')
                ->orWhere('purchase_receipts.remarks', 'like', '%'.$search.'%');
        });
    }
}
