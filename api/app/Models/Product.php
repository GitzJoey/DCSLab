<?php

namespace App\Models;

use App\Enums\ProductTypeEnum;
use App\Enums\RecordStatusEnum;
use App\Traits\BootableModel;
use App\Traits\ScopeableByCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Product extends Model
{
    use BootableModel;
    use HasFactory;
    use ScopeableByCompany;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'code',
        'category_id',
        'brand_id',
        'name',
        'slug',
        'is_taxable',
        'vat_rate',
        'is_price_include_vat',
        'is_use_serial_number',
        'is_expirable',
        'remarks',
        'type',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'is_taxable' => 'boolean',
            'vat_rate' => 'decimal:8',
            'is_price_include_vat' => 'boolean',
            'is_use_serial_number' => 'boolean',
            'is_expirable' => 'boolean',
            'type' => ProductTypeEnum::class,
            'status' => RecordStatusEnum::class,
        ];
    }

    public function company()
    {
        return $this->belongsTo(Company::class)->withTrashed();
    }

    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'category_id')->withTrashed();
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class)->withTrashed();
    }

    public function baseProductUnit()
    {
        return $this->hasOne(ProductUnit::class)->where('is_base', true);
    }

    public function productUnits()
    {
        return $this->hasMany(ProductUnit::class);
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

    public function stockTransactions()
    {
        return $this->hasMany(StockTransaction::class);
    }

    public function scopeWithRemainingStock($query, ?string $endDate, ?int $warehouseId)
    {
        $productsWithRemainingStockQuery = StockTransaction::select(
            'stock_transactions.product_id',
            DB::raw('SUM(stock_transactions.base_qty) AS remaining_stock')
        );

        if ($endDate) {
            $productsWithRemainingStockQuery->where('stock_transactions.date', '<=', $endDate);
        }

        if ($warehouseId) {
            $productsWithRemainingStockQuery->where('stock_transactions.warehouse_id', '=', $warehouseId);
        }

        $productsWithRemainingStockQuery->groupBy('stock_transactions.product_id');

        $query->leftJoinSub($productsWithRemainingStockQuery, 'products_with_remaining_stock', function ($join) {
            $join->on('products.id', '=', 'products_with_remaining_stock.product_id');
        });

        $query->addSelect(
            DB::raw('COALESCE(products_with_remaining_stock.remaining_stock, 0) AS remaining_stock')
        );

        return $query;
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($query) use ($search) {
            $query->whereHas('brand', fn ($q) => $q->search($search))
                ->orWhereHas('category', fn ($q) => $q->search($search))
                ->orWhere('products.code', 'like', '%'.$search.'%')
                ->orWhere('products.name', 'like', '%'.$search.'%')
                ->orWhere('products.remarks', 'like', '%'.$search.'%')
                ->orWhereHas('productUnits', fn ($q) => $q->search($search));
        });
    }
}
