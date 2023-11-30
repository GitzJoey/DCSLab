import menu from "./id/components/menu.json";
import buttons from "./id/components/buttons.json";
import alert_placeholder from "./id/components/alert-placeholder.json";
import dropdown from "./id/components/dropdown.json";
import top_bar from "./id/components/top-bar.json";
import data_list from "./id/components/data-list.json";
import user_location from "./id/components/user-location.json";
import delete_modal from "./id/components/delete-modal.json";
import file_upload from "./id/components/file-upload.json";

import login from "./id/views/login.json";
import register from "./id/views/register.json";
import forgot_password from "./en/views/forgot_password.json";
import reset_password from "./en/views/reset_password.json";
import profile from "./id/views/profile.json";
import user from "./id/views/user.json";
import company from "./id/views/company.json"
import branch from "./id/views/branch.json"
import error from "./id/views/error.json"

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
        "forgot_password": forgot_password,
        "reset_password": reset_password,
        "profile": profile,
        "user": user,
        "company": company,
        "branch": branch,
        "error": error,
    }
}