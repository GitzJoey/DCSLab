<?php

namespace App\Models;

use App\Traits\BootableModel;
use App\Traits\ScopeableByCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Investor extends Model
{
    use BootableModel;
    use HasFactory;
    use ScopeableByCompany;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'code',
        'name',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
        ];
    }

    public function company()
    {
        return $this->belongsTo(Company::class)->withTrashed();
    }

    public function capitalAdditions()
    {
        return $this->hasMany(CapitalAddition::class);
    }

    public function capitalWithdrawals()
    {
        return $this->hasMany(CapitalWithdrawal::class);
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($query) use ($search) {
            $query->where('investors.code', 'like', '%'.$search.'%')
                ->orWhere('investors.name', 'like', '%'.$search.'%')
                ->orWhere('investors.remarks', 'like', '%'.$search.'%');
        });
    }
}
