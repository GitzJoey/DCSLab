<?php

namespace App\Actions\ChartOfAccount;

use App\Actions\Randomizer\RandomizerActions;
use App\Enums\AccountType;
use App\Enums\RecordStatus;
use App\Models\ChartOfAccount;
use App\Traits\CacheHelper;
use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ChartOfAccountActions
{
    use CacheHelper;

    public function __construct()
    {
    }

    public function create(
        array $chartOfAccountArr
    ): ChartOfAccount {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $chartOfAccount = new ChartOfAccount();
            $chartOfAccount->company_id = $chartOfAccountArr['company_id'];
            $chartOfAccount->branch_id = $chartOfAccountArr['branch_id'];
            $chartOfAccount->parent_id = $chartOfAccountArr['parent_id'];
            $chartOfAccount->code = $chartOfAccountArr['code'];
            $chartOfAccount->name = $chartOfAccountArr['name'];
            $chartOfAccount->account_type = $chartOfAccountArr['account_type'];
            $chartOfAccount->can_have_child = $chartOfAccountArr['can_have_child'];
            $chartOfAccount->remarks = $chartOfAccountArr['remarks'];
            $chartOfAccount->status = $chartOfAccountArr['status'];

            $chartOfAccount->save();

            DB::commit();

            $this->flushCache();

            return $chartOfAccount;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '' : auth()->id()).'] '.__METHOD__.$e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '' : auth()->id()).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }

    public function createDefaultAccountPerCompany(
        int $companyId
    ): array {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $coaArr = [
                ['code' => '01', 'name' => 'Assets',                    'can_have_child' => 0,  'account_type' => AccountType::ASSET->value,                    'parent' => null],
                ['code' => '01.01', 'name' => 'Current Asset',  'can_have_child' => 0,  'account_type' => AccountType::CURRENT_ASSET->value,    'parent' => AccountType::ASSET->value],
                ['code' => '01.01.01',  'name' => 'Cash And Bank',      'can_have_child' => 1,  'account_type' => AccountType::CASH_AND_BANK->value,    'parent' => AccountType::CURRENT_ASSET->value],
                ['code' => '01.01.01.01', 'name' => 'Cash', 'can_have_child' => 1,  'account_type' => AccountType::CASH->value, 'parent' => AccountType::CASH_AND_BANK->value],
                ['code' => '01.01.01.02', 'name' => 'Bank', 'can_have_child' => 1,  'account_type' => AccountType::BANK->value, 'parent' => AccountType::CASH_AND_BANK->value],
                ['code' => '01.01.02',  'name' => 'Receivables',        'can_have_child' => 1,  'account_type' => AccountType::RECEIVABLE->value,       'parent' => AccountType::CURRENT_ASSET->value],
                ['code' => '01.01.02.01', 'name' => 'Revenue Receivable',                           'can_have_child' => 1,  'account_type' => AccountType::REVENUE_RECEIVABLE->value,                       'parent' => AccountType::RECEIVABLE->value],
                ['code' => '01.01.02.02', 'name' => 'Other Receivable',                             'can_have_child' => 1,  'account_type' => AccountType::OTHER_RECEIVABLE->value,                         'parent' => AccountType::RECEIVABLE->value],
                ['code' => '01.01.02.03', 'name' => 'Purchase Order (PO) Down Payment Receivable',  'can_have_child' => 1,  'account_type' => AccountType::PURCHASE_ORDER_DOWN_PAYMENT_RECEIVABLE->value,   'parent' => AccountType::RECEIVABLE->value],
                ['code' => '01.01.02.04', 'name' => 'Purchase Refund Receivable',                   'can_have_child' => 1,  'account_type' => AccountType::PURCHASE_REFUND_RECEIVABLE->value,               'parent' => AccountType::RECEIVABLE->value],
                ['code' => '01.01.02.05', 'name' => 'Purchase Return Receivable',                   'can_have_child' => 1,  'account_type' => AccountType::PURCHASE_RETURN_RECEIVABLE->value,               'parent' => AccountType::RECEIVABLE->value],
                ['code' => '01.01.02.06', 'name' => 'Asset Sales Receivable',                       'can_have_child' => 1,  'account_type' => AccountType::ASSET_SALES_RECEIVABLE->value,                   'parent' => AccountType::RECEIVABLE->value],
                ['code' => '01.01.02.07', 'name' => 'Sales Receivable',                             'can_have_child' => 1,  'account_type' => AccountType::SALES_RECEIVABLE->value,                         'parent' => AccountType::RECEIVABLE->value],
                ['code' => '01.01.02.08', 'name' => 'Value Added Tax (VAT) Receivable',             'can_have_child' => 1,  'account_type' => AccountType::VAT_RECEIVABLE->value,                           'parent' => AccountType::RECEIVABLE->value],
                ['code' => '01.01.03',  'name' => 'Prepaid Expense',    'can_have_child' => 1,  'account_type' => AccountType::PREPAID_EXPENSE->value,  'parent' => AccountType::CURRENT_ASSET->value],
                ['code' => '01.01.04',  'name' => 'Inventory',          'can_have_child' => 1,  'account_type' => AccountType::INVENTORY->value,        'parent' => AccountType::CURRENT_ASSET->value],
                ['code' => '01.02', 'name' => 'Fixed Asset',    'can_have_child' => 1,  'account_type' => AccountType::FIXED_ASSET->value,      'parent' => AccountType::ASSET->value],

                ['code' => '02', 'name' => 'Liabilities',               'can_have_child' => 0,  'account_type' => AccountType::LIABILITY->value,                'parent' => null],
                ['code' => '02.01', 'name' => 'Short Term Liability',   'can_have_child' => 0,  'account_type' => AccountType::SHORT_TERM_LIABILITY->value, 'parent' => AccountType::LIABILITY->value],
                ['code' => '02.01.01', 'name' => 'Expense Payable',                         'can_have_child' => 1,  'account_type' => AccountType::EXPENSE_PAYABLE->value,                          'parent' => AccountType::SHORT_TERM_LIABILITY->value],
                ['code' => '02.01.02', 'name' => 'Prepaid Expense Payable',                 'can_have_child' => 1,  'account_type' => AccountType::PREPAID_EXPENSE_PAYABLE->value,                  'parent' => AccountType::SHORT_TERM_LIABILITY->value],
                ['code' => '02.01.03', 'name' => 'Other Payable',                           'can_have_child' => 1,  'account_type' => AccountType::OTHER_PAYABLE->value,                            'parent' => AccountType::SHORT_TERM_LIABILITY->value],
                ['code' => '02.01.04', 'name' => 'Sales Order (SO) Down Payment Payable',   'can_have_child' => 1,  'account_type' => AccountType::SALES_ORDER_DOWN_PAYMENT_PAYABLE->value,         'parent' => AccountType::SHORT_TERM_LIABILITY->value],
                ['code' => '02.01.05', 'name' => 'Purchase Payable',                        'can_have_child' => 1,  'account_type' => AccountType::PURCHASE_PAYABLE->value,                         'parent' => AccountType::SHORT_TERM_LIABILITY->value],
                ['code' => '02.01.06', 'name' => 'Production Process Direct Cost Payable',  'can_have_child' => 1,  'account_type' => AccountType::PRODUCTION_PROCESS_DIRECT_COST_PAYABLE->value,   'parent' => AccountType::SHORT_TERM_LIABILITY->value],
                ['code' => '02.01.07', 'name' => 'Production Process Period Cost Payable',  'can_have_child' => 1,  'account_type' => AccountType::PRODUCTION_PROCESS_PERIOD_COST_PAYABLE->value,   'parent' => AccountType::SHORT_TERM_LIABILITY->value],
                ['code' => '02.01.08', 'name' => 'Asset Purchase Payable',                  'can_have_child' => 1,  'account_type' => AccountType::ASSET_PURCHASE_PAYABLE->value,                   'parent' => AccountType::SHORT_TERM_LIABILITY->value],
                ['code' => '02.01.09', 'name' => 'Sales Additional Expense Payable',        'can_have_child' => 1,  'account_type' => AccountType::SALES_ADDITIONAL_EXPENSE_PAYABLE->value,         'parent' => AccountType::SHORT_TERM_LIABILITY->value],
                ['code' => '02.01.10', 'name' => 'Sales Returns Payable',                   'can_have_child' => 1,  'account_type' => AccountType::SALES_RETURNS_PAYABLE->value,                    'parent' => AccountType::SHORT_TERM_LIABILITY->value],
                ['code' => '02.01.11', 'name' => 'Value Added Tax (VAT) Payable',           'can_have_child' => 1,  'account_type' => AccountType::VAT_PAYABLE->value,                              'parent' => AccountType::SHORT_TERM_LIABILITY->value],
                ['code' => '02.02', 'name' => 'Long Term Liability',    'can_have_child' => 0,  'account_type' => AccountType::LONG_TERM_LIABILITY->value,  'parent' => AccountType::LIABILITY->value],
                ['code' => '02.02.01', 'name' => 'Other Debt',          'can_have_child' => 1,  'account_type' => AccountType::OTHER_DEBT->value,           'parent' => AccountType::LONG_TERM_LIABILITY->value],
                ['code' => '02.02.02', 'name' => 'Asset Purchase Debt', 'can_have_child' => 1,  'account_type' => AccountType::ASSET_PURCHASE_DEBT->value,  'parent' => AccountType::LONG_TERM_LIABILITY->value],

                ['code' => '03', 'name' => 'Equity',                    'can_have_child' => 0,  'account_type' => AccountType::EQUITY->value,                   'parent' => null],
                ['code' => '03.01', 'name' => 'Capital',                    'can_have_child' => 1, 'account_type' => AccountType::CAPITAL->value,                   'parent' => AccountType::EQUITY->value],
                ['code' => '03.02', 'name' => 'Income Statement Account',   'can_have_child' => 0, 'account_type' => AccountType::INCOME_STATEMENT_ACCOUNT->value,  'parent' => AccountType::EQUITY->value],
                ['code' => '03.02.01', 'name' => 'Retained Earning',        'can_have_child' => 0,  'account_type' => AccountType::RETAINED_EARNING->value,     'parent' => AccountType::INCOME_STATEMENT_ACCOUNT->value],
                ['code' => '03.02.02', 'name' => 'Current Month Profit',    'can_have_child' => 0,  'account_type' => AccountType::CURRENT_MONTH_PROFIT->value, 'parent' => AccountType::INCOME_STATEMENT_ACCOUNT->value],
                ['code' => '03.02.03', 'name' => 'Current Year Profit',     'can_have_child' => 0,  'account_type' => AccountType::CURRENT_YEAR_PROFIT->value,  'parent' => AccountType::INCOME_STATEMENT_ACCOUNT->value],

                ['code' => '04', 'name' => 'Operating Revenue',         'can_have_child' => 1,  'account_type' => AccountType::OPERATING_REVENUE->value,        'parent' => null],
                ['code' => '04.01',     'name' => 'Sales',              'can_have_child' => 1,  'account_type' => AccountType::SALES->value,                                    'parent' => AccountType::OPERATING_REVENUE->value],
                ['code' => '04.01.01', 'name' => 'Product Sales',       'can_have_child' => 1, 'account_type' => AccountType::PRODUCT_SALES->value,     'parent' => AccountType::SALES->value],
                ['code' => '04.01.02', 'name' => 'Service Sales',       'can_have_child' => 1, 'account_type' => AccountType::SERVICE_SALES->value,     'parent' => AccountType::SALES->value],
                ['code' => '04.01.03', 'name' => 'Sales Discount',      'can_have_child' => 1, 'account_type' => AccountType::SALES_DISCOUNTS->value,   'parent' => AccountType::SALES->value],
                ['code' => '04.01.04', 'name' => 'Sales Value Added',   'can_have_child' => 1, 'account_type' => AccountType::SALES_VALUE_ADDED->value, 'parent' => AccountType::SALES->value],
                ['code' => '04.01.05', 'name' => 'Sales Rounding',      'can_have_child' => 1, 'account_type' => AccountType::SALES_ROUNDING->value,    'parent' => AccountType::SALES->value],
                ['code' => '04.01.06', 'name' => 'Sales Returns',       'can_have_child' => 1, 'account_type' => AccountType::SALES_RETURNS->value,     'parent' => AccountType::SALES->value],
                ['code' => '04.02.01',  'name' => 'Project Revenue',    'can_have_child' => 0,  'account_type' => AccountType::OPERATING_REVENUE_USER_SET_INCOME_GROUP->value,  'parent' => AccountType::OPERATING_REVENUE->value],
                ['code' => '04.02.02',  'name' => 'Supplier Cashback',  'can_have_child' => 0,  'account_type' => AccountType::OPERATING_REVENUE_USER_SET_INCOME_GROUP->value,  'parent' => AccountType::OPERATING_REVENUE->value],
                ['code' => '04.03',     'name' => 'Receivable Revenue', 'can_have_child' => 1,  'account_type' => AccountType::RECEIVABLE_REVENUE->value,                       'parent' => AccountType::OPERATING_REVENUE->value],

                ['code' => '05', 'name' => 'Cost of Goods Sold',        'can_have_child' => 0,  'account_type' => AccountType::COST_OF_GOODS_SOLD->value,       'parent' => null],
                ['code' => '05.01',     'name' => 'Cost of Goods Sold', 'can_have_child' => 0, 'account_type' => AccountType::SALES_COST_OF_GOODS_SOLD->value, 'parent' => AccountType::COST_OF_GOODS_SOLD->value],
                ['code' => '05.02',     'name' => 'Purchase Summary',   'can_have_child' => 0, 'account_type' => AccountType::PURCHASE_SUMMARY->value,         'parent' => AccountType::COST_OF_GOODS_SOLD->value],
                ['code' => '05.02.01',  'name' => 'Gross Purchase',              'can_have_child' => 0, 'account_type' => AccountType::GROSS_PURCHASE->value,           'parent' => AccountType::COST_OF_GOODS_SOLD->value],
                ['code' => '05.02.02',  'name' => 'Purchase Discount',           'can_have_child' => 1, 'account_type' => AccountType::PURCHASE_DISCOUNT->value,        'parent' => AccountType::COST_OF_GOODS_SOLD->value],
                ['code' => '05.02.03',  'name' => 'Additional Purchase Cost',    'can_have_child' => 1, 'account_type' => AccountType::ADDITIONAL_PURCHASE_COST->value, 'parent' => AccountType::COST_OF_GOODS_SOLD->value],
                ['code' => '05.02.04',  'name' => 'Purchase Refund',             'can_have_child' => 1, 'account_type' => AccountType::PURCHASE_REFUND->value,          'parent' => AccountType::COST_OF_GOODS_SOLD->value],
                ['code' => '05.02.05',  'name' => 'Purchase Return',             'can_have_child' => 0, 'account_type' => AccountType::PURCHASE_RETURN->value,          'parent' => AccountType::COST_OF_GOODS_SOLD->value],

                ['code' => '06', 'name' => 'Operating Expenses',        'can_have_child' => 1,  'account_type' => AccountType::OPERATING_EXPENSE->value,        'parent' => null],
                ['code' => '06.02',     'name' => 'Debt Cost',                      'can_have_child' => 1, 'account_type' => AccountType::DEBT_COST->value,                                 'parent' => AccountType::OPERATING_EXPENSE->value],
                ['code' => '06.03',     'name' => 'Depreciation Load',              'can_have_child' => 1, 'account_type' => AccountType::DEPRECIATION_LOAD->value,                         'parent' => AccountType::OPERATING_EXPENSE->value],

                ['code' => '07', 'name' => 'Other Income',              'can_have_child' => 1,  'account_type' => AccountType::OTHER_INCOME->value,             'parent' => null],
                ['code' => '07.01.01',  'name' => 'Bank Interest',                                  'can_have_child' => 1, 'account_type' => AccountType::OTHER_INCOME_USER_SET_INCOME_GROUP->value,            'parent' => AccountType::OTHER_INCOME->value],
                ['code' => '07.02',     'name' => 'Stock Adjustment Difference',                    'can_have_child' => 1, 'account_type' => AccountType::OTHER_INCOME_STOCK_ADJUSTMENT_DIFFERENCE->value,      'parent' => AccountType::OTHER_INCOME->value],
                ['code' => '07.03',     'name' => 'Asset Adjustment Difference',                    'can_have_child' => 1, 'account_type' => AccountType::OTHER_INCOME_ASSET_ADJUSTMENT_DIFFERENCE->value,      'parent' => AccountType::OTHER_INCOME->value],
                ['code' => '07.04',     'name' => 'Asset Sales Difference',                         'can_have_child' => 1, 'account_type' => AccountType::OTHER_INCOME_ASSET_SALES_DIFFERENCE->value,           'parent' => AccountType::OTHER_INCOME->value],
                ['code' => '07.05',     'name' => 'Production Process Difference',                  'can_have_child' => 1, 'account_type' => AccountType::OTHER_INCOME_PRODUCTION_PROCESS_DIFFERENCE->value,    'parent' => AccountType::OTHER_INCOME->value],
                ['code' => '07.06',     'name' => 'Value Added Tax (VAT) Receivable Adjustment',    'can_have_child' => 1, 'account_type' => AccountType::OTHER_INCOME_VAT_RECEIVABLE_ADJUSTMENT->value,        'parent' => AccountType::OTHER_INCOME->value],

                ['code' => '08', 'name' => 'Other Expenses',            'can_have_child' => 1,  'account_type' => AccountType::OTHER_EXPENSE->value,            'parent' => null],
                ['code' => '08.02',     'name' => 'Stock Adjustment Difference',                    'can_have_child' => 1, 'account_type' => AccountType::OTHER_EXPENSE_STOCK_ADJUSTMENT_DIFFERENCE->value,     'parent' => AccountType::OTHER_EXPENSE->value],
                ['code' => '08.03',     'name' => 'Asset Adjustment Difference',                    'can_have_child' => 1, 'account_type' => AccountType::OTHER_EXPENSE_ASSET_ADJUSTMENT_DIFFERENCE->value,     'parent' => AccountType::OTHER_EXPENSE->value],
                ['code' => '08.04',     'name' => 'Asset Sales Difference',                         'can_have_child' => 1, 'account_type' => AccountType::OTHER_EXPENSE_ASSET_SALES_DIFFERENCE->value,          'parent' => AccountType::OTHER_EXPENSE->value],
                ['code' => '08.05',     'name' => 'Production Process Difference',                  'can_have_child' => 1, 'account_type' => AccountType::OTHER_EXPENSE_PRODUCTION_PROCESS_DIFFERENCE->value,   'parent' => AccountType::OTHER_EXPENSE->value],
                ['code' => '08.06',     'name' => 'Value Added Tax (VAT) Receivable Adjustment',    'can_have_child' => 1, 'account_type' => AccountType::OTHER_EXPENSE_VAT_PAYABLE_ADJUSTMENT->value,          'parent' => AccountType::OTHER_EXPENSE->value],

                ['code' => '09', 'name' => 'Profit And Loss Summary',   'can_have_child' => 0,  'account_type' => AccountType::PROFIT_AND_LOSS_SUMMARY->value,  'parent' => null],
            ];

            $resultArr = [];
            for ($i = 0; $i < count($coaArr); $i++) {
                $coa = new ChartOfAccount();
                $coa->company_id = $companyId;
                $coa->branch_id = null;

                $coa->parent_id = null;

                $parentAcc = $coaArr[$i]['parent'];
                if ($parentAcc) {
                    $coa->parent_id = ChartOfAccount::whereCompanyId($companyId)->where('account_type', '=', $parentAcc)->first()->id;
                }

                $coa->code = $coaArr[$i]['code'];
                $coa->name = $coaArr[$i]['name'];
                $coa->can_have_child = $coaArr[$i]['can_have_child'];
                $coa->account_type = $coaArr[$i]['account_type'];
                $coa->status = 1;
                $coa->save();

                array_push($resultArr, $coa);
            }

            DB::commit();

            $this->flushCache();

            return $resultArr;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '' : auth()->id()).'] '.__METHOD__.$e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '' : auth()->id()).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }

    public function createDefaultAccountPerBranch(
        int $companyId,
        int $branchId
    ): array {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $coaArr = [
                ['code' => '01.01.01.01.01',    'name' => 'Owner Cash',                         'can_have_child' => 0,  'account_type' => AccountType::CASH->value,                                     'parent' => AccountType::CASH->value,                               'parent_name' => 'Cash'],
                ['code' => '01.01.01.01.02',    'name' => 'Cashier Cash',                       'can_have_child' => 0,  'account_type' => AccountType::CASH->value,                                     'parent' => AccountType::CASH->value,                               'parent_name' => 'Cash'],

                ['code' => '01.01.01.02.01',    'name' => 'Bank A',                             'can_have_child' => 0,  'account_type' => AccountType::BANK->value,                                     'parent' => AccountType::BANK->value,                               'parent_name' => 'Bank'],
                ['code' => '01.01.01.02.02',    'name' => 'Bank B',                             'can_have_child' => 0,  'account_type' => AccountType::BANK->value,                                     'parent' => AccountType::BANK->value,                               'parent_name' => 'Bank'],

                ['code' => '01.01.02.02.01',    'name' => 'Accounts Receivable Employee',       'can_have_child' => 1,  'account_type' => AccountType::OTHER_RECEIVABLE->value,                         'parent' => AccountType::OTHER_RECEIVABLE->value,                         'parent_name' => 'Other Receivable'],
                ['code' => '01.01.02.02.01.01', 'name' => 'Employee A',                         'can_have_child' => 1,  'account_type' => AccountType::OTHER_RECEIVABLE->value,                                         'parent' => AccountType::OTHER_RECEIVABLE->value,                         'parent_name' => 'Accounts Receivable Employee'],
                ['code' => '01.01.02.02.01.02', 'name' => 'Employee B',                         'can_have_child' => 1,  'account_type' => AccountType::OTHER_RECEIVABLE->value,                                         'parent' => AccountType::OTHER_RECEIVABLE->value,                         'parent_name' => 'Accounts Receivable Employee'],

                ['code' => '02.01.03.01', 'name' => 'Other Payable A',                          'can_have_child' => 0,  'account_type' => AccountType::OTHER_PAYABLE->value,                            'parent' => AccountType::OTHER_PAYABLE->value,                      'parent_name' => 'Other Payable'],
                ['code' => '02.01.03.02', 'name' => 'Other Payable B',                          'can_have_child' => 0,  'account_type' => AccountType::OTHER_PAYABLE->value,                            'parent' => AccountType::OTHER_PAYABLE->value,                      'parent_name' => 'Other Payable'],
                ['code' => '02.01.03.03', 'name' => 'Other Payable C',                          'can_have_child' => 0,  'account_type' => AccountType::OTHER_PAYABLE->value,                            'parent' => AccountType::OTHER_PAYABLE->value,                      'parent_name' => 'Other Payable'],

                ['code' => '02.02.01.01', 'name' => 'Bank A',                                   'can_have_child' => 0,  'account_type' => AccountType::OTHER_DEBT->value,                               'parent' => AccountType::OTHER_DEBT->value,                         'parent_name' => 'Other Debt'],
                ['code' => '02.02.01.02', 'name' => 'Bank B',                                   'can_have_child' => 0,  'account_type' => AccountType::OTHER_DEBT->value,                               'parent' => AccountType::OTHER_DEBT->value,                         'parent_name' => 'Other Debt'],

                ['code' => '03.01.01',          'name' => 'Owner Capital',                      'can_have_child' => 1,  'account_type' => AccountType::CAPITAL->value,                                  'parent' => AccountType::CAPITAL->value,                            'parent_name' => 'Capital'],
                ['code' => '03.01.01.01',       'name' => 'Owner Initial Capital',              'can_have_child' => 0,  'account_type' => AccountType::CAPITAL->value,                                  'parent' => AccountType::CAPITAL->value,                                    'parent_name' => 'Owner Capital'],
                ['code' => '03.01.01.02',       'name' => 'Owner Additional Capital',           'can_have_child' => 0,  'account_type' => AccountType::CAPITAL->value,                                  'parent' => AccountType::CAPITAL->value,                                    'parent_name' => 'Owner Capital'],
                ['code' => '03.01.02',          'name' => 'Investor A Capital',                 'can_have_child' => 1,  'account_type' => AccountType::CAPITAL->value,                                  'parent' => AccountType::CAPITAL->value,                            'parent_name' => 'Capital'],
                ['code' => '03.01.02.01',       'name' => 'Investor A Initial Capital',         'can_have_child' => 0,  'account_type' => AccountType::CAPITAL->value,                                  'parent' => AccountType::CAPITAL->value,                                    'parent_name' => 'Investor A Capital'],
                ['code' => '03.01.02.02',       'name' => 'Investor A Additional Capital',      'can_have_child' => 0,  'account_type' => AccountType::CAPITAL->value,                                  'parent' => AccountType::CAPITAL->value,                                    'parent_name' => 'Investor A Capital'],
                ['code' => '03.01.03',          'name' => 'Investor B Capital',                 'can_have_child' => 1,  'account_type' => AccountType::CAPITAL->value,                                  'parent' => AccountType::CAPITAL->value,                            'parent_name' => 'Capital'],
                ['code' => '03.01.03.01',       'name' => 'Investor B Initial Capital',         'can_have_child' => 0,  'account_type' => AccountType::CAPITAL->value,                                  'parent' => AccountType::CAPITAL->value,                                    'parent_name' => 'Investor B Capital'],
                ['code' => '03.01.03.02',       'name' => 'Investor B Additional Capital',      'can_have_child' => 0,  'account_type' => AccountType::CAPITAL->value,                                  'parent' => AccountType::CAPITAL->value,                                    'parent_name' => 'Investor B Capital'],

                ['code' => '06.01.01',          'name' => 'Fixed Operating Expenses',           'can_have_child' => 1,  'account_type' => AccountType::OPERATING_EXPENSE_USER_SET_EXPENSE_GROUP->value, 'parent' => AccountType::OPERATING_EXPENSE->value,                  'parent_name' => 'Operating Expenses'],
                ['code' => '06.01.01.01',       'name' => 'Employee Salary',                    'can_have_child' => 1,  'account_type' => AccountType::OPERATING_EXPENSE_USER_SET_EXPENSE_GROUP->value, 'parent' => AccountType::OPERATING_EXPENSE_USER_SET_EXPENSE_GROUP->value,   'parent_name' => 'Fixed Operating Expenses'],
                ['code' => '06.01.01.01.01',   'name' => 'Admin Salary',       'can_have_child' => 0, 'account_type' => AccountType::OPERATING_EXPENSE_USER_SET_EXPENSE_GROUP->value,  'parent' => AccountType::OPERATING_EXPENSE_USER_SET_EXPENSE_GROUP->value, 'parent_name' => 'Employee Salary'],
                ['code' => '06.01.01.01.02',   'name' => 'Marketing Salary',   'can_have_child' => 0, 'account_type' => AccountType::OPERATING_EXPENSE_USER_SET_EXPENSE_GROUP->value,  'parent' => AccountType::OPERATING_EXPENSE_USER_SET_EXPENSE_GROUP->value, 'parent_name' => 'Employee Salary'],
                ['code' => '06.01.01.01.03',   'name' => 'Programmer Salary',  'can_have_child' => 0, 'account_type' => AccountType::OPERATING_EXPENSE_USER_SET_EXPENSE_GROUP->value,  'parent' => AccountType::OPERATING_EXPENSE_USER_SET_EXPENSE_GROUP->value, 'parent_name' => 'Employee Salary'],
                ['code' => '06.01.01.01.04',   'name' => 'Driver Salary',      'can_have_child' => 0, 'account_type' => AccountType::OPERATING_EXPENSE_USER_SET_EXPENSE_GROUP->value,  'parent' => AccountType::OPERATING_EXPENSE_USER_SET_EXPENSE_GROUP->value, 'parent_name' => 'Employee Salary'],
                ['code' => '06.01.01.02',       'name' => 'Electricity Cost',                   'can_have_child' => 0,  'account_type' => AccountType::OPERATING_EXPENSE_USER_SET_EXPENSE_GROUP->value, 'parent' => AccountType::OPERATING_EXPENSE_USER_SET_EXPENSE_GROUP->value,   'parent_name' => 'Fixed Operating Expenses'],
                ['code' => '06.01.01.03',       'name' => 'Water Cost',                         'can_have_child' => 0,  'account_type' => AccountType::OPERATING_EXPENSE_USER_SET_EXPENSE_GROUP->value, 'parent' => AccountType::OPERATING_EXPENSE_USER_SET_EXPENSE_GROUP->value,   'parent_name' => 'Fixed Operating Expenses'],
                ['code' => '06.01.01.04',       'name' => 'Internet Cost',                      'can_have_child' => 0,  'account_type' => AccountType::OPERATING_EXPENSE_USER_SET_EXPENSE_GROUP->value, 'parent' => AccountType::OPERATING_EXPENSE_USER_SET_EXPENSE_GROUP->value,   'parent_name' => 'Fixed Operating Expenses'],
                ['code' => '06.01.02',          'name' => 'Variable Operating Expenses',        'can_have_child' => 1,  'account_type' => AccountType::OPERATING_EXPENSE_USER_SET_EXPENSE_GROUP->value, 'parent' => AccountType::OPERATING_EXPENSE->value,                  'parent_name' => 'Operating Expenses'],
                ['code' => '06.01.02.01',       'name' => 'Fuel Cost',                          'can_have_child' => 0,  'account_type' => AccountType::OPERATING_EXPENSE_USER_SET_EXPENSE_GROUP->value, 'parent' => AccountType::OPERATING_EXPENSE_USER_SET_EXPENSE_GROUP->value,   'parent_name' => 'Variable Operating Expenses'],
                ['code' => '06.01.02.02',       'name' => 'Office Stationery Cost',             'can_have_child' => 0,  'account_type' => AccountType::OPERATING_EXPENSE_USER_SET_EXPENSE_GROUP->value, 'parent' => AccountType::OPERATING_EXPENSE_USER_SET_EXPENSE_GROUP->value,   'parent_name' => 'Variable Operating Expenses'],
                ['code' => '06.01.02.03',       'name' => 'Renovation Costs',                   'can_have_child' => 0,  'account_type' => AccountType::OPERATING_EXPENSE_USER_SET_EXPENSE_GROUP->value, 'parent' => AccountType::OPERATING_EXPENSE_USER_SET_EXPENSE_GROUP->value,   'parent_name' => 'Variable Operating Expenses'],
                ['code' => '06.01.03',          'name' => 'Entertainment Fee',                  'can_have_child' => 0,  'account_type' => AccountType::OPERATING_EXPENSE_USER_SET_EXPENSE_GROUP->value, 'parent' => AccountType::OPERATING_EXPENSE->value,                  'parent_name' => 'Operating Expenses'],

                ['code' => '07.01.01.01',   'name' => 'A Bank Interest',                        'can_have_child' => 0,  'account_type' => AccountType::OTHER_INCOME_USER_SET_INCOME_GROUP->value,       'parent' => AccountType::OTHER_INCOME_USER_SET_INCOME_GROUP->value, 'parent_name' => 'Bank Interest'],
                ['code' => '07.01.01.02',   'name' => 'B Bank Interest',                        'can_have_child' => 0,  'account_type' => AccountType::OTHER_INCOME_USER_SET_INCOME_GROUP->value,       'parent' => AccountType::OTHER_INCOME_USER_SET_INCOME_GROUP->value, 'parent_name' => 'Bank Interest'],

                ['code' => '08.01.01',  'name' => 'Bank Administration Fee',                    'can_have_child' => 1,  'account_type' => AccountType::OTHER_EXPENSE_USER_SET_EXPENSE_GROUP->value,     'parent' => AccountType::OTHER_EXPENSE->value,                      'parent_name' => 'Other Expenses'],

            ];

            $resultArr = [];
            for ($i = 0; $i < count($coaArr); $i++) {
                $coa = new ChartOfAccount();
                $coa->company_id = $companyId;
                $coa->branch_id = $branchId;

                $coa->parent_id = null;

                $parentAcc = $coaArr[$i]['parent'];
                if ($parentAcc) {
                    $coaParentId = ChartOfAccount::whereCompanyId($companyId)->where('account_type', '=', $parentAcc);

                    $parentName = $coaArr[$i]['parent_name'];
                    if ($parentName) {
                        $coaParentId = $coaParentId->where('name', '=', $parentName);
                    }

                    $coa->parent_id = $coaParentId->first()->id;
                }

                $coa->code = $coaArr[$i]['code'];
                $coa->name = $coaArr[$i]['name'];
                $coa->can_have_child = $coaArr[$i]['can_have_child'];
                $coa->account_type = $coaArr[$i]['account_type'];
                $coa->status = 1;
                $coa->save();

                array_push($resultArr, $coa);
            }

            DB::commit();

            $this->flushCache();

            return $resultArr;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '' : auth()->id()).'] '.__METHOD__.$e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '' : auth()->id()).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }

    public function listOld(
        int $companyId,
        ?int $branchId = null,
        ?string $parentAccountType = null,
        string $search = '',
        bool $paginate = true,
        int $page = 1,
        ?int $perPage = 10,
        array $with = [],
        bool $withTrashed = false,
        bool $useCache = true
    ): Paginator|Collection {
        $timer_start = microtime(true);

        try {
            $cacheKey = '';
            if ($useCache) {
                $cacheKey = 'read_'.$companyId.'-'.(empty($search) ? '[empty]' : $search).'-'.$paginate.'-'.$page.'-'.$perPage;
                $cacheResult = $this->readFromCache($cacheKey);

                if (! is_null($cacheResult)) {
                    return $cacheResult;
                }
            }

            $result = null;

            if (count($with) != 0) {
                $chartOfAccount = ChartOfAccount::with($with);
            } else {
                $chartOfAccount = ChartOfAccount::with('parentAccount', 'childAccounts');
            }

            $chartOfAccount = $chartOfAccount->whereCompanyId($companyId);

            if ($branchId) {
                $chartOfAccount = $chartOfAccount->whereBranchId($branchId);
            }

            if ($parentAccountType) {
                $parent_id = ChartOfAccount::whereCompanyId($companyId)->where('account_type', '=', $parentAccountType)->first()->id;
                $chartOfAccount = $chartOfAccount->where('parent_id', '=', $parent_id);
            }

            if ($withTrashed) {
                $chartOfAccount = $chartOfAccount->withTrashed();
            }

            if ($search) {
                $chartOfAccount = $chartOfAccount->where('name', 'like', '%'.$search.'%');
            }

            $chartOfAccount = $chartOfAccount->orderBy('code', 'ASC');

            if ($paginate) {
                $perPage = is_numeric($perPage) ? $perPage : Config::get('dcslab.PAGINATION_LIMIT');
                $result = $chartOfAccount->paginate(perPage: abs($perPage), page: abs($page));
            } else {
                $result = $chartOfAccount->get();
            }

            if ($useCache) {
                $this->saveToCache($cacheKey, $result);
            }

            return $result;
        } catch (Exception $e) {
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '' : auth()->id()).'] '.__METHOD__.$e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '' : auth()->id()).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)'.($useCache ? ' (C)' : ' (DB)'));
        }
    }

    public function readAny(
        int $companyId,
        ?int $branchId = null,
        string $search = '',
        bool $paginate = true,
        int $page = 1,
        ?int $perPage = 10,
        array $with = [],
        bool $withTrashed = false,
        bool $useCache = true
    ): array {
        $timer_start = microtime(true);

        try {
            $cacheKey = '';
            if ($useCache) {
                $cacheKey = 'read_'.$companyId.'-'.(empty($search) ? '[empty]' : $search).'-'.$paginate.'-'.$page.'-'.$perPage;
                $cacheResult = $this->readFromCache($cacheKey);

                if (! is_null($cacheResult)) {
                    return $cacheResult;
                }
            }

            $result = null;

            if (count($with) != 0) {
                $chartOfAccount = ChartOfAccount::with($with);
            } else {
                $chartOfAccount = ChartOfAccount::with('childAccounts');
            }

            $chartOfAccount = $chartOfAccount->whereCompanyId($companyId);

            if ($branchId) {
                $chartOfAccount = $chartOfAccount->whereBranchId($branchId);
            }

            if ($withTrashed) {
                $chartOfAccount = $chartOfAccount->withTrashed();
            }

            if ($search) {
                $chartOfAccount = $chartOfAccount->where('name', 'like', '%'.$search.'%');
            }

            $chartOfAccount = $chartOfAccount->where('parent_id', '=', null);

            $chartOfAccount = $chartOfAccount->orderBy('code', 'ASC');

            if ($paginate) {
                $perPage = is_numeric($perPage) ? $perPage : Config::get('dcslab.PAGINATION_LIMIT');
                $result = $chartOfAccount->paginate(perPage: abs($perPage), page: abs($page));
            } else {
                $result = $chartOfAccount->get();
            }

            if ($useCache) {
                $this->saveToCache($cacheKey, $result);
            }

            return $result;
        } catch (Exception $e) {
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '' : auth()->id()).'] '.__METHOD__.$e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '' : auth()->id()).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)'.($useCache ? ' (C)' : ' (DB)'));
        }
    }

    public function listFormated(
        int $companyId,
        ?int $branchId = null,
        string $search = '',
        array $with = [],
        bool $withTrashed = false,
        bool $useCache = true
    ): array {
        $timer_start = microtime(true);

        try {
            $cacheKey = '';
            if ($useCache) {
                $cacheKey = 'read_'.$companyId.'-'.(empty($search) ? '[empty]' : $search);
                $cacheResult = $this->readFromCache($cacheKey);

                if (! is_null($cacheResult)) {
                    return $cacheResult;
                }
            }

            $result = null;

            if (count($with) != 0) {
                $chartOfAccount = ChartOfAccount::with($with);
            } else {
                $chartOfAccount = ChartOfAccount::with(['childrenRecursive' => function ($query) {
                    $query->with('childrenRecursive');
                }])->whereNull('parent_id');
            }

            $chartOfAccount = $chartOfAccount->whereCompanyId($companyId);

            if ($branchId) {
                $chartOfAccount = $chartOfAccount->whereBranchId($branchId);
            }

            if ($withTrashed) {
                $chartOfAccount = $chartOfAccount->withTrashed();
            }

            if ($search) {
                $chartOfAccount = $chartOfAccount->where('name', 'like', '%'.$search.'%');
            }

            $chartOfAccount = $chartOfAccount->where('parent_id', '=', null);

            $chartOfAccounts = $chartOfAccount->orderBy('code', 'ASC')->get();

            $result = $this->formatChartOfAccounts($chartOfAccounts);

            if ($useCache) {
                $this->saveToCache($cacheKey, $result);
            }

            return $result;
        } catch (Exception $e) {
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '' : auth()->id()).'] '.__METHOD__.$e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '' : auth()->id()).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)'.($useCache ? ' (C)' : ' (DB)'));
        }
    }

    protected function formatChartOfAccounts($chartOfAccounts)
    {
        $formattedChartOfAccounts = [];

        foreach ($chartOfAccounts as $chartOfAccount) {
            $formattedChartOfAccount = [
                'hId' => $chartOfAccount->hId,
                'uuid' => $chartOfAccount->uuid,
                'code' => $chartOfAccount->code,
                'name' => $chartOfAccount->name,
                'account_type' => $chartOfAccount->account_type->name,
                'can_have_child' => $chartOfAccount->can_have_child,
                'remarks' => $chartOfAccount->remarks,
                'status' => $chartOfAccount->status->name,
                'nodes' => [],
            ];

            if ($chartOfAccount->childrenRecursive->count()) {
                $formattedChartOfAccount['nodes'] = $this->formatChartOfAccounts($chartOfAccount->childrenRecursive);
            }

            $formattedChartOfAccounts[] = $formattedChartOfAccount;
        }

        return $formattedChartOfAccounts;
    }

    public function read(ChartOfAccount $chartOfAccount): ChartOfAccount
    {
        return $chartOfAccount = ChartOfAccount::with('parentAccount', 'childAccounts')->where('id', '=', $chartOfAccount->id)->first();
    }

    public function readBy(string $key, string $value)
    {
        $timer_start = microtime(true);

        try {
            switch (strtoupper($key)) {
                case 'ID':
                    return ChartOfAccount::find($value);
                    break;
                default:
                    break;
            }
        } catch (Exception $e) {
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '' : auth()->id()).'] '.__METHOD__.$e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '' : auth()->id()).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }

    public function update(
        ChartOfAccount $chartOfAccount,
        array $chartOfAccountArr
    ): ChartOfAccount {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $code = $chartOfAccountArr['code'];
            $name = $chartOfAccountArr['name'];
            $account_type = $chartOfAccountArr['account_type'];
            $can_have_child = $chartOfAccountArr['can_have_child'];
            $status = $chartOfAccountArr['status'];
            $remarks = $chartOfAccountArr['remarks'];

            $result = $chartOfAccount->update([
                'code' => $code,
                'name' => $name,
                'account_type' => $account_type,
                'can_have_child' => $can_have_child,
                'remarks' => $remarks,
                'status' => $status,
            ]);

            $result = $result;

            DB::commit();

            $this->flushCache();

            return $chartOfAccount->refresh();
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '' : auth()->id()).'] '.__METHOD__.$e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '' : auth()->id()).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }

    public function delete(ChartOfAccount $chartOfAccount): bool
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        $retval = false;

        try {
            $retval = $chartOfAccount->delete();

            DB::commit();

            $this->flushCache();

            return $retval;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '' : auth()->id()).'] '.__METHOD__.$e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '' : auth()->id()).'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }

    public function generateUniqueCode(): string
    {
        $rand = new RandomizerActions();
        $code = $rand->generateAlpha().$rand->generateNumeric();

        return $code;
    }

    public function isUniqueCode(?int $parentId, string $code, int $companyId, ?int $exceptId = null): bool
    {
        $result = ChartOfAccount::whereCompanyId($companyId)->where('code', '=', $code);

        if ($parentId) {
            $result = $result->where('parent_id', '=', $parentId);
        }

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0 ? true : false;
    }

    public function getActiveAccount(
        int $companyId,
        int $branchId,
        array $accountTypes = [],
        ?int $includingId = null
    ): Collection {
        $chartOfAccounts = ChartOfAccount::whereCompanyId($companyId)
                                         ->whereBranchId($branchId)
                                         ->where('can_have_child', '=', 0)
                                         ->where('status', '=', RecordStatus::ACTIVE->value);

        $chartOfAccounts = $chartOfAccounts->where(function ($query) use ($accountTypes) {
            for ($i = 0; $i < count($accountTypes); $i++) {
                if ($i == 0 && $accountTypes[$i]) {
                    $query->where('account_type', '=', $accountTypes[$i]);
                } else {
                    if ($accountTypes[$i]) {
                        $query->orWhere('account_type', '=', $accountTypes[$i]);
                    }
                }
            }
        });

        if ($includingId) {
            $chartOfAccounts = $chartOfAccounts->orWhere('id', '=', $includingId)->withTrashed();
        }

        return $chartOfAccounts->orderBy('code', 'ASC')->orderBy('name', 'ASC')->get();
    }
}
