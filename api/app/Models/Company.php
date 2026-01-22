<?php

namespace App\Models;

use App\Enums\RecordStatusEnum;
use App\Traits\BootableModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use BootableModel;
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'address',
        'default',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'default' => 'boolean',
            'status' => RecordStatusEnum::class,
        ];
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function branches()
    {
        return $this->hasMany(Branch::class);
    }

    public function warehouses()
    {
        return $this->hasMany(Warehouse::class);
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    public function productCategories()
    {
        return $this->hasMany(ProductCategory::class);
    }

    public function brands()
    {
        return $this->hasMany(Brand::class);
    }

    public function units()
    {
        return $this->hasMany(Unit::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function productUnits()
    {
        return $this->hasMany(ProductUnit::class);
    }

    public function stockAdjustmentCategories()
    {
        return $this->hasMany(StockAdjustmentCategory::class);
    }

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }

    public function customerAddresses()
    {
        return $this->hasMany(CustomerAddress::class);
    }

    public function customerGroups()
    {
        return $this->hasMany(CustomerGroup::class);
    }

    public function suppliers()
    {
        return $this->hasMany(Supplier::class);
    }

    public function investors()
    {
        return $this->hasMany(Investor::class);
    }

    public function cashAccounts()
    {
        return $this->hasMany(CashAccount::class);
    }

    public function capitalAdditions()
    {
        return $this->hasMany(CapitalAddition::class);
    }

    public function capitalWithdrawals()
    {
        return $this->hasMany(CapitalWithdrawal::class);
    }

    public function nonCapitalAdditionCategories()
    {
        return $this->hasMany(NonCapitalAdditionCategory::class);
    }

    public function nonCapitalAdditions()
    {
        return $this->hasMany(NonCapitalAddition::class);
    }

    public function nonCapitalWithdrawalCategories()
    {
        return $this->hasMany(NonCapitalWithdrawalCategory::class);
    }

    public function nonCapitalWithdrawals()
    {
        return $this->hasMany(NonCapitalWithdrawal::class);
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function purchaseProductUnits()
    {
        return $this->hasMany(PurchaseProductUnit::class);
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

    public function purchaseReturnAdditionalCostCategories()
    {
        return $this->hasMany(PurchaseReturnAdditionalCostCategory::class);
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
        return $query->where(function ($q) use ($search) {
            $q->where('companies.code', 'like', '%'.$search.'%')
                ->orWhere('companies.name', 'like', '%'.$search.'%')
                ->orWhere('companies.address', 'like', '%'.$search.'%');
        });
    }
}
