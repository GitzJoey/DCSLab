import ZiggyJS from "ziggy-js";
import axios from "@/axios";
import _ from "lodash";

var Ziggy = {};

const loadZiggyRoute = async () => {
    let response = await axios.get(import.meta.env.VITE_BACKEND_URL + '/api/get/dashboard/core/user/ziggy');
    
    if (response.status === 200)
        Ziggy = response.data;
}

const route = (name, params) => {
    if (_.isEmpty(Ziggy)) return '';

    return ZiggyJS(name, params, undefined, Ziggy);
}

export { route as default, loadZiggyRoute }