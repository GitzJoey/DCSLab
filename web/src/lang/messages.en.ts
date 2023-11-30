import menu from "./en/components/menu.json";
import buttons from "./en/components/buttons.json";
import alert_placeholder from "./en/components/alert-placeholder.json";
import dropdown from "./en/components/dropdown.json";
import top_bar from "./en/components/top-bar.json";
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
import error from "./en/views/error.json"

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
        "file-upload": file_upload,
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