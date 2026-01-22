import axios from "../axios";
import { useZiggyRouteStore } from "../stores/ziggy-route";
import { route, Config } from "ziggy-js";
import { AxiosError, AxiosResponse, isAxiosError } from "axios";
import { Resource } from "../types/resources/Resource";
import { ServiceResponse } from "../types/services/ServiceResponse";
import ErrorHandlerService from "./ErrorHandlerService";
import { SearchResult } from "@/types/models/SearchResult";

export default class SearchService {
    private ziggyRoute: Config;
    private ziggyRouteStore = useZiggyRouteStore();

    private errorHandlerService;

    constructor() {
        this.ziggyRoute = this.ziggyRouteStore.getZiggy;

        this.errorHandlerService = new ErrorHandlerService();
    }

    public async search(): Promise<ServiceResponse<Resource<Array<SearchResult>> | null>> {
        const result: ServiceResponse<Resource<Array<SearchResult>> | null> = {
            success: false,
        };

        try {
            const url = route('api.get.db.core.search', undefined, false, this.ziggyRoute);

            const response: AxiosResponse<Resource<Array<SearchResult>>> = await axios.get(url);

            result.success = true;
            result.data = response.data;

            return result;
        } catch (e: unknown) {
            if (e instanceof Error && e.message.includes('Ziggy error')) {
                return this.errorHandlerService.generateZiggyUrlErrorServiceResponse(e.message);
            } else if (isAxiosError(e)) {
                return this.errorHandlerService.generateAxiosErrorServiceResponse(e as AxiosError);
            } else {
                return result;
            }
        }
    }
}