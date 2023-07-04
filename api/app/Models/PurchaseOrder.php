<?php

namespace App\Models;

use App\Enums\DiscountType;
use App\Enums\RecordStatus;
use App\Traits\BootableModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model
{
    use HasFactory;
    use SoftDeletes;
    use BootableModel;

    protected $fillable = [
        'company_id',
        'branch_id',
        'invoice_code',
        'invoice_date',
        'supplier_id',
        'shipping_date',
        'shipping_address',
        'total',
        'grand_total',
        'remarks',
        'status',
    ];

    protected $casts = [
        'status' => RecordStatus::class,
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function globalDiscounts()
    {
        $result = $this->hasMany(PurchaseOrderDiscount::class)
            ->where(function ($query) {
                $query->where('discount_type', '=', DiscountType::GLOBAL_PERCENT_DISCOUNT->value)
                    ->orWhere('discount_type', '=', DiscountType::GLOBAL_NOMINAL_DISCOUNT->value);
            });

        return $result;
    }

    public function productUnits()
    {
        return $this->hasMany(PurchaseOrderProductUnit::class);
    }
}
