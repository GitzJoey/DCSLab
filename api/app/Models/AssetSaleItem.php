<?php

namespace App\Models;

use App\Traits\BootableModel;
use App\Traits\ScopeableByBranch;
use App\Traits\ScopeableByCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssetSaleItem extends Model
{
    use BootableModel;
    use HasFactory;
    use ScopeableByBranch;
    use ScopeableByCompany;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'branch_id',
        'asset_sale_id',
        'asset_id',
        'qty',
        'unit_price',
        'subtotal',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'qty' => 'decimal:8',
            'unit_price' => 'decimal:8',
            'subtotal' => 'decimal:8',
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

    public function assetSale()
    {
        return $this->belongsTo(AssetSale::class)->withTrashed();
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class)->withTrashed();
    }

    public function serials()
    {
        return $this->hasMany(AssetSaleItemSerial::class);
    }
}
