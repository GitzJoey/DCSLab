<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'path',
        'hash',
        'is_thumbnail',
    ];

    protected function casts(): array
    {
        return [
            'is_thumbnail' => 'boolean',
        ];
    }

    public function product()
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }
}
