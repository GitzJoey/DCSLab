<?php

namespace App\Models;

use App\Enums\ProductCategoryTypeEnum;
use App\Traits\BootableModel;
use App\Traits\ScopeableByCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductCategory extends Model
{
    use BootableModel;
    use HasFactory;
    use ScopeableByCompany;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'code',
        'name',
        'type',
    ];

    protected function casts(): array
    {
        return [
            'type' => ProductCategoryTypeEnum::class,
        ];
    }

    public function company()
    {
        return $this->belongsTo(Company::class)->withTrashed();
    }

    public function products()
    {
        return $this->hasMany(Product::class)->withTrashed();
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($query) use ($search) {
            $query->where('product_categories.code', 'like', '%'.$search.'%')
                ->orWhere('product_categories.name', 'like', '%'.$search.'%');
        });
    }
}
