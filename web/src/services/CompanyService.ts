import axios from "../axios";
import { useZiggyRouteStore } from "../stores/ziggy-route";
import route, { Config } from "ziggy-js";

export default class CompanyService {
    private ziggyRoute: Config;
    private ziggyRouteStore = useZiggyRouteStore();

    constructor() {
        this.ziggyRoute = this.ziggyRouteStore.getZiggy;
    }
}