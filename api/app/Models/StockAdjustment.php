<?php

namespace App\Models;

use App\Traits\ScopeableByBranch;
use App\Traits\ScopeableByCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use InvalidArgumentException;

class StockAdjustment extends Model
{
    use HasFactory;
    use ScopeableByBranch;
    use ScopeableByCompany;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'branch_id',
        'code',
        'date',
        'category_id',
        'in_warehouse_id',
        'out_warehouse_id',
        'remarks',
        'is_posted',
        'total_incoming_item_qty',
        'total_incoming_item_cogs',
        'total_outgoing_item_qty',
    ];

    protected $casts = [
        'is_posted' => 'boolean',
        'date' => 'datetime',
        'total_incoming_item_qty' => 'decimal:8',
        'total_incoming_item_cogs' => 'decimal:8',
        'total_outgoing_item_qty' => 'decimal:8',
    ];

    protected static function booted(): void
    {
        $validateRelations = static function (self $stockAdjustment): void {
            $validateCompanyRelation = static function (
                ?int $relationId,
                string $modelClass,
                string $errorMessage
            ) use ($stockAdjustment): void {
                if (is_null($relationId)) {
                    return;
                }

                $relation = $modelClass::find($relationId);

                if (! $relation || (int) $relation->company_id !== (int) $stockAdjustment->company_id) {
                    throw new InvalidArgumentException($errorMessage);
                }
            };

            $validateCompanyRelation(
                relationId: $stockAdjustment->branch_id,
                modelClass: Branch::class,
                errorMessage: 'Stock adjustment branch must exist in the same company.',
            );

            $validateCompanyRelation(
                relationId: $stockAdjustment->category_id,
                modelClass: StockAdjustmentCategory::class,
                errorMessage: 'Stock adjustment category must exist in the same company.',
            );

            $validateCompanyRelation(
                relationId: $stockAdjustment->in_warehouse_id,
                modelClass: Warehouse::class,
                errorMessage: 'Stock adjustment incoming warehouse must exist in the same company.',
            );

            $validateCompanyRelation(
                relationId: $stockAdjustment->out_warehouse_id,
                modelClass: Warehouse::class,
                errorMessage: 'Stock adjustment outgoing warehouse must exist in the same company.',
            );
        };

        static::creating(function (self $stockAdjustment) use ($validateRelations) {
            $stockAdjustment->ulid = Str::ulid()->generate();

            if (auth()->check()) {
                $stockAdjustment->created_by = auth()->id();
                $stockAdjustment->updated_by = auth()->id();
            }

            $validateRelations($stockAdjustment);
        });

        static::updating(function (self $stockAdjustment) use ($validateRelations) {
            if (auth()->check()) {
                $stockAdjustment->updated_by = auth()->id();
            }

            $validateRelations($stockAdjustment);
        });

        static::deleting(function (self $stockAdjustment) {
            if (! auth()->check()) {
                return;
            }

            $stockAdjustment->deleted_by = auth()->id();
            $stockAdjustment->save();
        });
    }

    public function company()
    {
        return $this->belongsTo(Company::class)->withTrashed();
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class)->withTrashed();
    }

    public function category()
    {
        return $this->belongsTo(StockAdjustmentCategory::class, 'category_id')->withTrashed();
    }

    public function inWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'in_warehouse_id')->withTrashed();
    }

    public function outWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'out_warehouse_id')->withTrashed();
    }

    public function inItems()
    {
        return $this->hasMany(StockAdjustmentInItem::class);
    }

    public function outItems()
    {
        return $this->hasMany(StockAdjustmentOutItem::class);
    }
}
