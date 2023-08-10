import axios from "../axios";
import { useZiggyRouteStore } from "../stores/ziggy-route";
import route, { Config } from "ziggy-js";
import { AxiosError, AxiosResponse, isAxiosError } from "axios";
import { Role } from "../types/models/Role";
import { Resource } from "../types/resources/Resource";
import { ServiceResponse } from "../types/services/ServiceResponse";
import ErrorHandlerService from "./ErrorHandlerService";


export default class ProfileService {
    private ziggyRoute : Config;
    private ziggyRouteStore = useZiggyRouteStore();

    private errorHandlerService;

    constructor() {
        this.ziggyRoute = this.ziggyRouteStore.getZiggy;

        this.errorHandlerService = new ErrorHandlerService();
    }

    async updateProfile(payload : {
        first_name : string,
        last_name : string,
        address : string,
        city : string,
        postal_code : string,
        country: string,
        tax_id : number,
        ic_num : number,
        remarks : string
    }) : Promise<ServiceResponse<Resource<{}>|null>> {
        const result : ServiceResponse<Resource<{}>> = {success : false}
        try {
            const url = route('api.post.db.module.profile.update.info', undefined, false, this.ziggyRoute);
            if(!url) return this.errorHandlerService.generateZiggyUrlErrorServiceResponse();
            const response : AxiosResponse<Resource<{}>> = await axios.post(url , {
                first_name : payload.first_name,
                last_name : payload.last_name,
                address : payload.address,
                city : payload.city,
                postal_code : payload.postal_code,
                country : payload.country,
                tax_id : payload.tax_id,
                ic_num : payload.ic_num,
                remarks : payload.remarks,
            })

            result.success = true
            result.data = response.data
            return result
        } catch (e) {
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