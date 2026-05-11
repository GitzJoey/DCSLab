<?php

namespace App\Models;

use App\Traits\ScopeableByBranch;
use App\Traits\ScopeableByCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use InvalidArgumentException;

class PurchaseAdditionalCostPayment extends Model
{
    use HasFactory;
    use ScopeableByBranch;
    use ScopeableByCompany;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'branch_id',
        'purchase_additional_cost_id',
        'code',
        'date',
        'cash_account_id',
        'amount',
        'remarks',
    ];

    protected $casts = [
        'date' => 'datetime',
        'amount' => 'decimal:8',
    ];

    protected static function booted(): void
    {
        $validateRelations = static function (self $purchaseAdditionalCostPayment): void {
            $validateCompanyRelation = static function (
                ?int $relationId,
                string $modelClass,
                string $errorMessage
            ) use ($purchaseAdditionalCostPayment): void {
                if (is_null($relationId)) {
                    return;
                }

                $relation = $modelClass::find($relationId);

                if (! $relation || (int) $relation->company_id !== (int) $purchaseAdditionalCostPayment->company_id) {
                    throw new InvalidArgumentException($errorMessage);
                }
            };

            $validateCompanyRelation(
                relationId: $purchaseAdditionalCostPayment->branch_id,
                modelClass: Branch::class,
                errorMessage: 'Purchase additional cost payment branch must exist in the same company.',
            );

            $validateCompanyRelation(
                relationId: $purchaseAdditionalCostPayment->purchase_additional_cost_id,
                modelClass: PurchaseAdditionalCost::class,
                errorMessage: 'Purchase additional cost payment purchase additional cost must exist in the same company.',
            );

            $validateCompanyRelation(
                relationId: $purchaseAdditionalCostPayment->cash_account_id,
                modelClass: CashAccount::class,
                errorMessage: 'Purchase additional cost payment cash account must exist in the same company.',
            );
        };

        static::creating(function (self $purchaseAdditionalCostPayment) use ($validateRelations) {
            $purchaseAdditionalCostPayment->ulid = Str::ulid()->generate();

            if (auth()->check()) {
                $purchaseAdditionalCostPayment->created_by = auth()->id();
                $purchaseAdditionalCostPayment->updated_by = auth()->id();
            }

            $validateRelations($purchaseAdditionalCostPayment);
        });

        static::updating(function (self $purchaseAdditionalCostPayment) use ($validateRelations) {
            if (auth()->check()) {
                $purchaseAdditionalCostPayment->updated_by = auth()->id();
            }

            $validateRelations($purchaseAdditionalCostPayment);
        });

        static::deleting(function (self $purchaseAdditionalCostPayment) {
            if (! auth()->check()) {
                return;
            }

            $purchaseAdditionalCostPayment->deleted_by = auth()->id();
            $purchaseAdditionalCostPayment->save();
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

    public function purchaseAdditionalCost()
    {
        return $this->belongsTo(PurchaseAdditionalCost::class)->withTrashed();
    }

    public function cashAccount()
    {
        return $this->belongsTo(CashAccount::class)->withTrashed();
    }

    public function cashTransaction()
    {
        return $this->morphOne(CashTransaction::class, 'referable');
    }
}
