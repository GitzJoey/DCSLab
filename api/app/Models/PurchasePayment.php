<?php

namespace App\Models;

use App\Traits\ScopeableByBranch;
use App\Traits\ScopeableByCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use InvalidArgumentException;

class PurchasePayment extends Model
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
        $validateRelations = static function (self $purchasePayment): void {
            $validateCompanyRelation = static function (
                ?int $relationId,
                string $modelClass,
                string $errorMessage
            ) use ($purchasePayment): void {
                if (is_null($relationId)) {
                    return;
                }

                $relation = $modelClass::find($relationId);

                if (! $relation || (int) $relation->company_id !== (int) $purchasePayment->company_id) {
                    throw new InvalidArgumentException($errorMessage);
                }
            };

            $validateCompanyRelation(
                relationId: $purchasePayment->branch_id,
                modelClass: Branch::class,
                errorMessage: 'Purchase payment branch must exist in the same company.',
            );

            $validateCompanyRelation(
                relationId: $purchasePayment->purchase_id,
                modelClass: Purchase::class,
                errorMessage: 'Purchase payment purchase must exist in the same company.',
            );

            $validateCompanyRelation(
                relationId: $purchasePayment->cash_account_id,
                modelClass: CashAccount::class,
                errorMessage: 'Purchase payment cash account must exist in the same company.',
            );
        };

        static::creating(function (self $purchasePayment) use ($validateRelations) {
            $purchasePayment->ulid = Str::ulid()->generate();

            if (auth()->check()) {
                $purchasePayment->created_by = auth()->id();
                $purchasePayment->updated_by = auth()->id();
            }

            $validateRelations($purchasePayment);
        });

        static::updating(function (self $purchasePayment) use ($validateRelations) {
            if (auth()->check()) {
                $purchasePayment->updated_by = auth()->id();
            }

            $validateRelations($purchasePayment);
        });

        static::deleting(function (self $purchasePayment) {
            if (! auth()->check()) {
                return;
            }

            $purchasePayment->deleted_by = auth()->id();
            $purchasePayment->save();
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

    public function cashAccount()
    {
        return $this->belongsTo(CashAccount::class)->withTrashed();
    }

    public function cashTransaction()
    {
        return $this->morphOne(CashTransaction::class, 'referable');
    }
}
