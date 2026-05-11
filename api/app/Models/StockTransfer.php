<?php

namespace App\Models;

use App\Traits\ScopeableByBranch;
use App\Traits\ScopeableByCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use InvalidArgumentException;

class StockTransfer extends Model
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
        'source_warehouse_id',
        'destination_warehouse_id',
        'remarks',
        'is_posted',
    ];

    protected $casts = [
        'date' => 'datetime',
        'is_posted' => 'boolean',
    ];

    protected static function booted(): void
    {
        $validateRelations = static function (self $stockTransfer): void {
            $validateCompanyRelation = static function (
                ?int $relationId,
                string $modelClass,
                string $errorMessage
            ) use ($stockTransfer): void {
                if (is_null($relationId)) {
                    return;
                }

                $relation = $modelClass::find($relationId);

                if (! $relation || (int) $relation->company_id !== (int) $stockTransfer->company_id) {
                    throw new InvalidArgumentException($errorMessage);
                }
            };

            $validateCompanyRelation(
                relationId: $stockTransfer->branch_id,
                modelClass: Branch::class,
                errorMessage: 'Stock transfer branch must exist in the same company.',
            );

            $validateCompanyRelation(
                relationId: $stockTransfer->source_warehouse_id,
                modelClass: Warehouse::class,
                errorMessage: 'Stock transfer source warehouse must exist in the same company.',
            );

            $validateCompanyRelation(
                relationId: $stockTransfer->destination_warehouse_id,
                modelClass: Warehouse::class,
                errorMessage: 'Stock transfer destination warehouse must exist in the same company.',
            );
        };

        static::creating(function (self $stockTransfer) use ($validateRelations) {
            $stockTransfer->ulid = Str::ulid()->generate();

            if (auth()->check()) {
                $stockTransfer->created_by = auth()->id();
                $stockTransfer->updated_by = auth()->id();
            }

            $validateRelations($stockTransfer);
        });

        static::updating(function (self $stockTransfer) use ($validateRelations) {
            if (auth()->check()) {
                $stockTransfer->updated_by = auth()->id();
            }

            $validateRelations($stockTransfer);
        });

        static::deleting(function (self $stockTransfer) {
            if (! auth()->check()) {
                return;
            }

            $stockTransfer->deleted_by = auth()->id();
            $stockTransfer->save();
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

    public function sourceWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'source_warehouse_id')->withTrashed();
    }

    public function destinationWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'destination_warehouse_id')->withTrashed();
    }

    public function stockTransferItems()
    {
        return $this->hasMany(StockTransferItem::class);
    }
}
