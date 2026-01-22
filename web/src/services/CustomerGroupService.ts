import axios from "../axios";
import { useZiggyRouteStore } from "../stores/ziggy-route";
import { route, Config } from "ziggy-js";
import { CustomerGroup } from "@/types/models/CustomerGroup";
import { Resource } from "../types/resources/Resource";
import { Collection } from "../types/resources/Collection";
import { ServiceResponse } from "../types/services/ServiceResponse";
import { AxiosError, AxiosResponse, isAxiosError } from "axios";
import ErrorHandlerService from "./ErrorHandlerService";
import { CustomerGroupReadAnyPaginateRequest, CustomerGroupReadAnyGetRequest } from "../types/services/customer-group/CustomerGroupRequest";
import { StatusCode } from "../types/enums/StatusCode";
import { client, useForm } from "laravel-precognition-vue";
import CacheService from "./CacheService";

export default class CustomerGroupService {
	private ziggyRoute: Config;
	private ziggyRouteStore = useZiggyRouteStore();

	private errorHandlerService;
	private cacheService;

	constructor() {
		this.ziggyRoute = this.ziggyRouteStore.getZiggy;

		this.errorHandlerService = new ErrorHandlerService();
		this.cacheService = new CacheService();
	}

	public useCustomerGroupCreateForm() {
		const url = route("api.post.customer_group.save", undefined, true, this.ziggyRoute);

		client.axios().defaults.withCredentials = true;
		client.axios().defaults.withXSRFToken = true;
		const form = useForm("post", url, {
			company_id: "",
			code: "_AUTO_",
			name: "",
			max_open_invoice: 0,
			max_outstanding_invoice: 0,
			max_invoice_age: 0,
			payment_term_type: "PIA",
			payment_term: 0,
			selling_point: 0,
			selling_point_multiple: 0,
			sell_at_cost: false,
			price_markup_percent: 0,
			price_markup_nominal: 0,
			price_markdown_percent: 0,
			price_markdown_nominal: 0,
			rounding_type: 1,
			rounding_digit: 0,
			remarks: "",
		});

		return form;
	}

	public async readAnyPaginate(args: CustomerGroupReadAnyPaginateRequest): Promise<ServiceResponse<Collection<Array<CustomerGroup>> | null>> {
		const result: ServiceResponse<Collection<Array<CustomerGroup>> | null> = {
			success: false,
		};

		try {
			const queryParams: Record<string, any> = {};
			if (args.with_trashed !== undefined) queryParams["with_trashed"] = args.with_trashed;
			if (args.company_id) queryParams["company_id"] = args.company_id;

			if (args.search) queryParams["search"] = args.search;
			if (args.include_id) queryParams["include_id"] = args.include_id;

			queryParams["refresh"] = args.refresh;
			queryParams["paginate"] = {
				page: args.page,
				per_page: args.per_page,
			};

			const url = route(
				"api.get.customer_group.read_any",
				{
					_query: queryParams,
				},
				false,
				this.ziggyRoute,
			);

			const response: AxiosResponse<Collection<Array<CustomerGroup>>> = await axios.get(url);

			if (response.status == StatusCode.OK) {
				result.success = true;
				result.data = response.data;
			}

			return result;
		} catch (e: unknown) {
			if (e instanceof Error && e.message.includes("Ziggy error")) {
				return this.errorHandlerService.generateZiggyUrlErrorServiceResponse(e.message);
			} else if (isAxiosError(e)) {
				return this.errorHandlerService.generateAxiosErrorServiceResponse(e as AxiosError);
			} else {
				return result;
			}
		}
	}

