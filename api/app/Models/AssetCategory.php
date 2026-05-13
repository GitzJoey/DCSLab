<?php

namespace App\Models;

use App\Traits\BootableModel;
use App\Traits\ScopeableByCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssetCategory extends Model
{
    use BootableModel;
    use HasFactory;
    use ScopeableByCompany;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'code',
        'name',
        'estimated_useful_life_months',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'estimated_useful_life_months' => 'integer',
        ];
    }

    public function company()
    {
        return $this->belongsTo(Company::class)->withTrashed();
    }
}
