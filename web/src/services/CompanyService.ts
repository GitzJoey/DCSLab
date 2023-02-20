import axios from "../axios";
import { useZiggyRouteStore } from "../stores/ziggy-route";
import route, { Config } from "ziggy-js";

export default class CompanyService {
    private ziggyRoute: Config;
    private ziggyRouteStore = useZiggyRouteStore();

    constructor() {
        this.ziggyRoute = this.ziggyRouteStore.getZiggy;
    }

    private async axiosGet(url: string): Promise<any> {
        try {
            return await axios.get(url);
        } catch (e) {
            return e;
        }
    }

    private async axiosPost(url: string, data: any): Promise<any> {
        try {
            return await axios.post(url, data);
        } catch (e) {
            return e;
        }
    }
}