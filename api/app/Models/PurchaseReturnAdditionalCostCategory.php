<?php

namespace App\Models;

use App\Traits\BootableModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseReturnAdditionalCostCategory extends Model
{
    use BootableModel;
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'code',
        'name',
    ];

    protected $casts = [

    ];

    public function company()
    {
        return $this->belongsTo(Company::class)->withTrashed();
    }

    public function purchaseReturnAdditionalCosts()
    {
        return $this->hasMany(PurchaseReturnAdditionalCost::class);
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where('purchase_return_additional_cost_categories.code', 'like', '%'.$search.'%')
            ->orWhere('purchase_return_additional_cost_categories.name', 'like', '%'.$search.'%');
    }
}
