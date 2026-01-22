import menu from "./en/components/menu.json";
import buttons from "./en/components/buttons.json";
import alert_placeholder from "./en/components/alert-placeholder.json";
import dropdown from "./en/components/dropdown.json";
import profile_menu from "./en/components/profile-menu.json";
import language_switcher from "./en/components/language-switcher.json";
import search_box from "./en/components/search-box.json";
import sidebar_pop from "./en/components/sidebar-pop.json";
import data_list from "./en/components/data-list.json"
import user_location from "./en/components/user-location.json";
import delete_modal from "./en/components/delete-modal.json";
import file_upload from "./en/components/file-upload.json";

import login from "./en/views/login.json";
import register from "./en/views/register.json";
import forgot_password from "./en/views/forgot_password.json";
import reset_password from "./en/views/reset_password.json";
import profile from "./en/views/profile.json";
import user from "./en/views/user.json";
import company from "./en/views/company.json"
import branch from "./en/views/branch.json"
import warehouse from "./en/views/warehouse.json"
import product_category from "./en/views/product_category.json"
import brand from "./en/views/brand.json"
import unit from "./en/views/unit.json"
import customer_group from "./en/views/customer_group.json"
import customer from "./en/views/customer.json"
import investor from "./en/views/investor.json"
import cash_account from "./en/views/cash_account.json"
import product_service from "./en/views/product_service.json"
import product from "./en/views/product.json"
import stock_adjustment_category from "./en/views/stock_adjustment_category.json"
import error from "./en/views/error.json"

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
