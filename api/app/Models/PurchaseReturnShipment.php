<?php

namespace App\Models;

use App\Traits\BootableModel;
use App\Traits\ScopeableByBranch;
use App\Traits\ScopeableByCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseReturnShipment extends Model
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
        'supplier_id',
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

    public function purchaseReturn()
    {
        return $this->belongsTo(PurchaseReturn::class)->withTrashed();
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class)->withTrashed();
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class)->withTrashed();
    }

    public function items()
    {
        return $this->hasMany(PurchaseReturnShipmentItem::class);
    }

    public function itemSerials()
    {
        return $this->hasMany(PurchaseReturnShipmentItemSerial::class);
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($query) use ($search) {
            $query->where('purchase_return_shipments.code', 'like', '%'.$search.'%')
                ->orWhere('purchase_return_shipments.remarks', 'like', '%'.$search.'%');
        });
    }
}
