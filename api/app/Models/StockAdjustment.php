<?php

namespace App\Models;

use App\Traits\BootableModel;
use App\Traits\ScopeableByBranch;
use App\Traits\ScopeableByCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockAdjustment extends Model
{
    use BootableModel;
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

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($query) use ($search) {
            $query->where('code', 'like', '%'.$search.'%')
                ->orWhere('remarks', 'like', '%'.$search.'%');
        });
    }
}
