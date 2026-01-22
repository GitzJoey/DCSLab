<?php

namespace App\Models;

use App\Enums\RecordStatusEnum;
use App\Traits\BootableModel;
use App\Traits\ScopeableByCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Branch extends Model
{
    use BootableModel;
    use HasFactory;
    use ScopeableByCompany;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'code',
        'name',
        'address',
        'city',
        'contact',
        'is_main',
        'remarks',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'is_main' => 'boolean',
            'status' => RecordStatusEnum::class,
        ];
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function warehouses()
    {
        return $this->hasMany(Warehouse::class);
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function cashAccounts()
    {
        return $this->hasMany(CashAccount::class);
    }

    public function purchaseProductUnitSerials()
    {
        return $this->hasMany(PurchaseProductUnitSerial::class);
    }

    public function purchaseAdditionalCosts()
    {
        return $this->hasMany(PurchaseAdditionalCost::class);
    }

    public function purchaseReturnProductUnits()
    {
        return $this->hasMany(PurchaseReturnProductUnit::class);
    }

    public function purchaseReturnProductUnitSerials()
    {
        return $this->hasMany(PurchaseReturnProductUnitSerial::class);
    }

    public function purchaseReturnAdditionalCosts()
    {
        return $this->hasMany(PurchaseReturnAdditionalCost::class);
    }

    public function purchasePayments()
    {
        return $this->hasMany(PurchasePayment::class);
    }

    public function purchaseReceipts()
    {
        return $this->hasMany(PurchaseReceipt::class);
    }

    public function purchaseReceiptProductUnits()
    {
        return $this->hasMany(PurchaseReceiptProductUnit::class);
    }

    public function purchaseReceiptProductUnitSerials()
    {
        return $this->hasMany(PurchaseReceiptProductUnitSerial::class);
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

    public function stockTransfers()
    {
        return $this->hasMany(StockTransfer::class);
    }

    public function stockTransferProductUnits()
    {
        return $this->hasMany(StockTransferProductUnit::class);
    }

    public function stockTransferProductUnitSerials()
    {
        return $this->hasMany(StockTransferProductUnitSerial::class);
    }

    public function salesOrders()
    {
        return $this->hasMany(SalesOrder::class);
    }

    public function saleOrderProductUnits()
    {
        return $this->hasMany(SaleOrderProductUnit::class);
    }

    public function saleOrderDownPayments()
    {
        return $this->hasMany(SaleOrderDownPayment::class);
    }

    public function saleOrderDownPaymentApplies()
    {
        return $this->hasMany(SaleOrderDownPaymentApply::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function saleProductUnits()
    {
        return $this->hasMany(SaleProductUnit::class);
    }

    public function saleProductUnitSerials()
    {
        return $this->hasMany(SaleProductUnitSerial::class);
    }

    public function salePayments()
    {
        return $this->hasMany(SalePayment::class);
    }

    public function saleReceipts()
    {
        return $this->hasMany(SaleReceipt::class);
    }

    public function saleReceiptProductUnits()
    {
        return $this->hasMany(SaleReceiptProductUnit::class);
    }

    public function saleReceiptProductUnitSerials()
    {
        return $this->hasMany(SaleReceiptProductUnitSerial::class);
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($query) use ($search) {
            $query->where('branches.code', 'like', '%'.$search.'%')
                ->orWhere('branches.name', 'like', '%'.$search.'%')
                ->orWhere('branches.address', 'like', '%'.$search.'%')
                ->orWhere('branches.city', 'like', '%'.$search.'%')
                ->orWhere('branches.contact', 'like', '%'.$search.'%')
                ->orWhere('branches.remarks', 'like', '%'.$search.'%');
        });
    }
}
