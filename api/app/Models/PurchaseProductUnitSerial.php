<?php

namespace App\Models;

use App\Traits\BootableModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseProductUnitSerial extends Model
{
    use BootableModel;
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'branch_id',
        'purchase_id',
        'purchase_product_unit_id',
    ];

    protected $casts = [

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

    public function purchaseProductUnit()
    {
        return $this->belongsTo(PurchaseProductUnit::class)->withTrashed();
    }

    public function scopeSearch($query, string $search)
    {
        return $query->whereHas('company', function ($query) use ($search) {
            $query->search($search);
        })
            ->orWhereHas('branch', function ($query) use ($search) {
                $query->search($search);
            })
            ->orWhereHas('purchase', function ($query) use ($search) {
                $query->search($search);
            })
            ->orWhereHas('purchaseProductUnit', function ($query) use ($search) {
                $query->search($search);
            });
    }
}
