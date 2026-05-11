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
        $validateParent = static function (self $chartOfAccount): void {
            if (! $chartOfAccount->parent_id) {
                return;
            }

            if ((int) $chartOfAccount->parent_id === (int) $chartOfAccount->id) {
                throw new InvalidArgumentException('Parent chart of account must not reference itself.');
            }

            $parent = self::find($chartOfAccount->parent_id);

            if (! $parent || (int) $parent->company_id !== (int) $chartOfAccount->company_id) {
                throw new InvalidArgumentException('Parent chart of account must exist in the same company.');
            }
        };

        $validateAccountType = static function (self $chartOfAccount): void {
            $isRoot = is_null($chartOfAccount->parent_id);

            if ($isRoot && is_null($chartOfAccount->account_type)) {
                throw new InvalidArgumentException('Account type for root chart of account must be provided by the system.');
            }
        };

        static::creating(function (self $chartOfAccount) use ($validateParent, $validateAccountType) {
            $chartOfAccount->ulid = Str::ulid()->generate();

            if (auth()->check()) {
                $chartOfAccount->created_by = auth()->id();
                $chartOfAccount->updated_by = auth()->id();
            }

            $validateParent($chartOfAccount);
            $validateAccountType($chartOfAccount);
        });

        static::updating(function (self $chartOfAccount) use ($validateParent, $validateAccountType) {
            if (auth()->check()) {
                $chartOfAccount->updated_by = auth()->id();
            }

            $validateParent($chartOfAccount);
            $validateAccountType($chartOfAccount);
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
