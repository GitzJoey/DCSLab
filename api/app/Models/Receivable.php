<?php

namespace App\Models;

use App\Traits\ScopeableByBranch;
use App\Traits\ScopeableByCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use InvalidArgumentException;

class Receivable extends Model
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
        'customer_id',
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
        $validateRelations = static function (self $receivable): void {
            $validateCompanyRelation = static function (
                ?int $relationId,
                string $modelClass,
                string $errorMessage
            ) use ($receivable): void {
                if (is_null($relationId)) {
                    return;
                }

                $relation = $modelClass::find($relationId);

                if (! $relation || (int) $relation->company_id !== (int) $receivable->company_id) {
                    throw new InvalidArgumentException($errorMessage);
                }
            };

            $validateCompanyRelation(
                relationId: $receivable->branch_id,
                modelClass: Branch::class,
                errorMessage: 'Receivable branch must exist in the same company.',
            );

            $validateCompanyRelation(
                relationId: $receivable->category_id,
                modelClass: ReceivableCategory::class,
                errorMessage: 'Receivable category must exist in the same company.',
            );

            $validateCompanyRelation(
                relationId: $receivable->customer_id,
                modelClass: Customer::class,
                errorMessage: 'Receivable customer must exist in the same company.',
            );

            $validateCompanyRelation(
                relationId: $receivable->cash_account_id,
                modelClass: CashAccount::class,
                errorMessage: 'Receivable cash account must exist in the same company.',
            );
        };

        static::creating(function (self $receivable) use ($validateRelations) {
            $receivable->ulid = Str::ulid()->generate();

            if (auth()->check()) {
                $receivable->created_by = auth()->id();
                $receivable->updated_by = auth()->id();
            }

            $validateRelations($receivable);
        });

        static::updating(function (self $receivable) use ($validateRelations) {
            if (auth()->check()) {
                $receivable->updated_by = auth()->id();
            }

            $validateRelations($receivable);
        });

        static::deleting(function (self $receivable) {
            if (! auth()->check()) {
                return;
            }

            $receivable->deleted_by = auth()->id();
            $receivable->save();
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
        return $this->belongsTo(ReceivableCategory::class, 'category_id')->withTrashed();
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class)->withTrashed();
    }

    public function cashAccount()
    {
        return $this->belongsTo(CashAccount::class)->withTrashed();
    }

    public function payments()
    {
        return $this->hasMany(ReceivablePayment::class);
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
