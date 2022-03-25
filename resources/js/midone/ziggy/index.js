import { Ziggy } from "@/ziggy/ziggy";
import ZiggyJS from "ziggy-js";

function route(name, params) {
    return ZiggyJS(name, params, undefined, Ziggy);
}

export { route }