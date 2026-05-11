<?php

namespace App\Models;

use App\Traits\ScopeableByBranch;
use App\Traits\ScopeableByCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use InvalidArgumentException;

class Debt extends Model
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
        'category_id',
        'creditor_id',
        'supplier_id',
        'cash_account_id',
        'direct_amount_received',
        'opening_amount_due',
        'amount_total',
        'amount_paid_by_cash_account',
        'amount_paid_by_stock_adjustment',
        'amount_due',
        'due_days',
        'is_paid_off',
        'remarks',
    ];

    protected $casts = [
        'date' => 'datetime',
        'direct_amount_received' => 'decimal:8',
        'opening_amount_due' => 'decimal:8',
        'amount_total' => 'decimal:8',
        'amount_paid_by_cash_account' => 'decimal:8',
        'amount_paid_by_stock_adjustment' => 'decimal:8',
        'amount_due' => 'decimal:8',
        'due_days' => 'integer',
        'is_paid_off' => 'boolean',
    ];

    protected static function booted(): void
    {
        $validateRelations = static function (self $debt): void {
            $validateCompanyRelation = static function (
                ?int $relationId,
                string $modelClass,
                string $errorMessage
            ) use ($debt): void {
                if (is_null($relationId)) {
                    return;
                }

                $relation = $modelClass::find($relationId);

                if (! $relation || (int) $relation->company_id !== (int) $debt->company_id) {
                    throw new InvalidArgumentException($errorMessage);
                }
            };

            $validateCompanyRelation(
                relationId: $debt->branch_id,
                modelClass: Branch::class,
                errorMessage: 'Debt branch must exist in the same company.',
            );

            $validateCompanyRelation(
                relationId: $debt->category_id,
                modelClass: DebtCategory::class,
                errorMessage: 'Debt category must exist in the same company.',
            );

            $validateCompanyRelation(
                relationId: $debt->creditor_id,
                modelClass: DebtCreditor::class,
                errorMessage: 'Debt creditor must exist in the same company.',
            );

            $validateCompanyRelation(
                relationId: $debt->supplier_id,
                modelClass: Supplier::class,
                errorMessage: 'Debt supplier must exist in the same company.',
            );

            $validateCompanyRelation(
                relationId: $debt->cash_account_id,
                modelClass: CashAccount::class,
                errorMessage: 'Debt cash account must exist in the same company.',
            );
        };

        static::creating(function (self $debt) use ($validateRelations) {
            $debt->ulid = Str::ulid()->generate();

            if (auth()->check()) {
                $debt->created_by = auth()->id();
                $debt->updated_by = auth()->id();
            }

            $validateRelations($debt);
        });

        static::updating(function (self $debt) use ($validateRelations) {
            if (auth()->check()) {
                $debt->updated_by = auth()->id();
            }

            $validateRelations($debt);
        });

        static::deleting(function (self $debt) {
            if (! auth()->check()) {
                return;
            }

            $debt->deleted_by = auth()->id();
            $debt->save();
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
        return $this->belongsTo(DebtCategory::class, 'category_id')->withTrashed();
    }

    public function creditor()
    {
        return $this->belongsTo(DebtCreditor::class)->withTrashed();
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class)->withTrashed();
    }

    public function cashAccount()
    {
        return $this->belongsTo(CashAccount::class)->withTrashed();
    }

    public function payments()
    {
        return $this->hasMany(DebtPayment::class);
    }

    public function cashTransaction()
    {
        return $this->morphOne(CashTransaction::class, 'referable');
    }
}
