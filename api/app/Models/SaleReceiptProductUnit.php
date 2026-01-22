<?php

namespace App\Models;

use App\Traits\BootableModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SaleReceiptProductUnit extends Model
{
    use BootableModel;
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'branch_id',
        'sale_receipt_id',
        'qty',
        'product_id',
        'product_unit_id',
        'product_unit_amount_per_unit',
        'product_unit_amount_total',
        'is_has_sale',
    ];

    protected $casts = [
        'qty' => 'integer',
        'product_unit_amount_per_unit' => 'decimal:8',
        'product_unit_amount_total' => 'decimal:8',
        'is_has_sale' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function saleReceipt()
    {
        return $this->belongsTo(SaleReceipt::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function productUnit()
    {
        return $this->belongsTo(ProductUnit::class);
    }

    public function saleReceiptProductUnitSerials()
    {
        return $this->hasMany(SaleReceiptProductUnitSerial::class);
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where('code', 'like', '%'.$search.'%')
            ->orWhere('remarks', 'like', '%'.$search.'%');
    }
}
