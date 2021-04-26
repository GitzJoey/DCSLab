<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Vinkla\Hashids\Facades\Hashids;

class ProductBrand extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name'
    ];

    protected $hidden = [
        'id',
        'created_by',
        'updated_by',
        'deleted_by',
        'created_at',
        'updated_at'
    ];

    protected $appends = ['hId'];

    public function getHIdAttribute() : string
    {
        return hashIDs::encode($this->attributes['id']);
    }
}
