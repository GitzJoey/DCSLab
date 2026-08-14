<?php

namespace App\Models;

use App\Traits\ScopeableByBranch;
use App\Traits\ScopeableByCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use InvalidArgumentException;

class Income extends Model
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
        $validateRelations = static function (self $income): void {
            $validateCompanyRelation = static function (
                ?int $relationId,
                string $modelClass,
                string $errorMessage
            ) use ($income): void {
                if (is_null($relationId)) {
                    return;
                }

                $relation = $modelClass::find($relationId);

                if (! $relation || (int) $relation->company_id !== (int) $income->company_id) {
                    throw new InvalidArgumentException($errorMessage);
                }
            };

            $validateCompanyRelation(
                relationId: $income->branch_id,
                modelClass: Branch::class,
                errorMessage: 'Income branch must exist in the same company.',
            );

            $validateCompanyRelation(
                relationId: $income->income_category_id,
                modelClass: IncomeCategory::class,
                errorMessage: 'Income category must exist in the same company.',
            );

            $validateCompanyRelation(
                relationId: $income->paid_immediately_cash_account_id,
                modelClass: CashAccount::class,
                errorMessage: 'Income paid immediately cash account must exist in the same company.',
            );
        };

        static::creating(function (self $income) use ($validateRelations) {
            $income->ulid = Str::ulid()->generate();

            if (auth()->check()) {
                $income->created_by = auth()->id();
                $income->updated_by = auth()->id();
            }

            $validateRelations($income);
        });

        static::updating(function (self $income) use ($validateRelations) {
            if (auth()->check()) {
                $income->updated_by = auth()->id();
            }

            $validateRelations($income);
        });

        static::deleting(function (self $income) {
            if (! auth()->check()) {
                return;
            }

            $income->deleted_by = auth()->id();
            $income->save();
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
        return $this->hasMany(IncomePayment::class);
    }

    public function images()
    {
        return $this->hasMany(IncomeImage::class);
    }

    public function mainImage()
    {
        return $this->hasOne(IncomeImage::class)
            ->orderByDesc('is_main')
            ->orderBy('id');
    }

    public function cashTransaction()
    {
        return $this->morphOne(CashTransaction::class, 'referable');
    }

    public function journalEntry()
    {
        return $this->morphOne(JournalEntry::class, 'source');
    }
}
