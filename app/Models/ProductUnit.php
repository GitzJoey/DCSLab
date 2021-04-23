<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductUnit extends Model
{
    use HasFactory;

    protected $fillable = [
        'name'
    ];

    protected $hidden = [
        'symbol',
        'status',
        'remarks',
        'created_by',
        'updated_by',
        'deleted_by',
        'created_at',
        'update_at',
        'deleted_at'
    ];
}
