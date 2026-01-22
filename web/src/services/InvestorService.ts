import axios from "../axios";
import { useZiggyRouteStore } from "../stores/ziggy-route";
import { route, Config } from "ziggy-js";
import { Investor } from "../types/models/Investor";
import { Resource } from "../types/resources/Resource";
import { Collection } from "../types/resources/Collection";
import { ServiceResponse } from "../types/services/ServiceResponse";
import { AxiosError, AxiosResponse, isAxiosError } from "axios";
import ErrorHandlerService from "./ErrorHandlerService";
import { StatusCode } from "../types/enums/StatusCode";
import { client, useForm } from "laravel-precognition-vue";
import { InvestorReadAnyGetRequest, InvestorReadAnyPaginateRequest } from "../types/services/investor/InvestorRequest";

export default class InvestorService {
    private ziggyRoute: Config;
    private ziggyRouteStore = useZiggyRouteStore();

    private errorHandlerService;

    constructor() {
        this.ziggyRoute = this.ziggyRouteStore.getZiggy;

        this.errorHandlerService = new ErrorHandlerService();
    }

    public useInvestorCreateForm() {
        const url = route('api.post.investor.save', undefined, true, this.ziggyRoute);

        client.axios().defaults.withCredentials = true;
        client.axios().defaults.withXSRFToken = true;
        const form = useForm('post', url, {
            company_id: '',
            code: '_AUTO_',
            name: '',
            remarks: '',
        });

        return form;
    }

    public async readAnyPaginate(args: InvestorReadAnyPaginateRequest): Promise<ServiceResponse<Collection<Array<Investor>> | null>> {
        const result: ServiceResponse<Collection<Array<Investor>> | null> = {
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

            const url = route('api.get.investor.read_any', {
                _query: queryParams
            }, false, this.ziggyRoute);

            const response: AxiosResponse<Collection<Array<Investor>>> = await axios.get(url);

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

    public async readAnyGet(args: InvestorReadAnyGetRequest): Promise<ServiceResponse<Resource<Array<Investor>> | null>> {
        const result: ServiceResponse<Resource<Array<Investor>> | null> = {
            success: false
        }

        try {
            const queryParams: Record<string, any> = {};
            if (args.with_trashed !== undefined) queryParams['with_trashed'] = args.with_trashed;
            if (args.company_id) queryParams['company_id'] = args.company_id;
            if (args.search) queryParams['search'] = args.search;
            if (args.include_id) queryParams['include_id'] = args.include_id;

            queryParams['refresh'] = args.refresh;
            queryParams['get'] = {
                limit: args.limit
            };

            const url = route('api.get.investor.read_any', {
                _query: queryParams
            }, false, this.ziggyRoute);

            const response: AxiosResponse<Resource<Array<Investor>>> = await axios.get(url);

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

    public async read(ulid: string): Promise<ServiceResponse<Investor | null>> {
        const result: ServiceResponse<Investor | null> = {
            success: false
        }

        try {
            const url = route('api.get.investor.read', {
                investor: ulid
            }, false, this.ziggyRoute);

            const response: AxiosResponse<Resource<Investor>> = await axios.get(url);

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

    public useInvestorEditForm(ulid: string) {
        const url = route('api.post.investor.edit', {
            investor: ulid
        }, true, this.ziggyRoute);

        client.axios().defaults.withCredentials = true;
        client.axios().defaults.withXSRFToken = true;
        const form = useForm('post', url, {
            company_id: '',
            code: '_AUTO_',
            name: '',
            remarks: '',
        });

        return form;
    }

    public async delete(ulid: string): Promise<ServiceResponse<boolean | null>> {
        const result: ServiceResponse<boolean | null> = {
            success: false,
        }

        try {
            const url = route('api.post.investor.delete', {
                investor: ulid
            }, false, this.ziggyRoute);

            const response: AxiosResponse<boolean | null> = await axios.post(url);

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
}
