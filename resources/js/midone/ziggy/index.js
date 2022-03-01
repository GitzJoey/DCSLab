import { Ziggy } from "@/ziggy/ziggy";
import ziggyRoute from "ziggy-js";

function route(name, params) {
    return ziggyRoute(name, params, undefined, Ziggy);
}

export { route }