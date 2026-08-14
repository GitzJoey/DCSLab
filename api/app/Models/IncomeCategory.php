<?php

namespace App\Models;

use App\Traits\ScopeableByCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use InvalidArgumentException;

class IncomeCategory extends Model
{
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

    protected static function booted(): void
    {
        $validateParent = static function (self $incomeCategory): void {
            if (! $incomeCategory->parent_id) {
                return;
            }

            if ((int) $incomeCategory->parent_id === (int) $incomeCategory->id) {
                throw new InvalidArgumentException('Income category parent must not reference itself.');
            }

            $parent = self::find($incomeCategory->parent_id);

            if (! $parent || (int) $parent->company_id !== (int) $incomeCategory->company_id) {
                throw new InvalidArgumentException('Income category parent must exist in the same company.');
            }
        };

        static::creating(function (self $incomeCategory) use ($validateParent) {
            $incomeCategory->ulid = Str::ulid()->generate();

            if (auth()->check()) {
                $incomeCategory->created_by = auth()->id();
                $incomeCategory->updated_by = auth()->id();
            }

            $validateParent($incomeCategory);
        });

        static::updating(function (self $incomeCategory) use ($validateParent) {
            if (auth()->check()) {
                $incomeCategory->updated_by = auth()->id();
            }

            $validateParent($incomeCategory);
        });

        static::deleting(function (self $incomeCategory) {
            if (! auth()->check()) {
                return;
            }

            $incomeCategory->deleted_by = auth()->id();
            $incomeCategory->save();
        });
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
