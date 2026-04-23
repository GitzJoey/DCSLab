<?php

namespace App\Models;

use App\Traits\BootableModel;
use App\Traits\ScopeableByCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class IncomeCategory extends Model
{
    use BootableModel;
    use HasFactory;
    use ScopeableByCompany;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'parent_id',
        'code',
        'name',
        'sequence',
    ];

    protected function casts(): array
    {
        return [
            'sequence' => 'integer',
        ];
    }

    public function company()
    {
        return $this->belongsTo(Company::class)->withTrashed();
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id')->withTrashed();
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id')
            ->orderBy('sequence')
            ->orderBy('id');
    }

    public function childrenRecursive()
    {
        return $this->hasMany(self::class, 'parent_id')
            ->with([
                'parent',
                'childrenRecursive',
            ])
            ->orderBy('sequence')
            ->orderBy('id');
    }

    public function getDisplayCodeAttribute(): string
    {
        $segments = [$this->code];
        $visitedIds = [$this->id => true];
        $parent = $this->relationLoaded('parent') ? $this->parent : $this->parent()->first();

        while ($parent) {
            if (isset($visitedIds[$parent->id])) {
                break;
            }

            array_unshift($segments, $parent->code);
            $visitedIds[$parent->id] = true;
            $parent = $parent->relationLoaded('parent') ? $parent->parent : $parent->parent()->first();
        }

        return implode('.', $segments);
    }
}
