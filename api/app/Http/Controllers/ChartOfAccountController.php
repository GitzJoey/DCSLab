<?php

namespace App\Http\Controllers;

use App\Actions\ChartOfAccount\ChartOfAccountActions;
use App\Enums\AccountType;
use App\Http\Requests\ChartOfAccountRequest;
use App\Http\Resources\ChartOfAccountResource;
use App\Models\ChartOfAccount;
use Exception;

class ChartOfAccountController extends Controller
{
    private $chartOfAccountActions;

    public function __construct(ChartOfAccountActions $chartOfAccountActions)
    {
        parent::__construct();

        $this->middleware('auth');
        $this->chartOfAccountActions = $chartOfAccountActions;
    }

    public function store(ChartOfAccountRequest $chartOfAccountRequest)
    {
        $request = $chartOfAccountRequest->validated();

        $company_id = $request['company_id'];
        $branch_id = $request['branch_id'];
        $parent_id = $request['parent_id'];

        $code = $request['code'];
        if ($code == config('dcslab.KEYWORDS.AUTO')) {
            $loopCount = 0;
            do {
                $loopCount = $loopCount + 1;

                $code = $this->chartOfAccountActions->generateUniqueCode($company_id, $parent_id) + $loopCount;
            } while (! $this->chartOfAccountActions->isUniqueCode($parent_id, str_pad($code, 2, '0', STR_PAD_LEFT), $company_id));
            $code = str_pad($code, 2, '0', STR_PAD_LEFT);
        } else {
            if (! $this->chartOfAccountActions->isUniqueCode($parent_id, $code, $company_id)) {
                return response()->error([
                    'code' => [trans('rules.unique_code')],
                ], 422);
            }
        }

        $parentCOA = ChartOfAccount::find($parent_id);

        $chartOfAccountArr = [
            'company_id' => $company_id,
            'branch_id' => $branch_id,
            'parent_id' => $parent_id,
            'code' => $parentCOA->code.'.'.$code,
            'name' => $request['name'],
            'account_type' => $request['account_type'],
            'can_have_child' => $request['can_have_child'],
            'remarks' => $request['remarks'],
            'status' => $request['status'],
        ];

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->chartOfAccountActions->create($chartOfAccountArr);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function readAny(ChartOfAccountRequest $chartOfAccountRequest)
    {
        $request = $chartOfAccountRequest->validated();

        $companyId = $request['company_id'];
        $branchId = $request['branch_id'];
        $search = $request['search'];
        $paginate = $request['paginate'];
        $page = array_key_exists('page', $request) ? abs($request['page']) : 1;
        $perPage = array_key_exists('per_page', $request) ? abs($request['per_page']) : 10;
        $useCache = array_key_exists('refresh', $request) ? boolval($request['refresh']) : true;

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->chartOfAccountActions->readAny(
                companyId: $companyId,
                branchId: $branchId,
                search: $search,
                paginate: false,
                page: $page,
                perPage: $perPage,
                useCache: $useCache
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            $response = ChartOfAccountResource::collection($result);

            return $response;
        }
    }

    public function readFormated(ChartOfAccountRequest $chartOfAccountRequest)
    {
        $request = $chartOfAccountRequest->validated();

        $companyId = $request['company_id'];
        $branchId = $request['branch_id'];
        $search = $request['search'];
        $useCache = array_key_exists('refresh', $request) ? boolval($request['refresh']) : true;

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->chartOfAccountActions->readFormated(
                companyId: $companyId,
                branchId: $branchId,
                search: $search,
                useCache: $useCache
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            return $result;
        }
    }

    public function read(ChartOfAccount $chartOfAccount, ChartOfAccountRequest $chartOfAccountRequest)
    {
        $request = $chartOfAccountRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->chartOfAccountActions->read($chartOfAccount);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            $response = new ChartOfAccountResource($result);

            return $response;
        }
    }

