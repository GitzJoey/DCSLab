import { useZiggyRouteStore } from "../stores/ziggy-route";
import route, { Config } from "ziggy-js";
import { client, useForm } from "laravel-precognition-vue";
import { ServiceResponse } from "../types/services/ServiceResponse";
import { AxiosResponse, isAxiosError, AxiosError } from "axios";
import axios from "../axios";
import ErrorHandlerService from "./ErrorHandlerService";
import { TwoFactorResponse, QRCode, ConfirmPasswordStatusResponse } from "../types/models/TwoFactorAuthentication";
import { StatusCode } from "../types/enums/StatusCode";
import { UserProfile } from "../types/models/UserProfile";
import { Resource } from "../types/resources/Resource";

export default class ProfileService {
  private ziggyRoute: Config;
  private ziggyRouteStore = useZiggyRouteStore();
  private errorHandlerService;

  constructor() {
    this.ziggyRoute = this.ziggyRouteStore.getZiggy;

    this.errorHandlerService = new ErrorHandlerService();
  }

  public async readProfile(): Promise<ServiceResponse<UserProfile | null>> {
    const result: ServiceResponse<UserProfile> | null = {
      success: false,
    };

    try {
      const url = route('api.get.db.module.profile.read', undefined, false, this.ziggyRoute);

      const response: AxiosResponse<Resource<UserProfile>> = await axios.get(url);

      result.success = true;
      result.data = response.data.data;

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

  public useUpdateUserProfileForm() {
    const url = route('api.post.db.module.profile.update.user_profile', undefined, true, this.ziggyRoute);

    client.axios().defaults.withCredentials = true;
    const form = useForm('post', url, {
      name: '',
    });

    return form;
  }

  public useUpdatePersonalInfoForm() {
    const url = route('api.post.db.module.profile.update.personal_info', undefined, true, this.ziggyRoute);

    client.axios().defaults.withCredentials = true;
    const form = useForm('post', url, {
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
    });

    return form;
  }

  public useUpdateAccountSettingsForm() {
    const url = route('api.post.db.module.profile.update.account_settings', undefined, true, this.ziggyRoute);

    client.axios().defaults.withCredentials = true;
    const form = useForm('post', url, {
      theme: 'side-menu-light-full',
      date_format: 'dd_MMM_yyyy',
      time_format: 'hh_mm_ss',
    });

    return form;
  }

  public useUpdateUserRolesForm() {
    const url = route('api.post.db.module.profile.update.roles', undefined, true, this.ziggyRoute);

    client.axios().defaults.withCredentials = true;
    const form = useForm('post', url, {
      roles: '',
    });

    return form;
  }

  public useUpdatePasswordForm() {
    const url = route('api.post.db.module.profile.update.password', undefined, true, this.ziggyRoute);

    client.axios().defaults.withCredentials = true;
    const form = useForm('post', url, {
      current_password: '',
      password: '',
      password_confirmation: '',
    });

    return form;
  }

  public useUpdateTokenForm() {
    const url = route('api.post.db.module.profile.update.tokens', undefined, true, this.ziggyRoute);

    client.axios().defaults.withCredentials = true;
    const form = useForm('post', url, {
      reset_tokens: true,
    });

    return form;
  }

  public async enableTwoFactor(): Promise<ServiceResponse<TwoFactorResponse | null>> {
    const result: ServiceResponse<TwoFactorResponse | null> = {
      success: false,
    }

    try {
      const url = route('two-factor.enable', undefined, false, this.ziggyRoute);

      const response: AxiosResponse<TwoFactorResponse> = await axios.post(url);

      if (!url) return this.errorHandlerService.generateZiggyUrlErrorServiceResponse();

      if (response.status == StatusCode.OK) {
        result.success = true;
        result.data = response.data;
      } else {
        result.success = false;
        result.data = response.data
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

  public async disableTwoFactor(): Promise<ServiceResponse<TwoFactorResponse | null>> {
    const result: ServiceResponse<TwoFactorResponse | null> = {
      success: false,
    }

    try {
      const url = route('two-factor.disable', undefined, false, this.ziggyRoute);

      const response: AxiosResponse<TwoFactorResponse> = await axios.delete(url);

      if (!url) return this.errorHandlerService.generateZiggyUrlErrorServiceResponse();

      if (response.status == StatusCode.OK) {
        result.success = true;
        result.data = response.data;
      } else {
        result.success = false;
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

  public async TwoFactorConfirmPassword(password: string): Promise<ServiceResponse<TwoFactorResponse | null>> {
    const result: ServiceResponse<TwoFactorResponse | null> = {
      success: false,
    }

    try {
      const url = route('password.confirm', undefined, false, this.ziggyRoute);

      const response: AxiosResponse<TwoFactorResponse> = await axios.post(url, {
        password: password
      });

      if (!url) return this.errorHandlerService.generateZiggyUrlErrorServiceResponse();

      if (response.status == StatusCode.OK || response.status == StatusCode.Created) {
        result.success = true;
      } else {
        result.success = false;
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

  public async TwoFactorConfirmPasswordStatus(): Promise<ServiceResponse<ConfirmPasswordStatusResponse | null>> {
    const result: ServiceResponse<ConfirmPasswordStatusResponse | null> = {
      success: false,
    }

    try {
      const url = route('password.confirmation', undefined, false, this.ziggyRoute);

      const response: AxiosResponse<ConfirmPasswordStatusResponse> = await axios.get(url);

      if (!url) return this.errorHandlerService.generateZiggyUrlErrorServiceResponse();

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

  public async TwoFactorAuthenticationConfirmed(code: string): Promise<ServiceResponse<any | null>> {
    const result: ServiceResponse<any | null> = {
      success: false,
    }

    try {
      const url = route('two-factor.confirm', undefined, false, this.ziggyRoute);

      const response: AxiosResponse<any> = await axios.post(url, {
        code: code
      });

      if (!url) return this.errorHandlerService.generateZiggyUrlErrorServiceResponse();

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

  public async twoFactorQR(): Promise<ServiceResponse<QRCode | null>> {
    const result: ServiceResponse<QRCode | null> = {
      success: false,
    }

    try {
      const url = route('two-factor.qr-code', undefined, false, this.ziggyRoute);

      const response: AxiosResponse<QRCode> = await axios.get(url);

      if (!url) return this.errorHandlerService.generateZiggyUrlErrorServiceResponse();

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

  public async twoFactorRecoveryCodes(): Promise<ServiceResponse<Array<string> | null>> {
    const result: ServiceResponse<Array<string> | null> = {
      success: false,
    }

    try {
      const url = route('two-factor.recovery-codes', undefined, false, this.ziggyRoute);

      const response: AxiosResponse<Array<string>> = await axios.get(url);

      if (!url) return this.errorHandlerService.generateZiggyUrlErrorServiceResponse();

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

  public async twoFactorSecretKey(): Promise<ServiceResponse<any | null>> {
    const result: ServiceResponse<any | null> = {
      success: false,
    }

    try {
      const url = route('two-factor.secret-key', undefined, false, this.ziggyRoute);

      const response: AxiosResponse<any> = await axios.get(url);

      if (!url) return this.errorHandlerService.generateZiggyUrlErrorServiceResponse();

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
