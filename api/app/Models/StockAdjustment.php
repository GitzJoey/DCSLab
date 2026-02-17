<?php

namespace App\Models;

use App\Traits\BootableModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockAdjustment extends Model
{
    use BootableModel;
    use HasFactory;
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
        'total_incoming_product_qty',
        'total_incoming_product_cogs',
        'total_outgoing_product_qty',
    ];

    protected $casts = [
        'is_posted' => 'boolean',
        'date' => 'datetime',
        'total_incoming_product_qty' => 'decimal:8',
        'total_incoming_product_cogs' => 'decimal:8',
        'total_outgoing_product_qty' => 'decimal:8',
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

    public function inProducts()
    {
        return $this->hasMany(StockAdjustmentInProduct::class);
    }

    public function outProducts()
    {
        return $this->hasMany(StockAdjustmentOutProduct::class);
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($query) use ($search) {
            $query->where('code', 'like', '%'.$search.'%')
                ->orWhere('remarks', 'like', '%'.$search.'%');
        });
    }
}
