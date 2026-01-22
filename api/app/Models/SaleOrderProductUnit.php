<?php

namespace App\Models;

use App\Traits\BootableModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SaleOrderProductUnit extends Model
{
    use BootableModel;
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'branch_id',
        'sale_order_id',

        'qty',
        'product_id',
        'product_unit_id',
        'product_unit_amount_per_unit',
        'product_unit_amount_total',
        'product_unit_initial_price',
        'product_unit_discount_rate1',
        'product_unit_discount_rate2',
        'product_unit_discount_rate3',
        'product_unit_discount_rate4',
        'product_unit_discount_rate5',
        'product_unit_discount_fixed1',
        'product_unit_discount_fixed2',
        'product_unit_discount_fixed3',
        'product_unit_discount_fixed4',
        'product_unit_discount_fixed5',
        'product_unit_net_price',
        'product_unit_subtotal',
        'product_unit_subtotal_discount_rate',
        'product_unit_subtotal_discount_fixed',
        'product_unit_total',
        'product_unit_global_discount_rate',
        'product_unit_global_discount_fixed',
        'product_unit_grand_total',

        'product_is_taxable',
        'product_vat_rate',
        'product_price_include_vat',
        'product_vat_base',
        'product_vat',

        'product_unit_final_price',
        'product_final_price_base_unit',

        'it_has_sale',
        'it_sent',
    ];

    protected $casts = [
        'qty' => 'integer',
        'product_unit_amount_per_unit' => 'decimal:8',
        'product_unit_amount_total' => 'decimal:8',
        'product_unit_initial_price' => 'decimal:8',
        'product_unit_discount_rate1' => 'decimal:8',
        'product_unit_discount_rate2' => 'decimal:8',
        'product_unit_discount_rate3' => 'decimal:8',
        'product_unit_discount_rate4' => 'decimal:8',
        'product_unit_discount_rate5' => 'decimal:8',
        'product_unit_discount_fixed1' => 'decimal:8',
        'product_unit_discount_fixed2' => 'decimal:8',
        'product_unit_discount_fixed3' => 'decimal:8',
        'product_unit_discount_fixed4' => 'decimal:8',
        'product_unit_discount_fixed5' => 'decimal:8',
        'product_unit_net_price' => 'decimal:8',
        'product_unit_subtotal' => 'decimal:8',
        'product_unit_subtotal_discount_rate' => 'decimal:8',
        'product_unit_subtotal_discount_fixed' => 'decimal:8',
        'product_unit_total' => 'decimal:8',
        'product_unit_global_discount_rate' => 'decimal:8',
        'product_unit_global_discount_fixed' => 'decimal:8',
        'product_unit_grand_total' => 'decimal:8',

        'product_is_taxable' => 'boolean',
        'product_vat_rate' => 'decimal:8',
        'product_price_include_vat' => 'boolean',
        'product_vat_base' => 'decimal:8',
        'product_vat' => 'decimal:8',

        'product_unit_final_price' => 'decimal:8',
        'product_final_price_base_unit' => 'decimal:8',

        'it_has_sale' => 'boolean',
        'it_sent' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function saleOrder()
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function productUnit()
    {
        return $this->belongsTo(ProductUnit::class);
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where('code', 'like', '%'.$search.'%')
            ->orWhere('remarks', 'like', '%'.$search.'%');
    }
}
