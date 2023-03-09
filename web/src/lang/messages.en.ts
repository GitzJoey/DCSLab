import menu from "./en/components/menu.json";
import buttons from "./en/components/buttons.json";
import alert_placeholder from "./en/components/alert-placeholder.json";
import dropdown from "./en/components/dropdown.json";
import top_bar from "./en/components/top-bar.json";

import login from "./en/views/login.json";
import register from "./en/views/register.json";
import profile from "./en/views/profile.json";
import user from "./en/views/user.json";

export default {
    "components": {
        "menu": menu,
        "alert-placeholder": alert_placeholder,
        "buttons": buttons,
        "dropdown": dropdown,
        "top-bar": top_bar
    },
    "views": {
        "login": login,
        "register": register,
        "profile": profile,
        "user": user
    }
}