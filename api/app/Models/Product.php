<?php

namespace App\Models;

use App\Enums\ProductTypeEnum;
use App\Enums\RecordStatusEnum;
use App\Traits\ScopeableByCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class Product extends Model
{
    use HasFactory;
    use ScopeableByCompany;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'code',
        'category_id',
        'brand_id',
        'name',
        'default_vat_profile_id',
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
            'is_price_include_vat' => 'boolean',
            'is_use_serial_number' => 'boolean',
            'is_expirable' => 'boolean',
            'type' => ProductTypeEnum::class,
            'status' => RecordStatusEnum::class,
        ];
    }

    protected static function booted(): void
    {
        $validateRelations = static function (self $product): void {
            $validateCompanyRelation = static function (
                ?int $relationId,
                string $modelClass,
                string $errorMessage
            ) use ($product): void {
                if (is_null($relationId)) {
                    return;
                }

                $relation = $modelClass::find($relationId);

                if (! $relation || (int) $relation->company_id !== (int) $product->company_id) {
                    throw new InvalidArgumentException($errorMessage);
                }
            };

            $validateCompanyRelation(
                relationId: $product->category_id,
                modelClass: ProductCategory::class,
                errorMessage: 'Product category must exist in the same company.',
            );

            $validateCompanyRelation(
                relationId: $product->brand_id,
                modelClass: Brand::class,
                errorMessage: 'Product brand must exist in the same company.',
            );

            $validateCompanyRelation(
                relationId: $product->default_vat_profile_id,
                modelClass: VatProfile::class,
                errorMessage: 'Product default VAT profile must exist in the same company.',
            );
        };

        static::creating(function (self $product) use ($validateRelations) {
            $product->ulid = Str::ulid()->generate();

            if (auth()->check()) {
                $product->created_by = auth()->id();
                $product->updated_by = auth()->id();
            }

            $validateRelations($product);
        });

        static::updating(function (self $product) use ($validateRelations) {
            if (auth()->check()) {
                $product->updated_by = auth()->id();
            }

            $validateRelations($product);
        });

        static::deleting(function (self $product) {
            if (! auth()->check()) {
                return;
            }

            $product->deleted_by = auth()->id();
            $product->save();
        });
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

    public function defaultVatProfile()
    {
        return $this->belongsTo(VatProfile::class, 'default_vat_profile_id')->withTrashed();
    }

    public function baseProductUnit()
    {
        return $this->hasOne(ProductUnit::class)->where('is_base', true);
    }

    public function productUnits()
    {
        return $this->hasMany(ProductUnit::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function mainImage()
    {
        return $this->hasOne(ProductImage::class)
            ->orderByDesc('is_main')
            ->orderBy('id');
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
}
