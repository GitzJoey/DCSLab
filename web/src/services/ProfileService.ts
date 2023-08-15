import axios from "../axios";
import { useZiggyRouteStore } from "../stores/ziggy-route";
import route, { Config } from "ziggy-js";
import { User } from "../types/models/User";
import { AxiosError, AxiosResponse, isAxiosError } from "axios";
import { ServiceResponse } from "../types/services/ServiceResponse";
import ErrorHandlerService from "./ErrorHandlerService";
import { UserProfileUpdateUserProfileFormFieldValues } from "../types/requests/UserProfileUpdateUserProfileFormFieldValues";
import { StatusCode } from "../types/enums/StatusCode";
import { Profile } from "../types/models/Profile";
import { UserProfileUpdateUserRoleFormFieldValues } from "../types/requests/UserProfileUpdateUserRoleFormFieldValues";
import { Role } from "../types/models/Role";
import { UserProfileUpdateUserPasswordFormFieldValues } from "../types/requests/UserProfileUpdateUserPasswordFormFieldValues";
import { Setting } from "../types/models/Setting";
import { UserProfileUpdateUserSettingFormFieldValues } from "../types/requests/UserProfileUpdateUserSettingFormFieldValues";
import { UserProfileUpdateUserFormFieldValues } from "../types/requests/UserProfileUpdateUserFormFieldValues";

export default class ProfileService {
  private ziggyRoute: Config;
  private ziggyRouteStore = useZiggyRouteStore();

  private errorHandlerService;

  constructor() {
    this.ziggyRoute = this.ziggyRouteStore.getZiggy;

    this.errorHandlerService = new ErrorHandlerService();
  }

  async updateProfile(payload: UserProfileUpdateUserProfileFormFieldValues): Promise<ServiceResponse<Profile | null>> {
    const result: ServiceResponse<Profile | null> = {
      success: false,
    };
    try {
      const url = route("api.post.db.module.profile.update.profile", undefined, false, this.ziggyRoute);
      if (!url) return this.errorHandlerService.generateZiggyUrlErrorServiceResponse();
      
      const response: AxiosResponse<Profile> = await axios.post(url, payload);

        if (response.status == StatusCode.OK) {
          result.success = true;
          result.data = response.data;
        }
        
      return result;
    } catch (e) {
      if (e instanceof Error && e.message.includes("Ziggy error")) {
        return this.errorHandlerService.generateZiggyUrlErrorServiceResponse(e.message);
      } else if (isAxiosError(e)) {
        return this.errorHandlerService.generateAxiosErrorServiceResponse(e as AxiosError);
      } else {
        return result;
      }
    }
  }

  async updateRoles(payload: UserProfileUpdateUserRoleFormFieldValues): Promise<ServiceResponse<Role | null>> {
    const result: ServiceResponse<Role | null> = {
      success: false,
    };
    
    try {
      const url = route("api.post.db.module.profile.update.roles", undefined, false, this.ziggyRoute);
      if (!url) return this.errorHandlerService.generateZiggyUrlErrorServiceResponse();      

      const response: AxiosResponse<Role> = await axios.post(url, payload);
      
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

  async updatePassword(payload: UserProfileUpdateUserPasswordFormFieldValues): Promise<ServiceResponse<User | null>> {
    const result: ServiceResponse<User | null> = {
      success: false,
    };

    try {
      const url = route("api.post.db.module.profile.update.password", undefined, false, this.ziggyRoute);
      if (!url) return this.errorHandlerService.generateZiggyUrlErrorServiceResponse();

      const response: AxiosResponse<User> = await axios.post(url, payload);

      if (response.status == StatusCode.OK) {
          result.success = true;
          result.data = response.data;
      }

      return result;
    } catch (e) {
      if (e instanceof Error && e.message.includes("Ziggy error")) {
        return this.errorHandlerService.generateZiggyUrlErrorServiceResponse(e.message);
      } else if (isAxiosError(e)) {
        return this.errorHandlerService.generateAxiosErrorServiceResponse(e as AxiosError);
      } else {
        return result;
      }
    }
  }

  async updateUserSetting(payload: UserProfileUpdateUserSettingFormFieldValues): Promise<ServiceResponse<Setting | null>> {
    const result: ServiceResponse<Setting | null> = {
      success: false,
    };

    try {
      const url = route("api.post.db.module.profile.update.setting", undefined, false, this.ziggyRoute);
      if (!url) return this.errorHandlerService.generateZiggyUrlErrorServiceResponse();

      const response: AxiosResponse<Setting> = await axios.post(url, payload);

      if (response.status == StatusCode.OK) {
        result.success = true;
        result.data = response.data;
      }

      return result;
    } catch (e) {
      if (e instanceof Error && e.message.includes("Ziggy error")) {
        return this.errorHandlerService.generateZiggyUrlErrorServiceResponse(e.message);
      } else if (isAxiosError(e)) {
        return this.errorHandlerService.generateAxiosErrorServiceResponse(e as AxiosError);
      } else {
        return result;
      }
    }
  }

  async updateUser(payload: UserProfileUpdateUserFormFieldValues): Promise<ServiceResponse<User | null>> {
    const result: ServiceResponse<User | null> = {
      success: false,
    };
    try {
      const url = route("api.post.db.module.profile.update.user", undefined, false, this.ziggyRoute);
      if (!url) return this.errorHandlerService.generateZiggyUrlErrorServiceResponse();

      const response: AxiosResponse<User> = await axios.post(url, payload);

      if (response.status == StatusCode.OK) {
        result.success = true;
        result.data = response.data;
      }

      return result;
    } catch (e) {
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
