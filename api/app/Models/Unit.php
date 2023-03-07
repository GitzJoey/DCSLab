<?php

namespace App\Models;

use App\Traits\BootableModel;
use Illuminate\Database\Eloquent\Model;

use App\Models\Company;
use App\Models\ProductUnit;

class Unit extends Model
{
    use BootableModel;

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
