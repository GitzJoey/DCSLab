<?php

namespace App\Models;

use App\Enums\RecordStatusEnum;
use App\Traits\ScopeableByCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use InvalidArgumentException;

class Warehouse extends Model
{
    use HasFactory;
    use ScopeableByCompany;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'branch_id',
        'code',
        'name',
        'address',
        'city',
        'contact',
        'remarks',
        'status',
    ];

    protected $casts = [
        'status' => RecordStatusEnum::class,
    ];

    protected static function booted(): void
    {
        $validateRelations = static function (self $warehouse): void {
            if (is_null($warehouse->branch_id)) {
                return;
            }

            $branch = Branch::find($warehouse->branch_id);

            if (! $branch || (int) $branch->company_id !== (int) $warehouse->company_id) {
                throw new InvalidArgumentException('Warehouse branch must exist in the same company.');
            }
        };

        static::creating(function (self $warehouse) use ($validateRelations) {
            $warehouse->ulid = Str::ulid()->generate();

            if (auth()->check()) {
                $warehouse->created_by = auth()->id();
                $warehouse->updated_by = auth()->id();
            }

            $validateRelations($warehouse);
        });

        static::updating(function (self $warehouse) use ($validateRelations) {
            if (auth()->check()) {
                $warehouse->updated_by = auth()->id();
            }

            $validateRelations($warehouse);
        });

        static::deleting(function (self $warehouse) {
            if (! auth()->check()) {
                return;
            }

            $warehouse->deleted_by = auth()->id();
            $warehouse->save();
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

    public function stockTransferSource()
    {
        return $this->hasMany(StockTransfer::class, 'source_warehouse_id');
    }

    public function stockTransferDestination()
    {
        return $this->hasMany(StockTransfer::class, 'destination_warehouse_id');
    }

    public function stockTransactions()
    {
        return $this->hasMany(StockTransaction::class);
    }
}
