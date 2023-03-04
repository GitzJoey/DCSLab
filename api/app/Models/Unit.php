<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $fillable = [
        'company_id',
        'code',
        'name',
        'description',
        'category',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function productUnits()
    {
        return $this->hasMany(ProductUnit::class);
    }
}
