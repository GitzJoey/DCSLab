<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockSerialTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'referable_type',
        'referable_id',
        'date',
        'warehouse_id',
        'product_id',
        'direction',
        'serial',
    ];

    protected $casts = [
        'date' => 'datetime',
        'direction' => 'integer',
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
