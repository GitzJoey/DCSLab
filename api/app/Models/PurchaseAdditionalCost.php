<?php

namespace App\Models;

use App\Traits\ScopeableByBranch;
use App\Traits\ScopeableByCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use InvalidArgumentException;

class PurchaseAdditionalCost extends Model
{
    use HasFactory;
    use ScopeableByBranch;
    use ScopeableByCompany;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'branch_id',
        'purchase_id',
        'code',
        'date',
        'due_days',
        'purchase_additional_cost_category_id',
        'paid_immediately_cash_account_id',
        'amount_paid_immediately',
        'amount_payable',
        'amount_payable_paid',
        'amount_payable_due',
        'is_amount_payable_paid_off',
        'amount_total',
        'remarks',
    ];

    protected $casts = [
        'date' => 'datetime',
        'due_days' => 'integer',
        'amount_paid_immediately' => 'decimal:8',
        'amount_payable' => 'decimal:8',
        'amount_payable_paid' => 'decimal:8',
        'amount_payable_due' => 'decimal:8',
        'is_amount_payable_paid_off' => 'boolean',
        'amount_total' => 'decimal:8',
    ];

    protected static function booted(): void
    {
        $validateRelations = static function (self $purchaseAdditionalCost): void {
            $validateCompanyRelation = static function (
                ?int $relationId,
                string $modelClass,
                string $errorMessage
            ) use ($purchaseAdditionalCost): void {
                if (is_null($relationId)) {
                    return;
                }

                $relation = $modelClass::find($relationId);

                if (! $relation || (int) $relation->company_id !== (int) $purchaseAdditionalCost->company_id) {
                    throw new InvalidArgumentException($errorMessage);
                }
            };

            $validateCompanyRelation(
                relationId: $purchaseAdditionalCost->branch_id,
                modelClass: Branch::class,
                errorMessage: 'Purchase additional cost branch must exist in the same company.',
            );

            $validateCompanyRelation(
                relationId: $purchaseAdditionalCost->purchase_id,
                modelClass: Purchase::class,
                errorMessage: 'Purchase additional cost purchase must exist in the same company.',
            );

            $validateCompanyRelation(
                relationId: $purchaseAdditionalCost->purchase_additional_cost_category_id,
                modelClass: PurchaseAdditionalCostCategory::class,
                errorMessage: 'Purchase additional cost category must exist in the same company.',
            );

            $validateCompanyRelation(
                relationId: $purchaseAdditionalCost->paid_immediately_cash_account_id,
                modelClass: CashAccount::class,
                errorMessage: 'Purchase additional cost paid immediately cash account must exist in the same company.',
            );
        };

        static::creating(function (self $purchaseAdditionalCost) use ($validateRelations) {
            $purchaseAdditionalCost->ulid = Str::ulid()->generate();

            if (auth()->check()) {
                $purchaseAdditionalCost->created_by = auth()->id();
                $purchaseAdditionalCost->updated_by = auth()->id();
            }

            $validateRelations($purchaseAdditionalCost);
        });

        static::updating(function (self $purchaseAdditionalCost) use ($validateRelations) {
            if (auth()->check()) {
                $purchaseAdditionalCost->updated_by = auth()->id();
            }

            $validateRelations($purchaseAdditionalCost);
        });

        static::deleting(function (self $purchaseAdditionalCost) {
            if (! auth()->check()) {
                return;
            }

            $purchaseAdditionalCost->deleted_by = auth()->id();
            $purchaseAdditionalCost->save();
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

    public function purchase()
    {
        return $this->belongsTo(Purchase::class)->withTrashed();
    }

    public function category()
    {
        return $this->belongsTo(PurchaseAdditionalCostCategory::class, 'purchase_additional_cost_category_id')->withTrashed();
    }

    public function paidImmediatelyCashAccount()
    {
        return $this->belongsTo(CashAccount::class, 'paid_immediately_cash_account_id')->withTrashed();
    }

    public function payments()
    {
        return $this->hasMany(PurchaseAdditionalCostPayment::class);
    }

    public function cashTransaction()
    {
        return $this->morphOne(CashTransaction::class, 'referable');
    }
}
