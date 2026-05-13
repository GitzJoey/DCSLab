<?php

namespace App\Models;

use App\Enums\ChartOfAccountSystemKeyEnum;
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

    public function vatProfiles()
    {
        return $this->hasMany(VatProfile::class);
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

    public function stockAdjustments()
    {
        return $this->hasMany(StockAdjustment::class);
    }

    public function stockAdjustmentInItems()
    {
        return $this->hasMany(StockAdjustmentInItem::class);
    }

    public function stockAdjustmentOutItems()
    {
        return $this->hasMany(StockAdjustmentOutItem::class);
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function purchaseAdditionalCostCategories()
    {
        return $this->hasMany(PurchaseAdditionalCostCategory::class);
    }

    public function purchaseAdditionalCosts()
    {
        return $this->hasMany(PurchaseAdditionalCost::class);
    }

    public function purchaseAdditionalCostPayments()
    {
        return $this->hasMany(PurchaseAdditionalCostPayment::class);
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

    public function chartOfAccounts()
    {
        return $this->hasMany(ChartOfAccount::class);
    }

    public function equityRootChartOfAccount()
    {
        return $this->hasOne(ChartOfAccount::class)
            ->where('system_key', ChartOfAccountSystemKeyEnum::EQUITY_ROOT);
    }

    public function assetCurrentCashChartOfAccount()
    {
        return $this->hasOne(ChartOfAccount::class)
            ->where('system_key', ChartOfAccountSystemKeyEnum::ASSET_CURRENT_CASH);
    }

    public function assetCurrentBankChartOfAccount()
    {
        return $this->hasOne(ChartOfAccount::class)
            ->where('system_key', ChartOfAccountSystemKeyEnum::ASSET_CURRENT_BANK);
    }

    public function liabilityAccountPayableChartOfAccount()
    {
        return $this->hasOne(ChartOfAccount::class)
            ->where('system_key', ChartOfAccountSystemKeyEnum::LIABILITY_ACCOUNT_PAYABLE);
    }

    public function assetCurrentAccountReceivableChartOfAccount()
    {
        return $this->hasOne(ChartOfAccount::class)
            ->where('system_key', ChartOfAccountSystemKeyEnum::ASSET_CURRENT_ACCOUNT_RECEIVABLE);
    }

    public function assetCurrentPrepaidExpenseChartOfAccount()
    {
        return $this->hasOne(ChartOfAccount::class)
            ->where('system_key', ChartOfAccountSystemKeyEnum::ASSET_CURRENT_PREPAID_EXPENSE);
    }

    public function equityCapitalOpeningCapitalChartOfAccount()
    {
        return $this->hasOne(ChartOfAccount::class)
            ->where('system_key', ChartOfAccountSystemKeyEnum::EQUITY_CAPITAL_OPENING_CAPITAL);
    }

    public function equityCapitalAdditionalCapitalChartOfAccount()
    {
        return $this->hasOne(ChartOfAccount::class)
            ->where('system_key', ChartOfAccountSystemKeyEnum::EQUITY_CAPITAL_ADDITIONAL_CAPITAL);
    }

    public function equityCapitalDrawingChartOfAccount()
    {
        return $this->hasOne(ChartOfAccount::class)
            ->where('system_key', ChartOfAccountSystemKeyEnum::EQUITY_CAPITAL_DRAWING);
    }

    public function equityCurrentMonthEarningsChartOfAccount()
    {
        return $this->hasOne(ChartOfAccount::class)
            ->where('system_key', ChartOfAccountSystemKeyEnum::EQUITY_CURRENT_MONTH_EARNINGS);
    }

    public function equityCurrentYearEarningsChartOfAccount()
    {
        return $this->hasOne(ChartOfAccount::class)
            ->where('system_key', ChartOfAccountSystemKeyEnum::EQUITY_CURRENT_YEAR_EARNINGS);
    }

    public function equityRetainedEarningsChartOfAccount()
    {
        return $this->hasOne(ChartOfAccount::class)
            ->where('system_key', ChartOfAccountSystemKeyEnum::EQUITY_RETAINED_EARNINGS);
    }

    public function incomeRootChartOfAccount()
    {
        return $this->hasOne(ChartOfAccount::class)
            ->where('system_key', ChartOfAccountSystemKeyEnum::INCOME_ROOT);
    }

    public function expenseRootChartOfAccount()
    {
        return $this->hasOne(ChartOfAccount::class)
            ->where('system_key', ChartOfAccountSystemKeyEnum::EXPENSE_ROOT);
    }

    public function otherExpenseRootChartOfAccount()
    {
        return $this->hasOne(ChartOfAccount::class)
            ->where('system_key', ChartOfAccountSystemKeyEnum::OTHER_EXPENSE_ROOT);
    }

    public function systemSuspenseChartOfAccount()
    {
        return $this->hasOne(ChartOfAccount::class)
            ->where('system_key', ChartOfAccountSystemKeyEnum::SYSTEM_SUSPENSE);
    }

    public function liabilityDeferredIncomeChartOfAccount()
    {
        return $this->hasOne(ChartOfAccount::class)
            ->where('system_key', ChartOfAccountSystemKeyEnum::LIABILITY_DEFERRED_INCOME);
    }

    public function investors()
    {
        return $this->hasMany(Investor::class);
    }

    public function cashAccounts()
    {
        return $this->hasMany(CashAccount::class);
    }

    public function stockTransfers()
    {
        return $this->hasMany(StockTransfer::class);
    }

    public function stockTransferItems()
    {
        return $this->hasMany(StockTransferItem::class);
    }

    public function stockTransferItemSerials()
    {
        return $this->hasMany(StockTransferItemSerial::class);
    }
}
