<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductBrand extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name'
    ];

    protected $hidden = [
        'created_by',
        'updated_by',
        'deleted_by'
    ];
}
