import axios from "../axios";
import { useZiggyRouteStore } from "../stores/ziggy-route";
import { route, Config } from "ziggy-js";
import { CashAccount } from "../types/models/CashAccount";
import { Resource } from "../types/resources/Resource";
import { Collection } from "../types/resources/Collection";
import { ServiceResponse } from "../types/services/ServiceResponse";
import { AxiosError, AxiosResponse, isAxiosError } from "axios";
import ErrorHandlerService from "./ErrorHandlerService";
import { CashAccountReadAnyPaginateRequest, CashAccountReadAnyGetRequest } from "../types/services/cash_account/CashAccountRequest";
import { StatusCode } from "../types/enums/StatusCode";
import { client, useForm } from "laravel-precognition-vue";
import CacheService from "./CacheService";

export default class CashAccountService {
    private ziggyRoute: Config;
    private ziggyRouteStore = useZiggyRouteStore();

    private errorHandlerService;
    private cacheService;

    constructor() {
        this.ziggyRoute = this.ziggyRouteStore.getZiggy;

        this.errorHandlerService = new ErrorHandlerService();
        this.cacheService = new CacheService();
    }

    public useCashAccountCreateForm() {
        const url = route('api.post.cash_account.save', undefined, true, this.ziggyRoute);

        client.axios().defaults.withCredentials = true;
        client.axios().defaults.withXSRFToken = true;
        const form = useForm('post', url, {
            company_id: '',
            branch_id: '',
            code: '_AUTO_',
            name: '',
            is_bank: false,
            is_active: true,
            remarks: '',
        });

        return form;
    }

    public async readAnyPaginate(args: CashAccountReadAnyPaginateRequest): Promise<ServiceResponse<Collection<Array<CashAccount>> | null>> {
        const result: ServiceResponse<Collection<Array<CashAccount>> | null> = {
            success: false
        }

        try {
            const queryParams: Record<string, any> = {};
            if (args.with_trashed !== undefined) queryParams['with_trashed'] = args.with_trashed;            
            if (args.company_id) queryParams['company_id'] = args.company_id;
            if (args.branch_id) queryParams['branch_id'] = args.branch_id;
            
            if (args.search) queryParams['search'] = args.search;
            if (args.include_id) queryParams['include_id'] = args.include_id;

            queryParams['refresh'] = args.refresh;
            queryParams['paginate'] = {
                page: args.page,
                per_page: args.per_page
            };

            const url = route('api.get.cash_account.read_any', {
                _query: queryParams
            }, false, this.ziggyRoute);

            const response: AxiosResponse<Collection<Array<CashAccount>>> = await axios.get(url);

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

    public async readAnyGet(args: CashAccountReadAnyGetRequest): Promise<ServiceResponse<Resource<Array<CashAccount>> | null>> {
        const result: ServiceResponse<Resource<Array<CashAccount>> | null> = {
            success: false
        }

        try {
            const queryParams: Record<string, any> = {};
            if (args.with_trashed !== undefined) queryParams['with_trashed'] = args.with_trashed;            
            if (args.company_id) queryParams['company_id'] = args.company_id;            
            if (args.branch_id) queryParams['branch_id'] = args.branch_id;
            
            if (args.search) queryParams['search'] = args.search;
            if (args.include_id) queryParams['include_id'] = args.include_id;

            queryParams['refresh'] = args.refresh;
            queryParams['get'] = {
                limit: args.limit
            };

            const url = route('api.get.cash_account.read_any', {
                _query: queryParams
            }, false, this.ziggyRoute);

            const response: AxiosResponse<Resource<Array<CashAccount>>> = await axios.get(url);

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

    public async read(ulid: string): Promise<ServiceResponse<CashAccount | null>> {
        const result: ServiceResponse<CashAccount | null> = {
            success: false
        }

        try {
            const url = route('api.get.cash_account.read', {
                cash_account: ulid
            }, false, this.ziggyRoute);

            const response: AxiosResponse<Resource<CashAccount>> = await axios.get(url);

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

    public useCashAccountEditForm(ulid: string) {
        const url = route('api.post.cash_account.edit', {
            cash_account: ulid
        }, true, this.ziggyRoute);

        client.axios().defaults.withCredentials = true;
        client.axios().defaults.withXSRFToken = true;
        const form = useForm('post', url, {
            company_id: '',
            code: '_AUTO_',
            name: '',
            is_bank: false,
            is_active: true,
            remarks: '',
        });

        return form;
    }

    public async delete(ulid: string): Promise<ServiceResponse<boolean | null>> {
        const result: ServiceResponse<boolean | null> = {
            success: false
        }

        try {
            const url = route('api.post.cash_account.delete', {
                cash_account: ulid
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
}
