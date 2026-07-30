<?php

namespace App\Enums;

use App\Traits\EnumHelper;

enum ChartOfAccountSystemKeyEnum: string
{
    use EnumHelper;

    case ASSET_ROOT = 'asset_root';
    case ASSET_CURRENT = 'asset_current';
    case ASSET_CURRENT_CASH_AND_CASH_EQUIVALENTS = 'asset_current_cash_and_cash_equivalents';
    case ASSET_CURRENT_CASH = 'asset_current_cash';
    case ASSET_CURRENT_BANK = 'asset_current_bank';
    case ASSET_CURRENT_ACCOUNT_RECEIVABLE = 'asset_current_account_receivable';
    case ASSET_CURRENT_INVENTORY = 'asset_current_inventory';
    case ASSET_CURRENT_PREPAID_EXPENSE = 'asset_current_prepaid_expense';
    case ASSET_CURRENT_VAT_IN = 'asset_current_vat_in';
    case ASSET_CURRENT_SUPPLIER_DOWN_PAYMENT = 'asset_current_supplier_down_payment';
    case ASSET_NON_CURRENT = 'asset_non_current';
    case ASSET_NON_CURRENT_FIXED_ASSET = 'asset_non_current_fixed_asset';
    case ASSET_NON_CURRENT_ACCUMULATED_DEPRECIATION = 'asset_non_current_accumulated_depreciation';
    case ASSET_NON_CURRENT_INTANGIBLE_ASSET = 'asset_non_current_intangible_asset';

    case LIABILITY_ROOT = 'liability_root';
    case LIABILITY_ACCOUNT_PAYABLE = 'liability_account_payable';
    case LIABILITY_TAX_PAYABLE = 'liability_tax_payable';
    case LIABILITY_DEFERRED_INCOME = 'liability_deferred_income';
    case LIABILITY_CUSTOMER_DOWN_PAYMENT = 'liability_customer_down_payment';
    case LIABILITY_GOODS_RECEIVED_NOT_INVOICED = 'liability_goods_received_not_invoiced';

    case EQUITY_ROOT = 'equity_root';
    case EQUITY_CAPITAL = 'equity_capital';
    case EQUITY_CAPITAL_OPENING_CAPITAL = 'equity_capital_opening_capital';
    case EQUITY_CAPITAL_ADDITIONAL_CAPITAL = 'equity_capital_additional_capital';
    case EQUITY_CAPITAL_DRAWING = 'equity_capital_drawing';
    case EQUITY_CURRENT_MONTH_EARNINGS = 'equity_current_month_earnings';
    case EQUITY_CURRENT_YEAR_EARNINGS = 'equity_current_year_earnings';
    case EQUITY_RETAINED_EARNINGS = 'equity_retained_earnings';

    case INCOME_ROOT = 'income_root';
    case INCOME_SALES = 'income_sales';
    case INCOME_SERVICE = 'income_service';
    case INCOME_SALES_RETURN = 'income_sales_return';

    case COGS_ROOT = 'cogs_root';
    case COGS_MATERIAL_COST = 'cogs_material_cost';
    case COGS_DIRECT_LABOR_COST = 'cogs_direct_labor_cost';
    case COGS_GOODS_SOLD = 'cogs_goods_sold';

    case EXPENSE_ROOT = 'expense_root';
    case EXPENSE_FREIGHT_OUT = 'expense_freight_out';
    case EXPENSE_SALARY = 'expense_salary';
    case EXPENSE_ELECTRICITY = 'expense_electricity';
    case EXPENSE_RENT = 'expense_rent';

    case OTHER_INCOME_ROOT = 'other_income_root';
    case OTHER_INCOME_INTEREST = 'other_income_interest';
    case OTHER_EXPENSE_ROOT = 'other_expense_root';
    case OTHER_EXPENSE_INTEREST = 'other_expense_interest';

    case SYSTEM_SUSPENSE = 'system_suspense';
}
