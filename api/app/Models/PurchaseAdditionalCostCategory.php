<?php

namespace App\Models;

use App\Traits\BootableModel;
use App\Traits\ScopeableByCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseAdditionalCostCategory extends Model
{
    use BootableModel;
    use HasFactory;
    use ScopeableByCompany;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'code',
        'name',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function additionalCosts()
    {
        return $this->hasMany(PurchaseAdditionalCost::class);
    }
}
