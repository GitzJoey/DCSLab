<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'referable_type',
        'referable_id',
        'date',
        'warehouse_id',
        'product_id',
        'base_qty',
    ];

    protected $casts = [
        'date' => 'datetime',
        'base_qty' => 'decimal:8',
    ];

    public function referable()
    {
        return $this->morphTo();
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
