import axios from "../axios";
import { useZiggyRouteStore } from "../stores/ziggy-route";
import route, { Config } from "ziggy-js";
import { Warehouse } from "../types/models/Warehouse";
import { Resource } from "../types/resources/Resource";
import { Collection } from "../types/resources/Collection";
import { ServiceResponse } from "../types/services/ServiceResponse";
import { AxiosError, AxiosResponse, isAxiosError } from "axios";
import ErrorHandlerService from "./ErrorHandlerService";
import { ReadAnyRequest } from "../types/services/ServiceRequest";
import { StatusCode } from "../types/enums/StatusCode";
import { client, useForm } from "laravel-precognition-vue";

export default class WarehouseService {
    private ziggyRoute: Config;
    private ziggyRouteStore = useZiggyRouteStore();

    private errorHandlerService;

    constructor() {
        this.ziggyRoute = this.ziggyRouteStore.getZiggy;
        this.errorHandlerService = new ErrorHandlerService();
    }

    public useWarehouseCreateForm() {
        const url = route('api.post.db.company.warehouse.save', undefined, true, this.ziggyRoute);

        client.axios().defaults.withCredentials = true;
        const form = useForm('post', url, {
            company_id: '',
            branch_id: '',
            code: '_AUTO_',
            name: '',
            address: '',
            city: '',
            contact: '',
            remarks: '',
            status: 'ACTIVE',
        });

        return form;
    }

    public async readAny(args: ReadAnyRequest): Promise<ServiceResponse<Collection<Array<Warehouse>> | Resource<Array<Warehouse>> | null>> {
        const result: ServiceResponse<Collection<Array<Warehouse>> | Resource<Array<Warehouse>> | null> = {
            success: false
        }

        try {
            const queryParams: Record<string, string | number | boolean> = {};
            if (args.company_id) {
                queryParams['company_id'] = args.company_id;
            }
            queryParams['search'] = args.search ? args.search : '';
            queryParams['paginate'] = args.paginate;
            if (args.page) queryParams['page'] = args.page;
            if (args.per_page) queryParams['per_page'] = args.per_page;
            queryParams['refresh'] = args.refresh;
            
            const url = route('api.get.db.company.warehouse.read_any', {
                _query: queryParams
            }, false, this.ziggyRoute);
                         
            const response: AxiosResponse<Collection<Array<Warehouse>>> = await axios.get(url);


            if (response.status === StatusCode.OK) {
                result.success = true;
                result.data = response.data;
            }

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

    public async read(ulid: string): Promise<ServiceResponse<Warehouse | null>> {
        const result: ServiceResponse<Warehouse | null> = {
            success: false
        }

        try {
            const url = route('api.get.db.company.warehouse.read', {
                warehouse: ulid
            }, false, this.ziggyRoute);

            const response: AxiosResponse<Resource<Warehouse>> = await axios.get(url);

            if (response.status === StatusCode.OK) {
                result.success = true;
                result.data = response.data.data;
            }

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

    public useWarehouseEditForm(ulid: string) {
        const url = route('api.post.db.company.warehouse.edit', ulid, true, this.ziggyRoute);

        client.axios().defaults.withCredentials = true;
        const form = useForm('post', url, {
            company_id: '',
            branch_id: '',
            code: '_AUTO_',
            name: '',
            address: '',
            city: '',
            contact: '',
            remarks: '',
            status: 'ACTIVE',
        });

        return form;
    }

    public async delete(ulid: string): Promise<ServiceResponse<boolean | null>> {
        const result: ServiceResponse<boolean | null> = {
            success: false,
        }

        try {
            const url = route('api.post.db.company.warehouse.delete', ulid, false, this.ziggyRoute);

            const response: AxiosResponse<boolean | null> = await axios.post(url);

            if (response.status === StatusCode.OK) {
                result.success = true;
                result.data = response.data;
            }

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