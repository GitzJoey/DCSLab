import menu from './en/components/menu.json';
import buttons from './en/components/buttons.json';
import alert_placeholder from './en/components/alert-placeholder.json';
import dropdown from './en/components/dropdown.json';
import profile_menu from './en/components/profile-menu.json';
import language_switcher from './en/components/language-switcher.json';
import search_box from './en/components/search-box.json';
import sidebar_pop from './en/components/sidebar-pop.json';
import data_list from './en/components/data-list.json';
import user_location from './en/components/user-location.json';
import delete_modal from './en/components/delete-modal.json';
import file_upload from './en/components/file-upload.json';

import login from './en/views/login.json';
import register from './en/views/register.json';
import forgot_password from './en/views/forgot_password.json';
import reset_password from './en/views/reset_password.json';
import profile from './en/views/profile.json';
import user from './en/views/user.json';
import company from './en/views/company.json';
import branch from './en/views/branch.json';
import warehouse from './en/views/warehouse.json';
import product_category from './en/views/product_category.json';
import brand from './en/views/brand.json';
import unit from './en/views/unit.json';
import vat_profile from './en/views/vat_profile.json';
import customer_group from './en/views/customer_group.json';
import customer from './en/views/customer.json';
import investor from './en/views/investor.json';
import cash_account from './en/views/cash_account.json';
import capital_opening from './en/views/capital_opening.json';
import capital_transaction from './en/views/capital_transaction.json';
import cash_transfer from './en/views/cash_transfer.json';
import expense from './en/views/expense.json';
import prepaid_expense from './en/views/prepaid_expense.json';
import expense_payment from './en/views/expense_payment.json';
import debt from './en/views/debt.json';
import receivable from './en/views/receivable.json';
import income from './en/views/income.json';
import prepaid_income from './en/views/prepaid_income.json';
import income_payment from './en/views/income_payment.json';
import purchase_additional_cost from './en/views/purchase_additional_cost.json';
import purchase_additional_cost_payment from './en/views/purchase_additional_cost_payment.json';
import purchase from './en/views/purchase.json';
import purchase_receipt from './en/views/purchase_receipt.json';
import expense_category from './en/views/expense_category.json';
import debt_category from './en/views/debt_category.json';
import receivable_category from './en/views/receivable_category.json';
import debt_creditor from './en/views/debt_creditor.json';
import income_category from './en/views/income_category.json';
import product_service from './en/views/product_service.json';
import product from './en/views/product.json';
import supplier from './en/views/supplier.json';
import stock_adjustment_category from './en/views/stock_adjustment_category.json';
import stock_adjustment from './en/views/stock_adjustment.json';
import stock_adjustment_in_item from './en/views/stock_adjustment_in_item.json';
import stock_adjustment_in_item_serial from './en/views/stock_adjustment_in_item_serial.json';
import stock_adjustment_out_item from './en/views/stock_adjustment_out_item.json';
import stock_adjustment_out_item_serial from './en/views/stock_adjustment_out_item_serial.json';
import purchase_order from './en/views/purchase_order.json';
import purchase_order_item from './en/views/purchase_order_item.json';
import purchase_order_down_payment from './en/views/purchase_order_down_payment.json';
import purchase_order_down_payment_refund from './en/views/purchase_order_down_payment_refund.json';
import stock_transfer from './en/views/stock_transfer.json';
import stock_transfer_item from './en/views/stock_transfer_item.json';
import stock_transfer_item_serial from './en/views/stock_transfer_item_serial.json';
import error from './en/views/error.json';

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
    receivable_category: receivable_category,
    debt_creditor: debt_creditor,
    income_category: income_category,
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
