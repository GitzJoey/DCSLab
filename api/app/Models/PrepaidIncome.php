<?php

namespace App\Models;

use App\Traits\ScopeableByBranch;
use App\Traits\ScopeableByCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use InvalidArgumentException;

class PrepaidIncome extends Model
{
    use HasFactory;
    use ScopeableByBranch;
    use ScopeableByCompany;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'branch_id',
        'code',
        'date',
        'income_category_id',
        'estimated_useful_life',
        'paid_immediately_cash_account_id',
        'amount_paid_immediately',
        'amount_receivable',
        'due_days',
        'amount_receivable_paid',
        'amount_receivable_due',
        'is_amount_receivable_paid_off',
        'amount_total',
        'remarks',
    ];

    protected $casts = [
        'date' => 'datetime',
        'estimated_useful_life' => 'integer',
        'amount_paid_immediately' => 'decimal:8',
        'amount_receivable' => 'decimal:8',
        'due_days' => 'integer',
        'amount_receivable_paid' => 'decimal:8',
        'amount_receivable_due' => 'decimal:8',
        'is_amount_receivable_paid_off' => 'boolean',
        'amount_total' => 'decimal:8',
    ];

    protected static function booted(): void
    {
        $validateRelations = static function (self $prepaidIncome): void {
            $validateCompanyRelation = static function (
                ?int $relationId,
                string $modelClass,
                string $errorMessage
            ) use ($prepaidIncome): void {
                if (is_null($relationId)) {
                    return;
                }

                $relation = $modelClass::find($relationId);

                if (! $relation || (int) $relation->company_id !== (int) $prepaidIncome->company_id) {
                    throw new InvalidArgumentException($errorMessage);
                }
            };

            $validateCompanyRelation(
                relationId: $prepaidIncome->branch_id,
                modelClass: Branch::class,
                errorMessage: 'Prepaid income branch must exist in the same company.',
            );

            $validateCompanyRelation(
                relationId: $prepaidIncome->income_category_id,
                modelClass: IncomeCategory::class,
                errorMessage: 'Prepaid income category must exist in the same company.',
            );

            $validateCompanyRelation(
                relationId: $prepaidIncome->paid_immediately_cash_account_id,
                modelClass: CashAccount::class,
                errorMessage: 'Prepaid income paid immediately cash account must exist in the same company.',
            );
        };

        static::creating(function (self $prepaidIncome) use ($validateRelations) {
            $prepaidIncome->ulid = Str::ulid()->generate();

            if (auth()->check()) {
                $prepaidIncome->created_by = auth()->id();
                $prepaidIncome->updated_by = auth()->id();
            }

            $validateRelations($prepaidIncome);
        });

        static::updating(function (self $prepaidIncome) use ($validateRelations) {
            if (auth()->check()) {
                $prepaidIncome->updated_by = auth()->id();
            }

            $validateRelations($prepaidIncome);
        });

        static::deleting(function (self $prepaidIncome) {
            if (! auth()->check()) {
                return;
            }

            $prepaidIncome->deleted_by = auth()->id();
            $prepaidIncome->save();
        });
    }

    public function company()
    {
        return $this->belongsTo(Company::class)->withTrashed();
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class)->withTrashed();
    }

    public function category()
    {
        return $this->belongsTo(IncomeCategory::class, 'income_category_id')->withTrashed();
    }

    public function paidImmediatelyCashAccount()
    {
        return $this->belongsTo(CashAccount::class, 'paid_immediately_cash_account_id')->withTrashed();
    }

    public function payments()
    {
        return $this->hasMany(PrepaidIncomePayment::class);
    }

    public function images()
    {
        return $this->hasMany(PrepaidIncomeImage::class);
    }

    public function mainImage()
    {
        return $this->hasOne(PrepaidIncomeImage::class)
            ->orderByDesc('is_main')
            ->orderBy('id');
    }

    public function cashTransaction()
    {
        return $this->morphOne(CashTransaction::class, 'referable');
    }
}
