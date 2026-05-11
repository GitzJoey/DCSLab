<?php

namespace App\Models;

use App\Traits\ScopeableByCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use InvalidArgumentException;

class ExpenseCategory extends Model
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
        $validateParent = static function (self $expenseCategory): void {
            if (! $expenseCategory->parent_id) {
                return;
            }

            if ((int) $expenseCategory->parent_id === (int) $expenseCategory->id) {
                throw new InvalidArgumentException('Expense category parent must not reference itself.');
            }

            $parent = self::find($expenseCategory->parent_id);

            if (! $parent || (int) $parent->company_id !== (int) $expenseCategory->company_id) {
                throw new InvalidArgumentException('Expense category parent must exist in the same company.');
            }
        };

        static::creating(function (self $expenseCategory) use ($validateParent) {
            $expenseCategory->ulid = Str::ulid()->generate();

            if (auth()->check()) {
                $expenseCategory->created_by = auth()->id();
                $expenseCategory->updated_by = auth()->id();
            }

            $validateParent($expenseCategory);
        });

        static::updating(function (self $expenseCategory) use ($validateParent) {
            if (auth()->check()) {
                $expenseCategory->updated_by = auth()->id();
            }

            $validateParent($expenseCategory);
        });

        static::deleting(function (self $expenseCategory) {
            if (! auth()->check()) {
                return;
            }

            $expenseCategory->deleted_by = auth()->id();
            $expenseCategory->save();
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

    public function chartOfAccount()
    {
        return $this->morphOne(ChartOfAccount::class, 'source');
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
