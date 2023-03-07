<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{

    protected $fillable = [
        'company_id',
        'branch_id',
        'code',
        'name',
        'address',
        'city',
        'contact',
        'status',
        'remarks',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
