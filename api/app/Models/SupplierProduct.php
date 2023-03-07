<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierProduct extends Model
{
    protected $table = 'supplier_products';

    protected $fillable = [
        'main_product',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
