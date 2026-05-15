import menu from './id/components/menu.json';
import buttons from './id/components/buttons.json';
import alert_placeholder from './id/components/alert-placeholder.json';
import dropdown from './id/components/dropdown.json';
import profile_menu from './id/components/profile-menu.json';
import language_switcher from './id/components/language-switcher.json';
import search_box from './id/components/search-box.json';
import sidebar_pop from './id/components/sidebar-pop.json';
import data_list from './id/components/data-list.json';
import user_location from './id/components/user-location.json';
import delete_modal from './id/components/delete-modal.json';
import file_upload from './id/components/file-upload.json';

import login from './id/views/login.json';
import register from './id/views/register.json';
import forgot_password from './id/views/forgot_password.json';
import reset_password from './id/views/reset_password.json';
import profile from './id/views/profile.json';
import user from './id/views/user.json';
import company from './id/views/company.json';
import branch from './id/views/branch.json';
import warehouse from './id/views/warehouse.json';
import product_category from './id/views/product_category.json';
import brand from './id/views/brand.json';
import unit from './id/views/unit.json';
import vat_profile from './id/views/vat_profile.json';
import customer_group from './id/views/customer_group.json';
import customer from './id/views/customer.json';
import investor from './id/views/investor.json';
import cash_account from './id/views/cash_account.json';
import capital_opening from './id/views/capital_opening.json';
import capital_transaction from './id/views/capital_transaction.json';
import cash_transfer from './id/views/cash_transfer.json';
import expense from './id/views/expense.json';
import prepaid_expense from './id/views/prepaid_expense.json';
import expense_payment from './id/views/expense_payment.json';
import debt from './id/views/debt.json';
import receivable from './id/views/receivable.json';
import income from './id/views/income.json';
import prepaid_income from './id/views/prepaid_income.json';
import income_payment from './id/views/income_payment.json';
import purchase_additional_cost from './id/views/purchase_additional_cost.json';
import purchase_additional_cost_payment from './id/views/purchase_additional_cost_payment.json';
import purchase from './id/views/purchase.json';
import purchase_receipt from './id/views/purchase_receipt.json';
import expense_category from './id/views/expense_category.json';
import debt_category from './id/views/debt_category.json';
import asset_category from './id/views/asset_category.json';
import asset_unit from './id/views/asset_unit.json';
import asset from './id/views/asset.json';
import asset_adjustment from './id/views/asset_adjustment.json';
import asset_adjustment_in_item from './id/views/asset_adjustment_in_item.json';
import asset_adjustment_out_item from './id/views/asset_adjustment_out_item.json';
import asset_purchase from './id/views/asset_purchase.json';
import asset_purchase_item from './id/views/asset_purchase_item.json';
import asset_sale from './id/views/asset_sale.json';
import asset_sale_item from './id/views/asset_sale_item.json';
import receivable_category from './id/views/receivable_category.json';
import debt_creditor from './id/views/debt_creditor.json';
import income_category from './id/views/income_category.json';
import chart_of_account from './id/views/chart_of_account.json';
import journal_entry from './id/views/journal_entry.json';
import balance_sheet from './id/views/balance_sheet.json';
import product_service from './id/views/product_service.json';
import product from './id/views/product.json';
import supplier from './id/views/supplier.json';
import stock_adjustment_category from './id/views/stock_adjustment_category.json';
import stock_adjustment from './id/views/stock_adjustment.json';
import stock_adjustment_in_item from './id/views/stock_adjustment_in_item.json';
import stock_adjustment_in_item_serial from './id/views/stock_adjustment_in_item_serial.json';
import stock_adjustment_out_item from './id/views/stock_adjustment_out_item.json';
import stock_adjustment_out_item_serial from './id/views/stock_adjustment_out_item_serial.json';
import purchase_order from './id/views/purchase_order.json';
import purchase_order_item from './id/views/purchase_order_item.json';
import purchase_order_down_payment from './id/views/purchase_order_down_payment.json';
import purchase_order_down_payment_refund from './id/views/purchase_order_down_payment_refund.json';
import stock_transfer from './id/views/stock_transfer.json';
import stock_transfer_item from './id/views/stock_transfer_item.json';
import stock_transfer_item_serial from './id/views/stock_transfer_item_serial.json';
import error from './id/views/error.json';

export default {
  components: {
    menu: menu,
    'alert-placeholder': alert_placeholder,
    buttons: buttons,
    dropdown: dropdown,
    'data-list': data_list,
    'user-location': user_location,
    'delete-modal': delete_modal,
    'file-upload': file_upload,
    'profile-menu': profile_menu,
    'language-switcher': language_switcher,
    'search-box': search_box,
    'sidebar-pop': sidebar_pop,
  },
  views: {
    login: login,
    register: register,
    forgot_password: forgot_password,
    reset_password: reset_password,
    profile: profile,
    user: user,
    company: company,
    branch: branch,
    warehouse: warehouse,
    product_category: product_category,
    brand: brand,
    unit: unit,
    vat_profile: vat_profile,
    product_service: product_service,
    product: product,
    supplier: supplier,
    customer_group: customer_group,
    customer: customer,
    investor: investor,
    cash_account: cash_account,
    capital_opening: capital_opening,
    capital_transaction: capital_transaction,
    cash_transfer: cash_transfer,
    expense: expense,
    prepaid_expense: prepaid_expense,
    expense_payment: expense_payment,
    debt: debt,
    receivable: receivable,
    income: income,
    prepaid_income: prepaid_income,
    income_payment: income_payment,
    purchase_additional_cost: purchase_additional_cost,
    purchase_additional_cost_payment: purchase_additional_cost_payment,
    purchase: purchase,
    purchase_receipt: purchase_receipt,
    expense_category: expense_category,
    debt_category: debt_category,
    asset_category: asset_category,
    asset_unit: asset_unit,
    asset: asset,
    asset_adjustment: asset_adjustment,
    asset_adjustment_in_item: asset_adjustment_in_item,
    asset_adjustment_out_item: asset_adjustment_out_item,
    asset_purchase: asset_purchase,
    asset_purchase_item: asset_purchase_item,
    asset_sale: asset_sale,
    asset_sale_item: asset_sale_item,
    receivable_category: receivable_category,
    debt_creditor: debt_creditor,
    income_category: income_category,
    chart_of_account: chart_of_account,
    journal_entry: journal_entry,
    balance_sheet: balance_sheet,
    stock_adjustment_category: stock_adjustment_category,
    stock_adjustment: stock_adjustment,
    stock_adjustment_in_item: stock_adjustment_in_item,
    stock_adjustment_in_item_serial: stock_adjustment_in_item_serial,
    stock_adjustment_out_item: stock_adjustment_out_item,
    stock_adjustment_out_item_serial: stock_adjustment_out_item_serial,
    purchase_order: purchase_order,
    purchase_order_item: purchase_order_item,
    purchase_order_down_payment: purchase_order_down_payment,
    purchase_order_down_payment_refund: purchase_order_down_payment_refund,
    stock_transfer: stock_transfer,
    stock_transfer_item: stock_transfer_item,
    stock_transfer_item_serial: stock_transfer_item_serial,
    error: error,
  },
};
