import menu from "./en/components/menu.json";
import buttons from "./en/components/buttons.json";
import alert_placeholder from "./en/components/alert-placeholder.json";
import dropdown from "./en/components/dropdown.json";
import top_bar from "./en/components/top-bar.json";
import data_list from "./en/components/data-list.json"
import user_location from "./en/components/user-location.json";
import delete_modal from "./en/components/delete-modal.json";

import login from "./en/views/login.json";
import register from "./en/views/register.json";
import reset_password from "./en/views/reset_password.json";
import profile from "./en/views/profile.json";
import user from "./en/views/user.json";
import company from "./en/views/company.json"
import branch from "./en/views/branch.json"
import employee from "./en/views/employee.json"
import warehouse from "./en/views/warehouse.json"
import product_group from "./en/views/product_group.json"
import brand from "./en/views/brand.json"
import unit from "./en/views/unit.json"
import product from "./en/views/product.json"
import supplier from "./en/views/supplier.json"
import customer_group from "./en/views/customer_group.json"
import customer from "./en/views/customer.json"
import purchase_order from "./en/views/purchase_order.json"

export default {
    "components": {
        "menu": menu,
        "alert-placeholder": alert_placeholder,
        "buttons": buttons,
        "dropdown": dropdown,
        "top-bar": top_bar,
        "data-list": data_list,
        "user-location": user_location,
        "delete-modal": delete_modal,
    },
    "views": {
        "login": login,
        "register": register,
        "reset_password": reset_password,
        "profile": profile,
        "user": user,
        "company": company,
        "branch": branch,
        "employee": employee,
        "warehouse": warehouse,
        "product_group": product_group,
        "brand": brand,
        "unit": unit,
        "product": product,
        "supplier": supplier,
        "customer_group": customer_group,
        "customer": customer,
        "purchase_order": purchase_order
    }
}