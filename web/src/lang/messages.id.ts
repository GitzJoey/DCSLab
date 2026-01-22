import menu from "./id/components/menu.json";
import buttons from "./id/components/buttons.json";
import alert_placeholder from "./id/components/alert-placeholder.json";
import dropdown from "./id/components/dropdown.json";
import profile_menu from "./id/components/profile-menu.json";
import language_switcher from "./id/components/language-switcher.json";
import search_box from "./id/components/search-box.json";
import sidebar_pop from "./id/components/sidebar-pop.json";
import data_list from "./id/components/data-list.json";
import user_location from "./id/components/user-location.json";
import delete_modal from "./id/components/delete-modal.json";
import file_upload from "./id/components/file-upload.json";

import login from "./id/views/login.json";
import register from "./id/views/register.json";
import forgot_password from "./id/views/forgot_password.json";
import reset_password from "./id/views/reset_password.json";
import profile from "./id/views/profile.json";
import user from "./id/views/user.json";
import company from "./id/views/company.json"
import branch from "./id/views/branch.json"
import warehouse from "./id/views/warehouse.json"
import product_category from "./id/views/product_category.json"
import brand from "./id/views/brand.json"
import unit from "./id/views/unit.json"
import customer_group from "./id/views/customer_group.json"
import customer from "./id/views/customer.json"
import investor from "./id/views/investor.json"
import cash_account from "./id/views/cash_account.json"
import product_service from "./id/views/product_service.json"
import product from "./id/views/product.json"
import stock_adjustment_category from "./id/views/stock_adjustment_category.json"
import error from "./id/views/error.json"

export default {
    "components": {
        "menu": menu,
        "alert-placeholder": alert_placeholder,
        "buttons": buttons,
        "dropdown": dropdown,
        "data-list": data_list,
        "user-location": user_location,
        "delete-modal": delete_modal,
        "file-upload": file_upload,
        "profile-menu": profile_menu,
        "language-switcher": language_switcher,
        "search-box": search_box,
        "sidebar-pop": sidebar_pop
    },
    "views": {
        "login": login,
        "register": register,
        "forgot_password": forgot_password,
        "reset_password": reset_password,
        "profile": profile,
        "user": user,
        "company": company,
        "branch": branch,
        "warehouse": warehouse,
        "product_category": product_category,
        "brand": brand,
        "unit": unit,
        "product_service": product_service,
        "product": product,
        "stock_adjustment_category": stock_adjustment_category,
        "customer_group": customer_group,
        "customer": customer,
        "investor": investor,
        "cash_account": cash_account,
        "error": error,
    }
}
