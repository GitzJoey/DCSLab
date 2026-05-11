<?php

namespace App\Models;

use App\Traits\ScopeableByBranch;
use App\Traits\ScopeableByCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use InvalidArgumentException;

class PurchaseReturnPayment extends Model
{
    use HasFactory;
    use ScopeableByBranch;
    use ScopeableByCompany;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'branch_id',
        'purchase_return_id',
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
        $validateRelations = static function (self $purchaseReturnPayment): void {
            $validateCompanyRelation = static function (
                ?int $relationId,
                string $modelClass,
                string $errorMessage
            ) use ($purchaseReturnPayment): void {
                if (is_null($relationId)) {
                    return;
                }

                $relation = $modelClass::find($relationId);

                if (! $relation || (int) $relation->company_id !== (int) $purchaseReturnPayment->company_id) {
                    throw new InvalidArgumentException($errorMessage);
                }
            };

            $validateCompanyRelation(
                relationId: $purchaseReturnPayment->branch_id,
                modelClass: Branch::class,
                errorMessage: 'Purchase return payment branch must exist in the same company.',
            );

            $validateCompanyRelation(
                relationId: $purchaseReturnPayment->purchase_return_id,
                modelClass: PurchaseReturn::class,
                errorMessage: 'Purchase return payment purchase return must exist in the same company.',
            );

            $validateCompanyRelation(
                relationId: $purchaseReturnPayment->cash_account_id,
                modelClass: CashAccount::class,
                errorMessage: 'Purchase return payment cash account must exist in the same company.',
            );
        };

        static::creating(function (self $purchaseReturnPayment) use ($validateRelations) {
            $purchaseReturnPayment->ulid = Str::ulid()->generate();

            if (auth()->check()) {
                $purchaseReturnPayment->created_by = auth()->id();
                $purchaseReturnPayment->updated_by = auth()->id();
            }

            $validateRelations($purchaseReturnPayment);
        });

        static::updating(function (self $purchaseReturnPayment) use ($validateRelations) {
            if (auth()->check()) {
                $purchaseReturnPayment->updated_by = auth()->id();
            }

            $validateRelations($purchaseReturnPayment);
        });

        static::deleting(function (self $purchaseReturnPayment) {
            if (! auth()->check()) {
                return;
            }

            $purchaseReturnPayment->deleted_by = auth()->id();
            $purchaseReturnPayment->save();
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

    public function purchaseReturn()
    {
        return $this->belongsTo(PurchaseReturn::class)->withTrashed();
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
