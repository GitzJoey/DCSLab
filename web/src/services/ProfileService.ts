import axios from "../axios";
import { useZiggyRouteStore } from "../stores/ziggy-route";
import route, { Config } from "ziggy-js";
import { AxiosError, AxiosResponse, isAxiosError } from "axios";
import { Resource } from "../types/resources/Resource";
import { ServiceResponse } from "../types/services/ServiceResponse";
import ErrorHandlerService from "./ErrorHandlerService";
import { client, useForm } from "laravel-precognition-vue";

export default class ProfileService {
  private ziggyRoute: Config;
  private ziggyRouteStore = useZiggyRouteStore();

  private errorHandlerService;

  constructor() {
    this.ziggyRoute = this.ziggyRouteStore.getZiggy;

    this.errorHandlerService = new ErrorHandlerService();
  }

  public useUserProfileUpdateForm() {
    const url = route('api.post.db.module.profile.update', undefined, true, this.ziggyRoute);

    client.axios().defaults.withCredentials = true;
    const form = useForm('post', url, {
      name: '',

      first_name: '',
      last_name: '',
      address: '',
      city: '',
      postal_code: '',
      country: '',
      img_path: '',
      tax_id: 0,
      ic_num: 0,
      status: '',
      remarks: '',

      roles: [],

      theme: 'side-menu-light-full',
      date_format: 'dd_MMM_yyyy',
      time_format: 'hh_mm_ss',

      tokens_reset: false,
      reset_password: false,

      current_password: '',
      new_password: '',
      new_password_confirmation: '',
    });

    return form;
  }


  async updateProfile(payload: {
    first_name: string;
    last_name: string;
    address: string;
    city: string;
    postal_code: string;
    country: string;
    tax_id: number;
    ic_num: number;
    remarks: string;
  }): Promise<ServiceResponse<Resource<Record<string, never>> | null>> {
    const result: ServiceResponse<Resource<Record<string, never>>> = {
      success: false,
    };
    try {
      const url = route(
        "api.post.db.module.profile.update.profile",
        undefined,
        false,
        this.ziggyRoute
      );
      if (!url)
        return this.errorHandlerService.generateZiggyUrlErrorServiceResponse();
      const response: AxiosResponse<Resource<Record<string, never>>> =
        await axios.post(url, {
          first_name: payload.first_name,
          last_name: payload.last_name,
          address: payload.address,
          city: payload.city,
          postal_code: payload.postal_code,
          country: payload.country,
          tax_id: payload.tax_id,
          ic_num: payload.ic_num,
          remarks: payload.remarks,
        });
      result.success = true;
      result.data = response.data;
      return result;
    } catch (e) {
      if (e instanceof Error && e.message.includes("Ziggy error")) {
        return this.errorHandlerService.generateZiggyUrlErrorServiceResponse(
          e.message
        );
      } else if (isAxiosError(e)) {
        return this.errorHandlerService.generateAxiosErrorServiceResponse(
          e as AxiosError
        );
      } else {
        return result;
      }
    }
  }

  async updateRoles(
    roles: string
  ): Promise<ServiceResponse<Resource<Record<string, never>> | null>> {
    const result: ServiceResponse<Resource<Record<string, never>>> = {
      success: false,
    };
    try {
      const url = route(
        "api.post.db.module.profile.update.roles",
        undefined,
        false,
        this.ziggyRoute
      );
      if (!url)
        return this.errorHandlerService.generateZiggyUrlErrorServiceResponse();
      const response: AxiosResponse<Resource<Record<string, never>>> =
        await axios.post(url, { roles: roles });
      result.success = true;
      result.data = response.data;
      return result;
    } catch (e: unknown) {
      if (e instanceof Error && e.message.includes("Ziggy error")) {
        return this.errorHandlerService.generateZiggyUrlErrorServiceResponse(
          e.message
        );
      } else if (isAxiosError(e)) {
        return this.errorHandlerService.generateAxiosErrorServiceResponse(
          e as AxiosError
        );
      } else {
        return result;
      }
    }
  }

  async updatePassword(payload: {
    password: string;
    confirm_password: string;
    current_password: string;
  }): Promise<ServiceResponse<Resource<Record<string, never>> | null>> {
    const result: ServiceResponse<Resource<Record<string, never>>> = {
      success: false,
    };
    try {
      const url = route(
        "api.post.db.module.profile.update.password",
        undefined,
        false,
        this.ziggyRoute
      );
      if (!url)
        return this.errorHandlerService.generateZiggyUrlErrorServiceResponse();
      const response: AxiosResponse<Resource<Record<string, never>>> =
        await axios.post(url, {
          current_password: payload.current_password,
          password: payload.password,
          password_confirmation: payload.confirm_password,
        });
      result.success = true;
      result.data = response.data;
      return result;
    } catch (e) {
      if (e instanceof Error && e.message.includes("Ziggy error")) {
        return this.errorHandlerService.generateZiggyUrlErrorServiceResponse(
          e.message
        );
      } else if (isAxiosError(e)) {
        return this.errorHandlerService.generateAxiosErrorServiceResponse(
          e as AxiosError
        );
      } else {
        return result;
      }
    }
  }

  async updateUserSetting(payload: {
    theme: string;
    date_format: string;
    time_format: string;
  }): Promise<ServiceResponse<Resource<Record<string, never>> | null>> {
    const result: ServiceResponse<Resource<Record<string, never>>> = {
      success: false,
    };
    try {
      const url = route(
        "api.post.db.module.profile.update.setting",
        undefined,
        false,
        this.ziggyRoute
      );
      const response: AxiosResponse<Resource<Record<string, never>>> =
        await axios.post(url, payload);
      result.success = true;
      result.data = response.data;
      return result;
    } catch (e) {
      if (e instanceof Error && e.message.includes("Ziggy error")) {
        return this.errorHandlerService.generateZiggyUrlErrorServiceResponse(
          e.message
        );
      } else if (isAxiosError(e)) {
        return this.errorHandlerService.generateAxiosErrorServiceResponse(
          e as AxiosError
        );
      } else {
        return result;
      }
    }
  }

  async updateUser(payload: {
    name: string;
  }): Promise<ServiceResponse<Resource<Record<string, never>> | null>> {
    const result: ServiceResponse<Resource<Record<string, never>>> = {
      success: false,
    };
    try {
      const url = route(
        "api.post.db.module.profile.update.user",
        undefined,
        false,
        this.ziggyRoute
      );
      if (!url)
        return this.errorHandlerService.generateZiggyUrlErrorServiceResponse();

      const response: AxiosResponse<Resource<Record<string, never>>> =
        await axios.post(url, payload);
      result.success = true;
      result.data = response.data;
      return result;
    } catch (e) {
      if (e instanceof Error && e.message.includes("Ziggy error")) {
        return this.errorHandlerService.generateZiggyUrlErrorServiceResponse(
          e.message
        );
      } else if (isAxiosError(e)) {
        return this.errorHandlerService.generateAxiosErrorServiceResponse(
          e as AxiosError
        );
      } else {
        return result;
      }
    }
  }
}
