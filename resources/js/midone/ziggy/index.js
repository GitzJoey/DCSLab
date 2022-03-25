import { Ziggy } from "@/ziggy/ziggy";
import ZiggyJS from "ziggy-js";

function route(name, params) {
    if (Ziggy.url !== window.location.host) {
        Ziggy.url = window.location.host;
        Ziggy.port = window.location.port;
    }
    
    return ZiggyJS(name, params, undefined, Ziggy);
}

export { route }