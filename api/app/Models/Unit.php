<?php

namespace App\Models;

use App\Enums\UnitTypeEnum;
use App\Traits\BootableModel;
use App\Traits\ScopeableByCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Unit extends Model
{
    use BootableModel;
    use HasFactory;
    use ScopeableByCompany;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'code',
        'name',
        'description',
        'type',
    ];

    protected function casts(): array
    {
        return [
            'type' => UnitTypeEnum::class,
        ];
    }

    public function company()
    {
        return $this->belongsTo(Company::class)->withTrashed();
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($query) use ($search) {
            $query->where('units.code', 'like', '%'.$search.'%')
                ->orWhere('units.name', 'like', '%'.$search.'%')
                ->orWhere('units.description', 'like', '%'.$search.'%');
        });
    }
}
