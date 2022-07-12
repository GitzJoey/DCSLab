import ZiggyJS from "ziggy-js";

const response = await fetch('http://localhost:8000/api/get/dashboard/core/user/ziggy');
const Ziggy = await response.toJson();

function route(name, params) {
    return ZiggyJS(name, params, undefined, Ziggy);
}

export { route }