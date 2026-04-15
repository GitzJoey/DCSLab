<?php

namespace App\Models;

use App\Traits\BootableModel;
use App\Traits\ScopeableByCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VatProfile extends Model
{
    use BootableModel;
    use HasFactory;
    use ScopeableByCompany;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'code',
        'name',
        'vat_rate',
        'vat_base_numerator',
        'vat_base_denominator',
        'remarks',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'vat_rate' => 'decimal:8',
            'vat_base_numerator' => 'integer',
            'vat_base_denominator' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function company()
    {
        return $this->belongsTo(Company::class)->withTrashed();
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'default_vat_profile_id');
    }

    public function purchaseOrderItems()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    // public function purchaseItems()
    // {
    //     return $this->hasMany(PurchaseItem::class);
    // }

    // public function purchaseReturnItems()
    // {
    //     return $this->hasMany(PurchaseReturnItem::class);
    // }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($query) use ($search) {
            $query->where('vat_profiles.code', 'like', '%'.$search.'%')
                ->orWhere('vat_profiles.name', 'like', '%'.$search.'%')
                ->orWhere('vat_profiles.remarks', 'like', '%'.$search.'%');
        });
    }
}
