<?php

namespace App\Models;

use App\Traits\BootableModel;
use Illuminate\Database\Eloquent\Model;

use App\Models\Company;
use App\Models\Product;

class ProductGroup extends Model
{
    use BootableModel;

    protected $fillable = [
        'company_id',
        'code',
        'name',
        'category',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
