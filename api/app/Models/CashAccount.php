<?php

namespace App\Models;

use App\Traits\BootableModel;
use App\Traits\ScopeableByCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CashAccount extends Model
{
    use BootableModel;
    use HasFactory;
    use ScopeableByCompany;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'branch_id',
        'code',
        'name',
        'is_bank',
        'is_active',
        'remarks',
    ];

    protected $casts = [
        'is_bank' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class)->withTrashed();
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class)->withTrashed();
    }

    public function purchasePayments()
    {
        return $this->hasMany(PurchasePayment::class);
    }

    public function capitalAdditions()
    {
        return $this->hasMany(CapitalAddition::class);
    }

    public function capitalWithdrawals()
    {
        return $this->hasMany(CapitalWithdrawal::class);
    }

    public function nonCapitalAdditions()
    {
        return $this->hasMany(NonCapitalAddition::class);
    }

    public function nonCapitalWithdrawals()
    {
        return $this->hasMany(NonCapitalWithdrawal::class);
    }

    public function saleOrderDownPayments()
    {
        return $this->hasMany(SaleOrderDownPayment::class);
    }

    public function saleOrderDownPaymentApplies()
    {
        return $this->hasMany(SaleOrderDownPaymentApply::class);
    }

    public function salePayments()
    {
        return $this->hasMany(SalePayment::class);
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($query) use ($search) {
            $query->where('cash_accounts.code', 'like', '%'.$search.'%')
                ->orWhere('cash_accounts.name', 'like', '%'.$search.'%')
                ->orWhere('cash_accounts.remarks', 'like', '%'.$search.'%');
        });
    }
}
