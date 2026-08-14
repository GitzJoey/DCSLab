<?php

namespace App\Models;

use App\Traits\BootableModel;
use App\Traits\ScopeableByBranch;
use App\Traits\ScopeableByCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssetPurchaseItem extends Model
{
    use BootableModel;
    use HasFactory;
    use ScopeableByBranch;
    use ScopeableByCompany;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'branch_id',
        'asset_purchase_id',
        'asset_id',
        'qty',
        'unit_price',
        'subtotal',
        'allocated_additional_cost',
        'subtotal_after_additional_cost',
        'unit_acquisition_cost',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'qty' => 'decimal:8',
            'unit_price' => 'decimal:8',
            'subtotal' => 'decimal:8',
            'allocated_additional_cost' => 'decimal:8',
            'subtotal_after_additional_cost' => 'decimal:8',
            'unit_acquisition_cost' => 'decimal:8',
        ];
    }

    public function company()
    {
        return $this->belongsTo(Company::class)->withTrashed();
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class)->withTrashed();
    }

    public function assetPurchase()
    {
        return $this->belongsTo(AssetPurchase::class)->withTrashed();
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class)->withTrashed();
    }

    public function serials()
    {
        return $this->hasMany(AssetPurchaseItemSerial::class);
    }
}
