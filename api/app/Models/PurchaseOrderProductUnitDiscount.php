<?php

namespace App\Models;

use App\Traits\BootableModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrderProductUnitDiscount extends Model
{
    use BootableModel;
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'branch_id',
        'purchase_order_product_unit_id',
        'sequence',
        'rate',
        'fixed',
    ];

    protected $casts = [
        'sequence' => 'integer',
        'rate' => 'decimal:8',
        'fixed' => 'decimal:8',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class)->withTrashed();
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class)->withTrashed();
    }

    public function purchaseOrderProductUnit()
    {
        return $this->belongsTo(PurchaseOrderProductUnit::class)->withTrashed();
    }
}