    public function update(ChartOfAccount $chartOfAccount, ChartOfAccountRequest $chartOfAccountRequest)
    {
        $request = $chartOfAccountRequest->validated();

        $company_id = $request['company_id'];

        $parentCount = $chartOfAccount->parentAccount()->count();

        $parent_id = null;
        if ($parentCount > 0) {
            $parentCOA = $chartOfAccount->parentAccount()->first();
            $parent_id = $parentCOA->id;
        }

        $code = $request['code'];
        if ($code == config('dcslab.KEYWORDS.AUTO')) {
            $loopCount = 0;
            do {
                $loopCount = $loopCount + 1;

                $code = $this->chartOfAccountActions->generateUniqueCode($company_id, $parent_id) + $loopCount;
            } while (! $this->chartOfAccountActions->isUniqueCode($parent_id, str_pad($code, 2, '0', STR_PAD_LEFT), $company_id, $chartOfAccount->id));
            $code = str_pad($code, 2, '0', STR_PAD_LEFT);
        } else {
            if (! $this->chartOfAccountActions->isUniqueCode($parent_id, $code, $company_id, $chartOfAccount->id)) {
                return response()->error([
                    'code' => [trans('rules.unique_code')],
                ], 422);
            }
        }

        if ($parentCount > 0) {
            $code = $parentCOA->code.'.'.$code;
        }

        $chartOfAccountArr = [
            'company_id' => $request['company_id'],
            'parent_id' => $parent_id,
            'code' => $code,
            'name' => $request['name'],
            'account_type' => $request['account_type'],
            'can_have_child' => $request['can_have_child'],
            'remarks' => $request['remarks'],
            'status' => $request['status'],
        ];

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->chartOfAccountActions->update(
                $chartOfAccount,
                $chartOfAccountArr
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(ChartOfAccount $chartOfAccount, ChartOfAccountRequest $chartOfAccountRequest)
    {        
        $result = false;
        $errorMsg = '';

        $childCount = $chartOfAccount->childAccounts()->count();
        if ($childCount > 0) {
            return response()->error([trans('rules.chart_of_account.delete_child')], 422);
        }

        $journalCount = $chartOfAccount->accountingJournals()->count();
        if ($journalCount > 0) {
            return response()->error(['This data cannot be deleted because it has been used in the journal'], 422);
        }

        try {
            $result = $this->chartOfAccountActions->delete($chartOfAccount);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }

    public function getAccountType($accountType)
    {
        switch ($accountType) {
            case AccountType::ASSET->value:
                return [
                    ['name' => 'components.dropdown.values.accountTypeDDL.asset', 'code' => AccountType::ASSET->value],
                ];

                break;
            case AccountType::CURRENT_ASSET->value:
                return [
                    ['name' => 'components.dropdown.values.accountTypeDDL.current_asset', 'code' => AccountType::CURRENT_ASSET->value],
                ];

                break;
            case AccountType::CASH_AND_BANK->value:
                return [
                    ['name' => 'components.dropdown.values.accountTypeDDL.cash', 'code' => AccountType::CASH->value],
                    ['name' => 'components.dropdown.values.accountTypeDDL.bank', 'code' => AccountType::BANK->value],
                ];

                break;
            case AccountType::CASH->value:
                return [
                    ['name' => 'components.dropdown.values.accountTypeDDL.cash', 'code' => AccountType::CASH->value],
                ];

                break;
            case AccountType::BANK->value:
                return [
                    ['name' => 'components.dropdown.values.accountTypeDDL.bank', 'code' => AccountType::BANK->value],
                ];

                break;
            case AccountType::RECEIVABLE->value:
                return [
                    ['name' => 'components.dropdown.values.accountTypeDDL.revenue_receivable', 'code' => AccountType::REVENUE_RECEIVABLE->value],
                    ['name' => 'components.dropdown.values.accountTypeDDL.other_receivable', 'code' => AccountType::OTHER_RECEIVABLE->value],
                    ['name' => 'components.dropdown.values.accountTypeDDL.purchase_order_down_payment_receivable', 'code' => AccountType::PURCHASE_ORDER_DOWN_PAYMENT_RECEIVABLE->value],
                    ['name' => 'components.dropdown.values.accountTypeDDL.purchase_refund_receivable', 'code' => AccountType::PURCHASE_REFUND_RECEIVABLE->value],
                    ['name' => 'components.dropdown.values.accountTypeDDL.purchase_return_receivable', 'code' => AccountType::PURCHASE_RETURN_RECEIVABLE->value],
                    ['name' => 'components.dropdown.values.accountTypeDDL.asset_sales_receivable', 'code' => AccountType::ASSET_SALES_RECEIVABLE->value],
                    ['name' => 'components.dropdown.values.accountTypeDDL.sales_receivable', 'code' => AccountType::SALES_RECEIVABLE->value],
                    ['name' => 'components.dropdown.values.accountTypeDDL.vat_receivable', 'code' => AccountType::VAT_RECEIVABLE->value],
                ];

                break;
            case AccountType::REVENUE_RECEIVABLE->value:
                return [
                    ['name' => 'components.dropdown.values.accountTypeDDL.revenue_receivable', 'code' => AccountType::REVENUE_RECEIVABLE->value],
                ];

                break;
            case AccountType::OTHER_RECEIVABLE->value:
                return [
                    ['name' => 'components.dropdown.values.accountTypeDDL.other_receivable', 'code' => AccountType::OTHER_RECEIVABLE->value],
                ];

                break;
            case AccountType::PURCHASE_ORDER_DOWN_PAYMENT_RECEIVABLE->value:
                return [
                    ['name' => 'components.dropdown.values.accountTypeDDL.purchase_order_down_payment_receivable', 'code' => AccountType::PURCHASE_ORDER_DOWN_PAYMENT_RECEIVABLE->value],
                ];

                break;
            case AccountType::PURCHASE_REFUND_RECEIVABLE->value:
                return [
                    ['name' => 'components.dropdown.values.accountTypeDDL.purchase_refund_receivable', 'code' => AccountType::PURCHASE_REFUND_RECEIVABLE->value],
                ];

                break;
            case AccountType::PURCHASE_RETURN_RECEIVABLE->value:
                return [
                    ['name' => 'components.dropdown.values.accountTypeDDL.purchase_return_receivable', 'code' => AccountType::PURCHASE_RETURN_RECEIVABLE->value],
                ];

                break;
            case AccountType::ASSET_SALES_RECEIVABLE->value:
                return [
                    ['name' => 'components.dropdown.values.accountTypeDDL.asset_sales_receivable', 'code' => AccountType::ASSET_SALES_RECEIVABLE->value],
                ];

                break;
            case AccountType::SALES_RECEIVABLE->value:
                return [
                    ['name' => 'components.dropdown.values.accountTypeDDL.sales_receivable', 'code' => AccountType::SALES_RECEIVABLE->value],
                ];

                break;
            case AccountType::VAT_RECEIVABLE->value:
                return [
                    ['name' => 'components.dropdown.values.accountTypeDDL.vat_receivable', 'code' => AccountType::VAT_RECEIVABLE->value],
                ];

                break;
            case AccountType::PREPAID_EXPENSE->value:
                return [
                    ['name' => 'components.dropdown.values.accountTypeDDL.prepaid_expense', 'code' => AccountType::PREPAID_EXPENSE->value],
                ];

                break;
            case AccountType::INVENTORY->value:
                return [
                    ['name' => 'components.dropdown.values.accountTypeDDL.inventory', 'code' => AccountType::INVENTORY->value],
                ];

                break;
            case AccountType::FIXED_ASSET->value:
                return [
                    ['name' => 'components.dropdown.values.accountTypeDDL.fixed_asset', 'code' => AccountType::FIXED_ASSET->value],
                ];

                break;

            case AccountType::LIABILITY->value:
                return [
                    ['name' => 'components.dropdown.values.accountTypeDDL.liability', 'code' => AccountType::LIABILITY->value],
                ];

                break;
            case AccountType::SHORT_TERM_LIABILITY->value:
                return [
                    ['name' => 'components.dropdown.values.accountTypeDDL.expense_payable', 'code' => AccountType::EXPENSE_PAYABLE->value],
                    ['name' => 'components.dropdown.values.accountTypeDDL.prepaid_expense_payable', 'code' => AccountType::PREPAID_EXPENSE_PAYABLE->value],
                    ['name' => 'components.dropdown.values.accountTypeDDL.other_payable', 'code' => AccountType::OTHER_PAYABLE->value],
                    ['name' => 'components.dropdown.values.accountTypeDDL.sales_order_down_payment_payable', 'code' => AccountType::SALES_ORDER_DOWN_PAYMENT_PAYABLE->value],
                    ['name' => 'components.dropdown.values.accountTypeDDL.purchase_payable', 'code' => AccountType::PURCHASE_PAYABLE->value],
                    ['name' => 'components.dropdown.values.accountTypeDDL.production_process_direct_cost_payable', 'code' => AccountType::PRODUCTION_PROCESS_DIRECT_COST_PAYABLE->value],
                    ['name' => 'components.dropdown.values.accountTypeDDL.production_process_period_cost_payable', 'code' => AccountType::PRODUCTION_PROCESS_PERIOD_COST_PAYABLE->value],
                    ['name' => 'components.dropdown.values.accountTypeDDL.asset_purchase_payable', 'code' => AccountType::ASSET_PURCHASE_PAYABLE->value],
                    ['name' => 'components.dropdown.values.accountTypeDDL.sales_additional_expense_payable', 'code' => AccountType::SALES_ADDITIONAL_EXPENSE_PAYABLE->value],
                    ['name' => 'components.dropdown.values.accountTypeDDL.sales_returns_payable', 'code' => AccountType::SALES_RETURNS_PAYABLE->value],
                    ['name' => 'components.dropdown.values.accountTypeDDL.vat_payable', 'code' => AccountType::VAT_PAYABLE->value],
                ];

                break;
            case AccountType::EXPENSE_PAYABLE->value:
                return [
                    ['name' => 'components.dropdown.values.accountTypeDDL.expense_payable', 'code' => AccountType::EXPENSE_PAYABLE->value],
                ];

                break;
            case AccountType::PREPAID_EXPENSE_PAYABLE->value:
                return [
                    ['name' => 'components.dropdown.values.accountTypeDDL.prepaid_expense_payable', 'code' => AccountType::PREPAID_EXPENSE_PAYABLE->value],
                ];

                break;
            case AccountType::OTHER_PAYABLE->value:
                return [
                    ['name' => 'components.dropdown.values.accountTypeDDL.other_payable', 'code' => AccountType::OTHER_PAYABLE->value],
                ];

                break;
            case AccountType::SALES_ORDER_DOWN_PAYMENT_PAYABLE->value:
                return [
                    ['name' => 'components.dropdown.values.accountTypeDDL.sales_order_down_payment_payable', 'code' => AccountType::SALES_ORDER_DOWN_PAYMENT_PAYABLE->value],
                ];

                break;
            case AccountType::PURCHASE_PAYABLE->value:
                return [
                    ['name' => 'components.dropdown.values.accountTypeDDL.purchase_payable', 'code' => AccountType::PURCHASE_PAYABLE->value],
                ];

                break;
            case AccountType::PRODUCTION_PROCESS_DIRECT_COST_PAYABLE->value:
                return [
                    ['name' => 'components.dropdown.values.accountTypeDDL.production_process_direct_cost_payable', 'code' => AccountType::PRODUCTION_PROCESS_DIRECT_COST_PAYABLE->value],
                ];

                break;
            case AccountType::PRODUCTION_PROCESS_PERIOD_COST_PAYABLE->value:
                return [
                    ['name' => 'components.dropdown.values.accountTypeDDL.production_process_period_cost_payable', 'code' => AccountType::PRODUCTION_PROCESS_PERIOD_COST_PAYABLE->value],
                ];

                break;
            case AccountType::ASSET_PURCHASE_PAYABLE->value:
                return [
                    ['name' => 'components.dropdown.values.accountTypeDDL.asset_purchase_payable', 'code' => AccountType::ASSET_PURCHASE_PAYABLE->value],
                ];

                break;
            case AccountType::SALES_ADDITIONAL_EXPENSE_PAYABLE->value:
                return [
                    ['name' => 'components.dropdown.values.accountTypeDDL.sales_additional_expense_payable', 'code' => AccountType::SALES_ADDITIONAL_EXPENSE_PAYABLE->value],
                ];

                break;
            case AccountType::SALES_RETURNS_PAYABLE->value:
                return [
                    ['name' => 'components.dropdown.values.accountTypeDDL.sales_returns_payable', 'code' => AccountType::SALES_RETURNS_PAYABLE->value],
                ];

                break;
            case AccountType::VAT_PAYABLE->value:
                return [
                    ['name' => 'components.dropdown.values.accountTypeDDL.vat_payable', 'code' => AccountType::VAT_PAYABLE->value],
                ];

                break;
            case AccountType::LONG_TERM_LIABILITY->value:
                return [
                    ['name' => 'components.dropdown.values.accountTypeDDL.other_debt', 'code' => AccountType::OTHER_DEBT->value],
                    ['name' => 'components.dropdown.values.accountTypeDDL.asset_purchase_debt', 'code' => AccountType::ASSET_PURCHASE_DEBT->value],
                ];

                break;
            case AccountType::OTHER_DEBT->value:
                return [
                    ['name' => 'components.dropdown.values.accountTypeDDL.other_debt', 'code' => AccountType::OTHER_DEBT->value],
                ];

                break;
            case AccountType::ASSET_PURCHASE_DEBT->value:
            case AccountType::EQUITY->value:
                return [
                    ['name' => 'components.dropdown.values.accountTypeDDL.equity', 'code' => AccountType::EQUITY->value],
                ];

                break;
            case AccountType::CAPITAL->value:
                return [
                    ['name' => 'components.dropdown.values.accountTypeDDL.capital', 'code' => AccountType::CAPITAL->value],
                ];

                break;
            case AccountType::INCOME_STATEMENT_ACCOUNT->value:
                return [
                    ['name' => 'components.dropdown.values.accountTypeDDL.retained_earning', 'code' => AccountType::RETAINED_EARNING->value],
                    ['name' => 'components.dropdown.values.accountTypeDDL.current_month_profit', 'code' => AccountType::CURRENT_MONTH_PROFIT->value],
                    ['name' => 'components.dropdown.values.accountTypeDDL.current_year_profit', 'code' => AccountType::CURRENT_YEAR_PROFIT->value],
                ];

                break;
            case AccountType::RETAINED_EARNING->value:
                return [
                    ['name' => 'components.dropdown.values.accountTypeDDL.retained_earning', 'code' => AccountType::RETAINED_EARNING->value],
                ];

                break;
            case AccountType::CURRENT_MONTH_PROFIT->value:
                return [
                    ['name' => 'components.dropdown.values.accountTypeDDL.current_month_profit', 'code' => AccountType::CURRENT_MONTH_PROFIT->value],
                ];

                break;
            case AccountType::CURRENT_YEAR_PROFIT->value:
            case AccountType::OPERATING_REVENUE->value:
                return [
                    ['name' => 'components.dropdown.values.accountTypeDDL.sales', 'code' => AccountType::SALES->value],
                    ['name' => 'components.dropdown.values.accountTypeDDL.operating_revenue_user_set_income_group', 'code' => AccountType::OPERATING_REVENUE_USER_SET_INCOME_GROUP->value],
                    ['name' => 'components.dropdown.values.accountTypeDDL.receivable_revenue', 'code' => AccountType::RECEIVABLE_REVENUE->value],
                ];

                break;
            case AccountType::SALES->value:
                return [
                    ['name' => 'components.dropdown.values.accountTypeDDL.product_sales', 'code' => AccountType::PRODUCT_SALES->value],
                    ['name' => 'components.dropdown.values.accountTypeDDL.service_sales', 'code' => AccountType::SERVICE_SALES->value],
                    ['name' => 'components.dropdown.values.accountTypeDDL.sales_discounts', 'code' => AccountType::SALES_DISCOUNTS->value],
                    ['name' => 'components.dropdown.values.accountTypeDDL.sales_value_added', 'code' => AccountType::SALES_VALUE_ADDED->value],
                    ['name' => 'components.dropdown.values.accountTypeDDL.sales_rounding', 'code' => AccountType::SALES_ROUNDING->value],
                    ['name' => 'components.dropdown.values.accountTypeDDL.sales_returns', 'code' => AccountType::SALES_RETURNS->value],
                ];

                break;
            case AccountType::PRODUCT_SALES->value:
                return [
                    ['name' => 'components.dropdown.values.accountTypeDDL.product_sales', 'code' => AccountType::PRODUCT_SALES->value],
                ];

                break;
            case AccountType::SERVICE_SALES->value:
                return [
                    ['name' => 'components.dropdown.values.accountTypeDDL.service_sales', 'code' => AccountType::SERVICE_SALES->value],
                ];

                break;
            case AccountType::SALES_DISCOUNTS->value:
                return [
                    ['name' => 'components.dropdown.values.accountTypeDDL.sales_discounts', 'code' => AccountType::SALES_DISCOUNTS->value],
                ];

                break;
            case AccountType::SALES_VALUE_ADDED->value:
                return [
                    ['name' => 'components.dropdown.values.accountTypeDDL.sales_value_added', 'code' => AccountType::SALES_VALUE_ADDED->value],
                ];

                break;
            case AccountType::SALES_ROUNDING->value:
                return [
                    ['name' => 'components.dropdown.values.accountTypeDDL.sales_rounding', 'code' => AccountType::SALES_ROUNDING->value],
                ];

                break;
            case AccountType::SALES_RETURNS->value:
                return [
                    ['name' => 'components.dropdown.values.accountTypeDDL.sales_returns', 'code' => AccountType::SALES_RETURNS->value],
                ];

                break;
            case AccountType::OPERATING_REVENUE_USER_SET_INCOME_GROUP->value:
                return [
                    ['name' => 'components.dropdown.values.accountTypeDDL.operating_revenue_user_set_income_group', 'code' => AccountType::OPERATING_REVENUE_USER_SET_INCOME_GROUP->value],
                ];

                break;
            case AccountType::RECEIVABLE_REVENUE->value:
                return [
                    ['name' => 'components.dropdown.values.accountTypeDDL.receivable_revenue', 'code' => AccountType::RECEIVABLE_REVENUE->value],
                ];

                break;
            case AccountType::SALES_COST_OF_GOODS_SOLD->value:
                return [
                    ['name' => 'components.dropdown.values.accountTypeDDL.sales_cost_of_goods_sold', 'code' => AccountType::SALES_COST_OF_GOODS_SOLD->value],
                ];

                break;
            case AccountType::PURCHASE_SUMMARY->value:
                return [
                    ['name' => 'components.dropdown.values.accountTypeDDL.gross_purchase', 'code' => AccountType::GROSS_PURCHASE->value],
                    ['name' => 'components.dropdown.values.accountTypeDDL.purchase_discount', 'code' => AccountType::PURCHASE_DISCOUNT->value],
                    ['name' => 'components.dropdown.values.accountTypeDDL.additional_purchase_cost', 'code' => AccountType::ADDITIONAL_PURCHASE_COST->value],
                    ['name' => 'components.dropdown.values.accountTypeDDL.purchase_refund', 'code' => AccountType::PURCHASE_REFUND->value],
                    ['name' => 'components.dropdown.values.accountTypeDDL.purchase_return', 'code' => AccountType::PURCHASE_RETURN->value],
                ];

                break;
            case AccountType::GROSS_PURCHASE->value:
                return [
                    ['name' => 'components.dropdown.values.accountTypeDDL.gross_purchase', 'code' => AccountType::GROSS_PURCHASE->value],
                ];

                break;
            case AccountType::PURCHASE_DISCOUNT->value:
                return [
                    ['name' => 'components.dropdown.values.accountTypeDDL.purchase_discount', 'code' => AccountType::PURCHASE_DISCOUNT->value],
                ];

                break;
            case AccountType::ADDITIONAL_PURCHASE_COST->value:
                return [
                    ['name' => 'components.dropdown.values.accountTypeDDL.additional_purchase_cost', 'code' => AccountType::ADDITIONAL_PURCHASE_COST->value],
                ];

                break;
            case AccountType::PURCHASE_REFUND->value:
                return [
                    ['name' => 'components.dropdown.values.accountTypeDDL.purchase_refund', 'code' => AccountType::PURCHASE_REFUND->value],
                ];

                break;
            case AccountType::PURCHASE_RETURN->value:
                return [
                    ['name' => 'components.dropdown.values.accountTypeDDL.purchase_return', 'code' => AccountType::PURCHASE_RETURN->value],
                ];

                break;
            case AccountType::OPERATING_EXPENSE->value:
                return [
                    ['name' => 'components.dropdown.values.accountTypeDDL.operating_expense_user_set_expense_group', 'code' => AccountType::OPERATING_EXPENSE_USER_SET_EXPENSE_GROUP->value],
                    ['name' => 'components.dropdown.values.accountTypeDDL.debt_cost', 'code' => AccountType::DEBT_COST->value],
                    ['name' => 'components.dropdown.values.accountTypeDDL.depreciation_load', 'code' => AccountType::DEPRECIATION_LOAD->value],
                ];

                break;
            case AccountType::OPERATING_EXPENSE_USER_SET_EXPENSE_GROUP->value:
                return [
                    ['name' => 'components.dropdown.values.accountTypeDDL.operating_expense_user_set_expense_group', 'code' => AccountType::OPERATING_EXPENSE_USER_SET_EXPENSE_GROUP->value],
                ];

                break;
            case AccountType::DEBT_COST->value:
                return [
                    ['name' => 'components.dropdown.values.accountTypeDDL.debt_cost', 'code' => AccountType::DEBT_COST->value],
                ];

                break;
            case AccountType::DEPRECIATION_LOAD->value:
                return [
                    ['name' => 'components.dropdown.values.accountTypeDDL.depreciation_load', 'code' => AccountType::DEPRECIATION_LOAD->value],
                ];

                break;
            case AccountType::OTHER_INCOME->value:
                return [
                    ['name' => 'components.dropdown.values.accountTypeDDL.other_income_user_set_income_group', 'code' => AccountType::OTHER_INCOME_USER_SET_INCOME_GROUP->value],
                    ['name' => 'components.dropdown.values.accountTypeDDL.other_income_stock_adjustment_difference', 'code' => AccountType::OTHER_INCOME_STOCK_ADJUSTMENT_DIFFERENCE->value],
                    ['name' => 'components.dropdown.values.accountTypeDDL.other_income_asset_adjustment_difference', 'code' => AccountType::OTHER_INCOME_ASSET_ADJUSTMENT_DIFFERENCE->value],
                    ['name' => 'components.dropdown.values.accountTypeDDL.other_income_asset_sales_difference', 'code' => AccountType::OTHER_INCOME_ASSET_SALES_DIFFERENCE->value],
                    ['name' => 'components.dropdown.values.accountTypeDDL.other_income_production_process_difference', 'code' => AccountType::OTHER_INCOME_PRODUCTION_PROCESS_DIFFERENCE->value],
                    ['name' => 'components.dropdown.values.accountTypeDDL.other_income_vat_receivable_adjustment', 'code' => AccountType::OTHER_INCOME_VAT_RECEIVABLE_ADJUSTMENT->value],
                ];

                break;
            case AccountType::OTHER_INCOME_USER_SET_INCOME_GROUP->value:
                return [
                    ['name' => 'components.dropdown.values.accountTypeDDL.other_income_user_set_income_group', 'code' => AccountType::OTHER_INCOME_USER_SET_INCOME_GROUP->value],
                ];

                break;
            case AccountType::OTHER_INCOME_STOCK_ADJUSTMENT_DIFFERENCE->value:
                return [
                    ['name' => 'components.dropdown.values.accountTypeDDL.other_income_stock_adjustment_difference', 'code' => AccountType::OTHER_INCOME_STOCK_ADJUSTMENT_DIFFERENCE->value],
                ];

                break;
            case AccountType::OTHER_INCOME_ASSET_ADJUSTMENT_DIFFERENCE->value:
                return [
                    ['name' => 'components.dropdown.values.accountTypeDDL.other_income_asset_adjustment_difference', 'code' => AccountType::OTHER_INCOME_ASSET_ADJUSTMENT_DIFFERENCE->value],
                ];

                break;
            case AccountType::OTHER_INCOME_ASSET_SALES_DIFFERENCE->value:
                return [
                    ['name' => 'components.dropdown.values.accountTypeDDL.other_income_asset_sales_difference', 'code' => AccountType::OTHER_INCOME_ASSET_SALES_DIFFERENCE->value],
                ];

                break;
            case AccountType::OTHER_INCOME_PRODUCTION_PROCESS_DIFFERENCE->value:
                return [
                    ['name' => 'components.dropdown.values.accountTypeDDL.other_income_production_process_difference', 'code' => AccountType::OTHER_INCOME_PRODUCTION_PROCESS_DIFFERENCE->value],
                ];

                break;
            case AccountType::OTHER_INCOME_VAT_RECEIVABLE_ADJUSTMENT->value:
                return [
                    ['name' => 'components.dropdown.values.accountTypeDDL.other_income_vat_receivable_adjustment', 'code' => AccountType::OTHER_INCOME_VAT_RECEIVABLE_ADJUSTMENT->value],
                ];

                break;
            case AccountType::OTHER_EXPENSE->value:
                return [
                    ['name' => 'components.dropdown.values.accountTypeDDL.other_expense_user_set_expense_group', 'code' => AccountType::OTHER_EXPENSE_USER_SET_EXPENSE_GROUP->value],
                    ['name' => 'components.dropdown.values.accountTypeDDL.other_expense_stock_adjustment_difference', 'code' => AccountType::OTHER_EXPENSE_STOCK_ADJUSTMENT_DIFFERENCE->value],
                    ['name' => 'components.dropdown.values.accountTypeDDL.other_expense_asset_adjustment_difference', 'code' => AccountType::OTHER_EXPENSE_ASSET_ADJUSTMENT_DIFFERENCE->value],
                    ['name' => 'components.dropdown.values.accountTypeDDL.other_expense_asset_sales_difference', 'code' => AccountType::OTHER_EXPENSE_ASSET_SALES_DIFFERENCE->value],
                    ['name' => 'components.dropdown.values.accountTypeDDL.other_expense_production_process_difference', 'code' => AccountType::OTHER_EXPENSE_PRODUCTION_PROCESS_DIFFERENCE->value],
                    ['name' => 'components.dropdown.values.accountTypeDDL.other_expense_vat_payable_adjustment', 'code' => AccountType::OTHER_EXPENSE_VAT_PAYABLE_ADJUSTMENT->value],
                ];

                break;
            case AccountType::OTHER_EXPENSE_USER_SET_EXPENSE_GROUP->value:
                return [
                    ['name' => 'components.dropdown.values.accountTypeDDL.other_expense_user_set_expense_group', 'code' => AccountType::OTHER_EXPENSE_USER_SET_EXPENSE_GROUP->value],
                ];

                break;
            case AccountType::OTHER_EXPENSE_STOCK_ADJUSTMENT_DIFFERENCE->value:
                return [
                    ['name' => 'components.dropdown.values.accountTypeDDL.other_expense_stock_adjustment_difference', 'code' => AccountType::OTHER_EXPENSE_STOCK_ADJUSTMENT_DIFFERENCE->value],
                ];

                break;
            case AccountType::OTHER_EXPENSE_ASSET_ADJUSTMENT_DIFFERENCE->value:
                return [
                    ['name' => 'components.dropdown.values.accountTypeDDL.other_expense_asset_adjustment_difference', 'code' => AccountType::OTHER_EXPENSE_ASSET_ADJUSTMENT_DIFFERENCE->value],
                ];

                break;
            case AccountType::OTHER_EXPENSE_ASSET_SALES_DIFFERENCE->value:
                return [
                    ['name' => 'components.dropdown.values.accountTypeDDL.other_expense_asset_sales_difference', 'code' => AccountType::OTHER_EXPENSE_ASSET_SALES_DIFFERENCE->value],
                ];

                break;
            case AccountType::OTHER_EXPENSE_PRODUCTION_PROCESS_DIFFERENCE->value:
                return [
                    ['name' => 'components.dropdown.values.accountTypeDDL.other_expense_production_process_difference', 'code' => AccountType::OTHER_EXPENSE_PRODUCTION_PROCESS_DIFFERENCE->value],
                ];

                break;
            case AccountType::OTHER_EXPENSE_VAT_PAYABLE_ADJUSTMENT->value:
                return [
                    ['name' => 'components.dropdown.values.accountTypeDDL.other_expense_vat_payable_adjustment', 'code' => AccountType::OTHER_EXPENSE_VAT_PAYABLE_ADJUSTMENT->value],
                ];

                break;
            case AccountType::PROFIT_AND_LOSS_SUMMARY->value:
                return [
                    ['name' => 'components.dropdown.values.accountTypeDDL.profit_and_loss_summary', 'code' => AccountType::PROFIT_AND_LOSS_SUMMARY->value],
                ];

                break;
            default:
                break;
        }
    }

    public function getActiveAccountCash(ChartOfAccountRequest $chartOfAccountRequest)
    {
        $request = $chartOfAccountRequest->validated();

        $company_id = $request['company_id'];
        $branch_id = $request['branch_id'];
        $include_id = array_key_exists('include_id', $request) ? $request['include_id'] : null;

        try {
            $result = $this->chartOfAccountActions->getActiveAccount(
                companyId: $company_id,
                branchId: $branch_id,
                accountTypes: [AccountType::CASH->value],
                includingId: $include_id
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            $response = ChartOfAccountResource::collection($result);

            return $response;
        }
    }

    public function getActiveAccountBank(ChartOfAccountRequest $chartOfAccountRequest)
    {
        $request = $chartOfAccountRequest->validated();

        $company_id = $request['company_id'];
        $branch_id = $request['branch_id'];
        $include_id = array_key_exists('include_id', $request) ? $request['include_id'] : null;

        try {
            $result = $this->chartOfAccountActions->getActiveAccount(
                companyId: $company_id,
                branchId: $branch_id,
                accountTypes: [AccountType::BANK->value],
                includingId: $include_id
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            $response = ChartOfAccountResource::collection($result);

            return $response;
        }
    }

    public function getActiveAccountCashAndBank(ChartOfAccountRequest $chartOfAccountRequest)
    {
        $request = $chartOfAccountRequest->validated();

        $company_id = $request['company_id'];
        $branch_id = $request['branch_id'];
        $include_id = array_key_exists('include_id', $request) ? $request['include_id'] : null;

        try {
            $result = $this->chartOfAccountActions->getActiveAccount(
                companyId: $company_id,
                branchId: $branch_id,
                accountTypes: [
                    AccountType::CASH->value,
                    AccountType::BANK->value,
                ],
                includingId: $include_id
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            $response = ChartOfAccountResource::collection($result);

            return $response;
        }
    }

    public function getActiveAccountCapitalGroup(ChartOfAccountRequest $chartOfAccountRequest)
    {
        $request = $chartOfAccountRequest->validated();

        $company_id = $request['company_id'];
        $branch_id = $request['branch_id'];
        $include_id = array_key_exists('include_id', $request) ? $request['include_id'] : null;

        try {
            $result = $this->chartOfAccountActions->getActiveAccount(
                companyId: $company_id,
                branchId: $branch_id,
                accountTypes: [AccountType::CAPITAL->value],
                includingId: $include_id
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            $response = ChartOfAccountResource::collection($result);

            return $response;
        }
    }

    public function getActiveAccountExpenseGroup(ChartOfAccountRequest $chartOfAccountRequest)
    {
        $request = $chartOfAccountRequest->validated();

        $company_id = $request['company_id'];
        $branch_id = $request['branch_id'];
        $include_id = array_key_exists('include_id', $request) ? $request['include_id'] : null;

        try {
            $result = $this->chartOfAccountActions->getActiveAccount(
                companyId: $company_id,
                branchId: $branch_id,
                accountTypes: [
                    AccountType::OPERATING_EXPENSE_USER_SET_EXPENSE_GROUP->value,
                    AccountType::OTHER_EXPENSE_USER_SET_EXPENSE_GROUP->value,
                ],
                includingId: $include_id
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            $response = ChartOfAccountResource::collection($result);

            return $response;
        }
    }

    public function getActiveAccountIncomeGroup(ChartOfAccountRequest $chartOfAccountRequest)
    {
        $request = $chartOfAccountRequest->validated();

        $company_id = $request['company_id'];
        $branch_id = $request['branch_id'];
        $include_id = array_key_exists('include_id', $request) ? $request['include_id'] : null;

        try {
            $result = $this->chartOfAccountActions->getActiveAccount(
                companyId: $company_id,
                branchId: $branch_id,
                accountTypes: [
                    AccountType::OPERATING_REVENUE_USER_SET_INCOME_GROUP->value,
                    AccountType::OTHER_INCOME_USER_SET_INCOME_GROUP->value,
                ],
                includingId: $include_id
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            $response = ChartOfAccountResource::collection($result);

            return $response;
        }
    }

    public function getActiveAccountOtherDebtGroup(ChartOfAccountRequest $chartOfAccountRequest)
    {
        $request = $chartOfAccountRequest->validated();

        $company_id = $request['company_id'];
        $branch_id = $request['branch_id'];
        $include_id = array_key_exists('include_id', $request) ? $request['include_id'] : null;

        try {
            $result = $this->chartOfAccountActions->getActiveAccount(
                companyId: $company_id,
                branchId: $branch_id,
                accountTypes: [
                    AccountType::OTHER_PAYABLE->value,
                    AccountType::OTHER_DEBT->value,
                ],
                includingId: $include_id
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            $response = ChartOfAccountResource::collection($result);

            return $response;
        }
    }

    public function getActiveAccountOtherReceivableGroup(ChartOfAccountRequest $chartOfAccountRequest)
    {
        $request = $chartOfAccountRequest->validated();

        $company_id = $request['company_id'];
        $branch_id = $request['branch_id'];
        $include_id = array_key_exists('include_id', $request) ? $request['include_id'] : null;

        try {
            $result = $this->chartOfAccountActions->getActiveAccount(
                companyId: $company_id,
                branchId: $branch_id,
                accountTypes: [AccountType::OTHER_PAYABLE->value],
                includingId: $include_id
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            $response = ChartOfAccountResource::collection($result);

            return $response;
        }
    }
}
