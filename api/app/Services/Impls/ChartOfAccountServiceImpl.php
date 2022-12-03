<?php

namespace App\Services\Impls;

use App\Actions\RandomGenerator;
use App\Enums\AccountType;
use App\Models\ChartOfAccount;
use App\Services\ChartOfAccountService;
use App\Traits\CacheHelper;
use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ChartOfAccountServiceImpl implements ChartOfAccountService
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
            $company_id = $chartOfAccountArr['company_id'];
            $parent_id = $chartOfAccountArr['parent_id'];
            $code = $chartOfAccountArr['code'];
            $name = $chartOfAccountArr['name'];
            $account_type = $chartOfAccountArr['account_type'];
            $remarks = $chartOfAccountArr['remarks'];

            $chartOfAccount = new ChartOfAccount();
            $chartOfAccount->company_id = $company_id;
            $chartOfAccount->parent_id = $parent_id;
            $chartOfAccount->code = $code;
            $chartOfAccount->name = $name;
            $chartOfAccount->account_type = $account_type;
            $chartOfAccount->remarks = $remarks;

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

    public function createRootAccount(
        int $companyId
    ): array {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $coaArr = [
                ['code' => '01', 'name' => 'Assets', 'account_type' => AccountType::ASSETS->value, 'parent' => null],
                    ['code' => '01.01', 'name' => 'Current Asset', 'account_type' => AccountType::CURRENT_ASSET->value, 'parent' => AccountType::ASSETS->value],
                        ['code' => '01.01.01', 'name' => 'Cash And Bank', 'account_type' => AccountType::CASH_AND_BANKS->value, 'parent' => AccountType::CURRENT_ASSET->value],
                        ['code' => '01.01.02', 'name' => 'Receivables', 'account_type' => AccountType::RECEIVABLES->value, 'parent' => AccountType::CURRENT_ASSET->value],
                            ['code' => '01.01.02.01', 'name' => 'Revenue Receivable', 'account_type' => AccountType::REVENUE_RECEIVABLE->value, 'parent' => AccountType::RECEIVABLES->value],
                            ['code' => '01.01.02.02', 'name' => 'Other Receivable', 'account_type' => AccountType::OTHER_RECEIVABLE->value, 'parent' => AccountType::RECEIVABLES->value],
                            ['code' => '01.01.02.03', 'name' => 'Purchase Order (PO) Down Payment Receivable', 'account_type' => AccountType::PURCHASE_ORDER_DOWN_PAYMENT_RECEIVABLE->value, 'parent' => AccountType::RECEIVABLES->value],
                            ['code' => '01.01.02.04', 'name' => 'Purchase Refund Receivable', 'account_type' => AccountType::PURCHASE_REFUND_RECEIVABLE->value, 'parent' => AccountType::RECEIVABLES->value],
                            ['code' => '01.01.02.05', 'name' => 'Purchase Return Receivable', 'account_type' => AccountType::PURCHASE_RETURN_RECEIVABLE->value, 'parent' => AccountType::RECEIVABLES->value],
                            ['code' => '01.01.02.06', 'name' => 'Asset Sales Receivable', 'account_type' => AccountType::ASSET_SALES_RECEIVABLE->value, 'parent' => AccountType::RECEIVABLES->value],
                            ['code' => '01.01.02.07', 'name' => 'Sales Receivable', 'account_type' => AccountType::SALES_RECEIVABLE->value, 'parent' => AccountType::RECEIVABLES->value],
                            ['code' => '01.01.02.08', 'name' => 'Value Added Tax (VAT) Receivable', 'account_type' => AccountType::VAT_RECEIVABLE->value, 'parent' => AccountType::RECEIVABLES->value],
                        ['code' => '01.01.03', 'name' => 'Prepaid Expense', 'account_type' => AccountType::PREPAID_EXPENSE->value, 'parent' => AccountType::CURRENT_ASSET->value],
                        ['code' => '01.01.04', 'name' => 'Inventory', 'account_type' => AccountType::INVENTORY->value, 'parent' => AccountType::CURRENT_ASSET->value],
                    ['code' => '01.02', 'name' => 'Fixed Asset', 'account_type' => AccountType::FIXED_ASSET->value, 'parent' => AccountType::ASSETS->value],
                
                ['code' => '02', 'name' => 'Liabilities', 'account_type' => AccountType::LIABILITIES->value, 'parent' => null],
                    ['code' => '02.01', 'name' => 'Short Term Liability', 'account_type' => AccountType::SHORT_TERM_LIABILITY->value, 'parent' => AccountType::LIABILITIES->value],
                        ['code' => '02.01.01', 'name' => 'Expense Payable', 'account_type' => AccountType::EXPENSE_PAYABLE->value, 'parent' => AccountType::SHORT_TERM_LIABILITY->value],
                        ['code' => '02.01.02', 'name' => 'Prepaid Expense Payable', 'account_type' => AccountType::PREPAID_EXPENSE_PAYABLE->value, 'parent' => AccountType::SHORT_TERM_LIABILITY->value],
                        ['code' => '02.01.03', 'name' => 'Other Payable', 'account_type' => AccountType::OTHER_PAYABLE->value, 'parent' => AccountType::SHORT_TERM_LIABILITY->value],
                        ['code' => '02.01.04', 'name' => 'Sales Order (SO) Down Payment Payable', 'account_type' => AccountType::SALES_ORDER_DOWN_PAYMENT_PAYABLE->value, 'parent' => AccountType::SHORT_TERM_LIABILITY->value],
                        ['code' => '02.01.05', 'name' => 'Purchase Payable', 'account_type' => AccountType::PURCHASE_PAYABLE->value, 'parent' => AccountType::SHORT_TERM_LIABILITY->value],
                        ['code' => '02.01.06', 'name' => 'Production Process Direct Cost Payable', 'account_type' => AccountType::PRODUCTION_PROCESS_DIRECT_COST_PAYABLE->value, 'parent' => AccountType::SHORT_TERM_LIABILITY->value],
                        ['code' => '02.01.07', 'name' => 'Production Process Period Cost Payable', 'account_type' => AccountType::PRODUCTION_PROCESS_PERIOD_COST_PAYABLE->value, 'parent' => AccountType::SHORT_TERM_LIABILITY->value],
                        ['code' => '02.01.08', 'name' => 'Asset Purchase Payable', 'account_type' => AccountType::ASSET_PURCHASE_PAYABLE->value, 'parent' => AccountType::SHORT_TERM_LIABILITY->value],
                        ['code' => '02.01.09', 'name' => 'Sales Additional Expense Payable', 'account_type' => AccountType::SALES_ADDITIONAL_EXPENSE_PAYABLE->value, 'parent' => AccountType::SHORT_TERM_LIABILITY->value],
                        ['code' => '02.01.10', 'name' => 'Sales Returns Payable', 'account_type' => AccountType::SALES_RETURNS_PAYABLE->value, 'parent' => AccountType::SHORT_TERM_LIABILITY->value],
                        ['code' => '02.01.11', 'name' => 'Value Added Tax (VAT) Payable', 'account_type' => AccountType::VAT_PAYABLE->value, 'parent' => AccountType::SHORT_TERM_LIABILITY->value],
                    ['code' => '02.02', 'name' => 'Long Term Liability', 'account_type' => AccountType::LONG_TERM_LIABILITY->value, 'parent' => AccountType::LIABILITIES->value],
                        ['code' => '02.02.01', 'name' => 'Other Debt', 'account_type' => AccountType::OTHER_DEBT->value, 'parent' => AccountType::LONG_TERM_LIABILITY->value],
                        ['code' => '02.02.02', 'name' => 'Asset Purchase Debt', 'account_type' => AccountType::ASSET_PURCHASE_DEBT->value, 'parent' => AccountType::LONG_TERM_LIABILITY->value],
                
                ['code' => '03', 'name' => 'Equity', 'account_type' => AccountType::EQUITY->value, 'parent' => null],
                    ['code' => '03.01', 'name' => 'Capital', 'account_type' => AccountType::CAPITAL->value, 'parent' => AccountType::EQUITY->value],
                    ['code' => '03.02', 'name' => 'Income Statement Account', 'account_type' => AccountType::INCOME_STATEMENT_ACCOUNT->value, 'parent' => AccountType::EQUITY->value],
                        ['code' => '03.02.01', 'name' => 'Retained Earning', 'account_type' => AccountType::RETAINED_EARNING->value, 'parent' => AccountType::INCOME_STATEMENT_ACCOUNT->value],
                        ['code' => '03.02.02', 'name' => 'Current Month Profit', 'account_type' => AccountType::CURRENT_MONTH_PROFIT->value, 'parent' => AccountType::INCOME_STATEMENT_ACCOUNT->value],
                        ['code' => '03.02.03', 'name' => 'Current Year Profit', 'account_type' => AccountType::CURRENT_YEAR_PROFIT->value, 'parent' => AccountType::INCOME_STATEMENT_ACCOUNT->value],
                
                ['code' => '04', 'name' => 'Operating Revenue', 'account_type' => AccountType::OPERATING_REVENUES->value, 'parent' => null],
                    ['code' => '04.01', 'name' => 'Sales', 'account_type' => AccountType::SALES->value, 'parent' => AccountType::OPERATING_REVENUES->value],
                        ['code' => '04.01.01', 'name' => 'Product Sales', 'account_type' => AccountType::PRODUCT_SALES->value, 'parent' => AccountType::SALES->value],
                        ['code' => '04.01.02', 'name' => 'Service Sales', 'account_type' => AccountType::SERVICE_SALES->value, 'parent' => AccountType::SALES->value],
                        ['code' => '04.01.03', 'name' => 'Sales Discount', 'account_type' => AccountType::SALES_DISCOUNTS->value, 'parent' => AccountType::SALES->value],
                        ['code' => '04.01.04', 'name' => 'Sales Value Added', 'account_type' => AccountType::SALES_VALUE_ADDED->value, 'parent' => AccountType::SALES->value],
                        ['code' => '04.01.05', 'name' => 'Sales Rounding', 'account_type' => AccountType::SALES_ROUNDING->value, 'parent' => AccountType::SALES->value],
                        ['code' => '04.01.06', 'name' => 'Sales Returns', 'account_type' => AccountType::SALES_RETURNS->value, 'parent' => AccountType::SALES->value],
                    ['code' => '04.03', 'name' => 'Receivable Revenue', 'account_type' => AccountType::RECEIVABLE_REVENUE->value, 'parent' => AccountType::OPERATING_REVENUES->value],
                
                ['code' => '05', 'name' => 'Cost of Goods Sold', 'account_type' => AccountType::COST_OF_GOODS_SOLD->value, 'parent' => null],
                    ['code' => '05.01', 'name' => 'Cost of Goods Sold', 'account_type' => AccountType::SALES_COST_OF_GOODS_SOLD->value, 'parent' => AccountType::COST_OF_GOODS_SOLD->value],
                    ['code' => '05.02', 'name' => 'Purchase Summary', 'account_type' => AccountType::PURCHASE_SUMMARY->value, 'parent' => AccountType::COST_OF_GOODS_SOLD->value],
                    ['code' => '05.02.01', 'name' => 'Gross Purchase', 'account_type' => AccountType::GROSS_PURCHASE->value, 'parent' => AccountType::COST_OF_GOODS_SOLD->value],
                    ['code' => '05.02.02', 'name' => 'Purchase Discount', 'account_type' => AccountType::PURCHASE_DISCOUNT->value, 'parent' => AccountType::COST_OF_GOODS_SOLD->value],
                    ['code' => '05.02.03', 'name' => 'Additional Purchase Cost', 'account_type' => AccountType::ADDITIONAL_PURCHASE_COST->value, 'parent' => AccountType::COST_OF_GOODS_SOLD->value],
                    ['code' => '05.02.04', 'name' => 'Purchase Refund', 'account_type' => AccountType::PURCHASE_REFUND->value, 'parent' => AccountType::COST_OF_GOODS_SOLD->value],
                    ['code' => '05.02.05', 'name' => 'Purchase Return', 'account_type' => AccountType::PURCHASE_RETURN->value, 'parent' => AccountType::COST_OF_GOODS_SOLD->value],
                
                ['code' => '06', 'name' => 'Operating Expenses', 'account_type' => AccountType::OPERATING_EXPENSES->value, 'parent' => null],
                    ['code' => '06.02', 'name' => 'Debt Cost', 'account_type' => AccountType::DEBT_COST->value, 'parent' => AccountType::OPERATING_EXPENSES->value],
                    ['code' => '06.03', 'name' => 'Depreciation Load', 'account_type' => AccountType::DEPRECIATION_LOAD->value, 'parent' => AccountType::OPERATING_EXPENSES->value],

                ['code' => '07', 'name' => 'Other Income', 'account_type' => AccountType::OTHER_INCOMES->value, 'parent' => null],
                    ['code' => '07.02', 'name' => 'Stock Adjustment Difference', 'account_type' => AccountType::OTHER_INCOMES_STOCK_ADJUSTMENT_DIFFERENCE->value, 'parent' => AccountType::OTHER_INCOMES->value],
                    ['code' => '07.03', 'name' => 'Asset Adjustment Difference', 'account_type' => AccountType::OTHER_INCOMES_ASSET_ADJUSTMENT_DIFFERENCE->value, 'parent' => AccountType::OTHER_INCOMES->value],
                    ['code' => '07.04', 'name' => 'Asset Sales Difference', 'account_type' => AccountType::OTHER_INCOMES_ASSET_SALES_DIFFERENCE->value, 'parent' => AccountType::OTHER_INCOMES->value],
                    ['code' => '07.05', 'name' => 'Production Process Difference', 'account_type' => AccountType::OTHER_INCOMES_PRODUCTION_PROCESS_DIFFERENCE->value, 'parent' => AccountType::OTHER_INCOMES->value],
                    ['code' => '07.06', 'name' => 'Value Added Tax (VAT) Receivable Adjustment', 'account_type' => AccountType::OTHER_INCOMES_VAT_RECEIVABLE_ADJUSTMENT->value, 'parent' => AccountType::OTHER_INCOMES->value],

                ['code' => '08', 'name' => 'Other Expenses', 'account_type' => AccountType::OTHER_EXPENSES->value, 'parent' => null],
                    ['code' => '08.02', 'name' => 'Stock Adjustment Difference', 'account_type' => AccountType::OTHER_EXPENSES_STOCK_ADJUSTMENT_DIFFERENCE->value, 'parent' => AccountType::OTHER_EXPENSES->value],
                    ['code' => '08.03', 'name' => 'Asset Adjustment Difference', 'account_type' => AccountType::OTHER_EXPENSES_ASSET_ADJUSTMENT_DIFFERENCE->value, 'parent' => AccountType::OTHER_EXPENSES->value],
                    ['code' => '08.04', 'name' => 'Asset Sales Difference', 'account_type' => AccountType::OTHER_EXPENSES_ASSET_SALES_DIFFERENCE->value, 'parent' => AccountType::OTHER_EXPENSES->value],
                    ['code' => '08.05', 'name' => 'Production Process Difference', 'account_type' => AccountType::OTHER_EXPENSES_PRODUCTION_PROCESS_DIFFERENCE->value, 'parent' => AccountType::OTHER_EXPENSES->value],
                    ['code' => '08.06', 'name' => 'Value Added Tax (VAT) Receivable Adjustment', 'account_type' => AccountType::OTHER_EXPENSES_VAT_PAYABLE_ADJUSTMENT->value, 'parent' => AccountType::OTHER_EXPENSES->value],
                                
                ['code' => '09', 'name' => 'Profit Snd Loss Summary', 'account_type' => AccountType::PROFIT_AND_LOSS_SUMMARY->value, 'parent' => null],
            ];

            $resultArr = [];
            for ($i = 0; $i < count($coaArr); $i++) {
                $coa = new ChartOfAccount();
                $coa->company_id = $companyId;
                
                $coa->parent_id = null;
                if ($coaArr[$i]['parent']) {
                    $coa->parent_id = ChartOfAccount::whereCompanyId($companyId)->where('account_type' ,'=', $coaArr[$i]['parent'])->first()->id;
                }
                
                $coa->code = $coaArr[$i]['code'];
                $coa->name = $coaArr[$i]['name'];
                $coa->account_type = $coaArr[$i]['account_type'];
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

    public function list(
        int $companyId,
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

                if (!is_null($cacheResult)) {
                    return $cacheResult;
                }
            }

            $result = null;

            if (count($with) != 0) {
                $chartOfAccount = ChartOfAccount::with($with)->whereCompanyId($companyId);
            } else {
                $chartOfAccount = ChartOfAccount::with('parentAccount')->whereCompanyId($companyId);
            }

            if ($withTrashed)
                $chartOfAccount = $chartOfAccount->withTrashed();

            if (empty($search)) {
                $pb = ChartOfAccount::whereCompanyId($companyId)->latest();
            } else {
                $pb = ChartOfAccount::whereCompanyId($companyId)->where('name', 'like', '%'.$search.'%')->latest();
            }

            if ($paginate) {
                $perPage = is_numeric($perPage) ? $perPage : Config::get('dcslab.PAGINATION_LIMIT');
                $result = $pb->paginate(perPage: abs($perPage), page: abs($page));
            } else {
                $result = $pb->get();
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

    public function read(ChartOfAccount $chartOfAccount): ChartOfAccount
    {
        return $chartOfAccount->first();
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
            $remarks = $chartOfAccountArr['remarks'];

            $result = $chartOfAccount->update([
                'code' => $code,
                'name' => $name,
                'account_type' => $account_type,
                'remarks' => $remarks,
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

    public function generateUniqueCode(int $companyId, ?int $parentId): string
    {
        $query = ChartOfAccount::where('company_id', '=', $companyId);

        if ($parentId) {
            $query = $query->where('parent_id', '=', $parentId);
        }

        $code = $query->count();

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
}
