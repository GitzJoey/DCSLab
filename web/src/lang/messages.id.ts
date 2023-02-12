import menu from "./id/components/menu.json";
import buttons from "./id/components/buttons.json";
import alert_placeholder from "./id/components/alert-placeholder.json";
import dropdown from "./id/components/dropdown.json";
import top_bar from "./id/components/top-bar.json";

import login from "./id/views/login.json";
import register from "./id/views/register.json";
import profile from "./id/views/profile.json";
import user from "./id/views/user.json";

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