<?php

namespace App\Models;

use App\Enums\ChartOfAccountScopeEnum;
use App\Enums\ChartOfAccountSystemKeyEnum;
use App\Traits\ScopeableByCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use InvalidArgumentException;

class ChartOfAccount extends Model
{
    use HasFactory;
    use ScopeableByCompany;

    protected $fillable = [
        'company_id',
        'scope',
        'system_key',
        'parent_id',
        'source_type',
        'source_id',
        'code',
        'name',
        'account_type',
        'normal_balance',
        'level',
        'is_group',
        'is_active',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'scope' => ChartOfAccountScopeEnum::class,
            'system_key' => ChartOfAccountSystemKeyEnum::class,
            'level' => 'integer',
            'is_group' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $chartOfAccount) {
            $chartOfAccount->ulid = Str::ulid()->generate();

            if (auth()->check()) {
                $chartOfAccount->created_by = auth()->id();
                $chartOfAccount->updated_by = auth()->id();
            }

            if ($chartOfAccount->parent_id) {
                if ((int) $chartOfAccount->parent_id === (int) $chartOfAccount->id) {
                    throw new InvalidArgumentException('Parent chart of account must not reference itself.');
                }

                $parent = self::query()->find($chartOfAccount->parent_id);

                if (! $parent || (int) $parent->company_id !== (int) $chartOfAccount->company_id) {
                    throw new InvalidArgumentException('Parent chart of account must exist in the same company.');
                }
            }
        });

        static::updating(function (self $chartOfAccount) {
            if (auth()->check()) {
                $chartOfAccount->updated_by = auth()->id();
            }

            if ($chartOfAccount->parent_id) {
                if ((int) $chartOfAccount->parent_id === (int) $chartOfAccount->id) {
                    throw new InvalidArgumentException('Parent chart of account must not reference itself.');
                }

                $parent = self::query()->find($chartOfAccount->parent_id);

                if (! $parent || (int) $parent->company_id !== (int) $chartOfAccount->company_id) {
                    throw new InvalidArgumentException('Parent chart of account must exist in the same company.');
                }
            }
        });
    }

    public function company()
    {
        return $this->belongsTo(Company::class)->withTrashed();
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id')
            ->orderBy('code')
            ->orderBy('id');
    }

    public function childrenRecursive()
    {
        return $this->hasMany(self::class, 'parent_id')
            ->with([
                'parent',
                'childrenRecursive',
            ])
            ->orderBy('code')
            ->orderBy('id');
    }

    public function source()
    {
        return $this->morphTo();
    }
}
