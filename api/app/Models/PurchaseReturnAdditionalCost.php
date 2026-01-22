<?php

namespace App\Models;

use App\Traits\BootableModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseReturnAdditionalCost extends Model
{
    use BootableModel;
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'branch_id',
        'purchase_id',
        'code',
        'date',
        'amount',
        'remarks',
    ];

    protected $casts = [
        'date' => 'datetime',
        'amount' => 'decimal:8',
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

    public function category()
    {
        return $this->belongsTo(PurchaseReturnAdditionalCostCategory::class)->withTrashed();
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where('purchase_return_additional_costs.code', 'like', '%'.$search.'%')
            ->orWhere('purchase_return_additional_costs.date', 'like', '%'.$search.'%')
            ->orWhere('purchase_return_additional_costs.remarks', 'like', '%'.$search.'%');
    }
}
