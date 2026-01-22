import axios from "../axios";
import { useZiggyRouteStore } from "../stores/ziggy-route";
import { route, Config } from "ziggy-js";
import { Unit } from "../types/models/Unit";
import { Resource } from "../types/resources/Resource";
import { Collection } from "../types/resources/Collection";
import { ServiceResponse } from "../types/services/ServiceResponse";
import { AxiosError, AxiosResponse, isAxiosError } from "axios";
import ErrorHandlerService from "./ErrorHandlerService";
import { UnitReadAnyPaginateRequest, UnitReadAnyGetRequest } from "../types/services/unit/UnitRequest";
import { StatusCode } from "../types/enums/StatusCode";
import { client, useForm } from "laravel-precognition-vue";
import CacheService from "./CacheService";
import { DropDownOption } from "../types/models/DropDownOption";

export default class UnitService {
    private ziggyRoute: Config;
    private ziggyRouteStore = useZiggyRouteStore();

    private errorHandlerService;
    private cacheService;

    constructor() {
        this.ziggyRoute = this.ziggyRouteStore.getZiggy;

        this.errorHandlerService = new ErrorHandlerService();
        this.cacheService = new CacheService();
    }

    public useUnitCreateForm() {
        const url = route('api.post.unit.save', undefined, true, this.ziggyRoute);

        client.axios().defaults.withCredentials = true;
        client.axios().defaults.withXSRFToken = true;
        const form = useForm('post', url, {
            company_id: '',
            code: '_AUTO_',
            name: '',
            description: '',
            type: '',
        });

        return form;
    }

    public async readAnyPaginate(args: UnitReadAnyPaginateRequest): Promise<ServiceResponse<Collection<Array<Unit>> | null>> {
        const result: ServiceResponse<Collection<Array<Unit>> | null> = {
            success: false
        }

        try {
            const queryParams: Record<string, any> = {};
            if (args.with_trashed !== undefined) queryParams['with_trashed'] = args.with_trashed;            
            if (args.company_id) queryParams['company_id'] = args.company_id;
            
            if (args.search) queryParams['search'] = args.search;
            if (args.include_id) queryParams['include_id'] = args.include_id;

            queryParams['refresh'] = args.refresh;
            queryParams['paginate'] = {
                page: args.page,
                per_page: args.per_page
            };

            const url = route('api.get.unit.read_any', {
                _query: queryParams
            }, false, this.ziggyRoute);

            const response: AxiosResponse<Collection<Array<Unit>>> = await axios.get(url);

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

    public async readAnyGet(args: UnitReadAnyGetRequest): Promise<ServiceResponse<Resource<Array<Unit>> | null>> {
        const result: ServiceResponse<Resource<Array<Unit>> | null> = {
            success: false
        }

        try {
            const queryParams: Record<string, any> = {};
            queryParams['with_trashed'] = args.with_trashed ? 1 : 0;
            
            queryParams['company_id'] = args.company_id;
            queryParams['search'] = args.search ? args.search : '';
            if (args.include_id) queryParams['include_id'] = args.include_id;

            queryParams['refresh'] = args.refresh;
            queryParams['get'] = {
                limit: args.limit
            };

            const url = route('api.get.unit.read_any', {
                _query: queryParams
            }, false, this.ziggyRoute);

            const response: AxiosResponse<Resource<Array<Unit>>> = await axios.get(url);

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

    public async read(ulid: string): Promise<ServiceResponse<Unit | null>> {
        const result: ServiceResponse<Unit | null> = {
            success: false
        }

        try {
            const url = route('api.get.unit.read', {
                unit: ulid
            }, false, this.ziggyRoute);

            const response: AxiosResponse<Resource<Unit>> = await axios.get(url);

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

    public useUnitEditForm(ulid: string) {
        const url = route('api.post.unit.edit', {
            unit: ulid
        }, true, this.ziggyRoute);

        client.axios().defaults.withCredentials = true;
        client.axios().defaults.withXSRFToken = true;
        const form = useForm('post', url, {
            company_id: '',
            code: '_AUTO_',
            name: '',
            description: '',
            type: '',
        });

        return form;
    }

    public async delete(ulid: string): Promise<ServiceResponse<boolean | null>> {
        const result: ServiceResponse<boolean | null> = {
            success: false
        }

        try {
            const url = route('api.post.unit.delete', {
                unit: ulid
            }, false, this.ziggyRoute);

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

    public async getTypes(): Promise<Array<DropDownOption> | null> {
        const ddlName = 'unitTypesDDL';
        let result: Array<DropDownOption> = [];

        try {
            if (this.cacheService.getCachedDDL(ddlName) == null) {
                const url = route('api.get.unit.read_types', undefined, false, this.ziggyRoute);

                const response: AxiosResponse<Array<DropDownOption> | null> = await axios.get(url);

                this.cacheService.setCachedDDL(ddlName, response.data);
            }

            const cachedData: Array<DropDownOption> | null = this.cacheService.getCachedDDL(ddlName);

            if (cachedData != null) {
                result = cachedData as Array<DropDownOption>;
            }

            return result;
        } catch (e: unknown) {
            return result;
        }
    }
}
