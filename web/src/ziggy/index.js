import ZiggyJS from "ziggy-js";
import ziggyRoute from "./ziggy";

const route = (name, params) => {
    return ZiggyJS(name, params, undefined, ziggyRoute);
}

export { route as default }