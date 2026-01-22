import axios from "../axios";
import { useZiggyRouteStore } from "../stores/ziggy-route";
import { route, Config } from "ziggy-js";
import { Branch } from "../types/models/Branch";
import { Resource } from "../types/resources/Resource";
import { Collection } from "../types/resources/Collection";
import { ServiceResponse } from "../types/services/ServiceResponse";
import { AxiosError, AxiosResponse, isAxiosError } from "axios";
import ErrorHandlerService from "./ErrorHandlerService";
import { BranchReadAnyPaginateRequest, BranchReadAnyGetRequest } from "../types/services/branch/BranchRequest";
import { StatusCode } from "../types/enums/StatusCode";
import { client, useForm } from "laravel-precognition-vue";

export default class BranchService {
    private ziggyRoute: Config;
    private ziggyRouteStore = useZiggyRouteStore();

    private errorHandlerService;

    constructor() {
        this.ziggyRoute = this.ziggyRouteStore.getZiggy;

        this.errorHandlerService = new ErrorHandlerService();
    }

    public useBranchCreateForm() {
        const url = route('api.post.branch.save', undefined, true, this.ziggyRoute);

        client.axios().defaults.withCredentials = true;
        client.axios().defaults.withXSRFToken = true;
        const form = useForm('post', url, {
            company_id: '',
            code: '_AUTO_',
            name: '',
            address: '',
            city: '',
            contact: '',
            is_main: false,
            remarks: '',
            status: 'ACTIVE',
        });

        return form;
    }

    public async readAnyPaginate(args: BranchReadAnyPaginateRequest): Promise<ServiceResponse<Collection<Array<Branch>> | null>> {
        const result: ServiceResponse<Collection<Array<Branch>> | null> = {
            success: false
        }

        try {
            const queryParams: Record<string, any> = {};

            if (args.with_trashed !== undefined) queryParams['with_trashed'] = args.with_trashed;

            if (args.company_id) queryParams['company_id'] = args.company_id;
            if (args.search) queryParams['search'] = args.search;
            if (args.is_main !== undefined) queryParams['is_main'] = args.is_main;
            if (args.status) queryParams['status'] = args.status;
            if (args.include_id) queryParams['include_id'] = args.include_id;
            
            queryParams['refresh'] = args.refresh;            
            queryParams['paginate'] = {
                page: args.page,
                per_page: args.per_page
            };

            const url = route('api.get.branch.read_any', {
                _query: queryParams
            }, false, this.ziggyRoute);

            const response: AxiosResponse<Collection<Array<Branch>>> = await axios.get(url);

            if (response.status == StatusCode.OK) {
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

    public async readAnyGet(args: BranchReadAnyGetRequest): Promise<ServiceResponse<Resource<Array<Branch>> | null>> {
        const result: ServiceResponse<Resource<Array<Branch>> | null> = {
            success: false
        }

        try {
            const queryParams: Record<string, any> = {};
            if (args.with_trashed !== undefined)  queryParams['with_trashed'] = args.with_trashed;

            if (args.company_id) queryParams['company_id'] = args.company_id;
            if (args.search) queryParams['search'] = args.search;
            if (args.is_main !== undefined) queryParams['is_main'] = args.is_main;
            if (args.status) queryParams['status'] = args.status;
            if (args.include_id) queryParams['include_id'] = args.include_id;
            
            queryParams['refresh'] = args.refresh;            
            queryParams['get'] = {
                limit: args.limit
            };

            const url = route('api.get.branch.read_any', {
                _query: queryParams
            }, false, this.ziggyRoute);

            const response: AxiosResponse<Resource<Array<Branch>>> = await axios.get(url);

            if (response.status == StatusCode.OK) {
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

    public async read(ulid: string): Promise<ServiceResponse<Branch | null>> {
        const result: ServiceResponse<Branch | null> = {
            success: false
        }

        try {
            const url = route('api.get.branch.read', {
                branch: ulid
            }, false, this.ziggyRoute);

            const response: AxiosResponse<Resource<Branch>> = await axios.get(url);

            if (response.status == StatusCode.OK) {
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

    public useBranchEditForm(ulid: string) {
        const url = route('api.post.branch.edit', ulid, true, this.ziggyRoute);

        client.axios().defaults.withCredentials = true;
        client.axios().defaults.withXSRFToken = true;
        const form = useForm('post', url, {
            company_id: '',
            code: '_AUTO_',
            name: '',
            address: '',
            city: '',
            contact: '',
            is_main: false,
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
            const url = route('api.post.branch.delete', ulid, false, this.ziggyRoute);

            const response: AxiosResponse<boolean | null> = await axios.post(url);

            if (response.status == StatusCode.OK) {
                result.success = true;
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