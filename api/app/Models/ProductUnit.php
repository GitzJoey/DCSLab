<?php

namespace App\Models;

use App\Traits\ScopeableByCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use InvalidArgumentException;

class ProductUnit extends Model
{
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

    protected static function booted(): void
    {
        $validateRelations = static function (self $productUnit): void {
            $validateCompanyRelation = static function (
                ?int $relationId,
                string $modelClass,
                string $errorMessage
            ) use ($productUnit): void {
                if (is_null($relationId)) {
                    return;
                }

                $relation = $modelClass::find($relationId);

                if (! $relation || (int) $relation->company_id !== (int) $productUnit->company_id) {
                    throw new InvalidArgumentException($errorMessage);
                }
            };

            $validateCompanyRelation(
                relationId: $productUnit->product_id,
                modelClass: Product::class,
                errorMessage: 'Product unit product must exist in the same company.',
            );

            $validateCompanyRelation(
                relationId: $productUnit->unit_id,
                modelClass: Unit::class,
                errorMessage: 'Product unit unit must exist in the same company.',
            );
        };

        static::creating(function (self $productUnit) use ($validateRelations) {
            $productUnit->ulid = Str::ulid()->generate();

            if (auth()->check()) {
                $productUnit->created_by = auth()->id();
                $productUnit->updated_by = auth()->id();
            }

            $validateRelations($productUnit);
        });

        static::updating(function (self $productUnit) use ($validateRelations) {
            if (auth()->check()) {
                $productUnit->updated_by = auth()->id();
            }

            $validateRelations($productUnit);
        });

        static::deleting(function (self $productUnit) {
            if (! auth()->check()) {
                return;
            }

            $productUnit->deleted_by = auth()->id();
            $productUnit->save();
        });
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

    public function stockTransferItems()
    {
        return $this->hasMany(StockTransferItem::class);
    }
}
