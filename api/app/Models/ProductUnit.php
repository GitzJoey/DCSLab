<?php

namespace App\Models;

use App\Traits\BootableModel;
use App\Traits\ScopeableByCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductUnit extends Model
{
    use BootableModel;
    use HasFactory;
    use ScopeableByCompany;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'product_id',
        'code',
        'is_manufacturer_sku',
        'unit_id',
        'price',
        'is_base',
        'conversion_value',
        'is_primary_unit',
        'point',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'is_base' => 'boolean',
            'is_manufacturer_sku' => 'boolean',
            'conversion_value' => 'decimal:2',
            'is_primary_unit' => 'boolean',
            'point' => 'integer',
        ];
    }

    public function company()
    {
        return $this->belongsTo(Company::class)->withTrashed();
    }

    public function product()
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class)->withTrashed();
    }

    public function purchaseReturnProductUnits()
    {
        return $this->hasMany(PurchaseReturnProductUnit::class);
    }

    public function purchaseReceiptProductUnits()
    {
        return $this->hasMany(PurchaseReceiptProductUnit::class);
    }

    public function stockTransferProductUnits()
    {
        return $this->hasMany(StockTransferProductUnit::class);
    }

    public function saleOrderProductUnits()
    {
        return $this->hasMany(SaleOrderProductUnit::class);
    }

    public function saleProductUnits()
    {
        return $this->hasMany(SaleProductUnit::class);
    }

    public function saleReceiptProductUnits()
    {
        return $this->hasMany(SaleReceiptProductUnit::class);
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($query) use ($search) {
            $query->where('product_units.code', 'like', '%'.$search.'%')
                ->orWhere('product_units.remarks', 'like', '%'.$search.'%');
        });
    }
}