	public async readAnyGet(args: CustomerGroupReadAnyGetRequest): Promise<ServiceResponse<Resource<Array<CustomerGroup>> | null>> {
		const result: ServiceResponse<Resource<Array<CustomerGroup>> | null> = {
			success: false,
		};

		try {
			const queryParams: Record<string, any> = {};
			if (args.with_trashed !== undefined) queryParams["with_trashed"] = args.with_trashed;
			if (args.company_id) queryParams["company_id"] = args.company_id;

			if (args.search) queryParams["search"] = args.search;
			if (args.include_id) queryParams["include_id"] = args.include_id;

			queryParams["refresh"] = args.refresh;
			queryParams["get"] = {
				limit: args.limit,
			};

			const url = route(
				"api.get.customer_group.read_any",
				{
					_query: queryParams,
				},
				false,
				this.ziggyRoute,
			);

			const response: AxiosResponse<Resource<Array<CustomerGroup>>> = await axios.get(url);

			if (response.status == StatusCode.OK) {
				result.success = true;
				result.data = response.data;
			}

			return result;
		} catch (e: unknown) {
			if (e instanceof Error && e.message.includes("Ziggy error")) {
				return this.errorHandlerService.generateZiggyUrlErrorServiceResponse(e.message);
			} else if (isAxiosError(e)) {
				return this.errorHandlerService.generateAxiosErrorServiceResponse(e as AxiosError);
			} else {
				return result;
			}
		}
	}

	public async read(ulid: string): Promise<ServiceResponse<CustomerGroup | null>> {
		const result: ServiceResponse<CustomerGroup | null> = {
			success: false,
		};

		try {
			const url = route(
				"api.get.customer_group.read",
				{
					customer_group: ulid,
				},
				false,
				this.ziggyRoute,
			);

			const response: AxiosResponse<Resource<CustomerGroup>> = await axios.get(url);

			if (response.status == StatusCode.OK) {
				result.success = true;
				result.data = response.data.data;
			}

			return result;
		} catch (e: unknown) {
			if (e instanceof Error && e.message.includes("Ziggy error")) {
				return this.errorHandlerService.generateZiggyUrlErrorServiceResponse(e.message);
			} else if (isAxiosError(e)) {
				return this.errorHandlerService.generateAxiosErrorServiceResponse(e as AxiosError);
			} else {
				return result;
			}
		}
	}

	public useCustomerGroupEditForm(ulid: string) {
		const url = route(
			"api.post.customer_group.edit",
			{
				customer_group: ulid,
			},
			true,
			this.ziggyRoute,
		);

		client.axios().defaults.withCredentials = true;
		client.axios().defaults.withXSRFToken = true;
		const form = useForm("post", url, {
			company_id: "",
			code: "_AUTO_",
			name: "",
			max_open_invoice: 0,
			max_outstanding_invoice: 0,
			max_invoice_age: 0,
			payment_term_type: "PIA",
			payment_term: 0,
			selling_point: 0,
			selling_point_multiple: 0,
			sell_at_cost: false,
			price_markup_percent: 0,
			price_markup_nominal: 0,
			price_markdown_percent: 0,
			price_markdown_nominal: 0,
			rounding_type: 1,
			rounding_digit: 0,
			remarks: "",
		});

		return form;
	}

	public async delete(ulid: string): Promise<ServiceResponse<boolean | null>> {
		const result: ServiceResponse<boolean | null> = {
			success: false,
		};

		try {
			const url = route(
				"api.post.customer_group.delete",
				{
					customer_group: ulid,
				},
				false,
				this.ziggyRoute,
			);

			const response: AxiosResponse<boolean | null> = await axios.post(url);

			if (response.status == StatusCode.OK) {
				result.success = true;
				result.data = response.data;
			}

			return result;
		} catch (e: unknown) {
			if (e instanceof Error && e.message.includes("Ziggy error")) {
				return this.errorHandlerService.generateZiggyUrlErrorServiceResponse(e.message);
			} else if (isAxiosError(e)) {
				return this.errorHandlerService.generateAxiosErrorServiceResponse(e as AxiosError);
			} else {
				return result;
			}
		}
	}
}
