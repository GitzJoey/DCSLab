import ZiggyJS from "ziggy-js";
import { Ziggy } from "./ziggy";

const loadZiggyRoute = (params) => {

}

const route = (name, params) => {
    return ZiggyJS(name, params, undefined, Ziggy);
}

export { route as default, loadZiggyRoute }