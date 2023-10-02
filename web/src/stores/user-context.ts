import { defineStore } from "pinia";
import { UserProfile } from "../types/models/UserProfile";

export interface UserContextState {
  isLoaded: boolean,
  isAuthenticated: boolean,
  userContext: UserProfile,
}

export const useUserContextStore = defineStore("userContext", {
  state: (): UserContextState => ({
    isLoaded: false,
    isAuthenticated: false,
    userContext: {
      id: '',
      ulid: '',
      name: '',
      email: '',
      email_verified: false,
      profile: {
        first_name: '',
        last_name: '',
        address: '',
        city: '',
        postal_code: '',
        country: '',
        status: '',
        tax_id: 0,
        ic_num: 0,
        img_path: '',
        remarks: '',
      },
      roles: [],
      companies: [],
      settings: {
        theme: '',
        date_format: '',
        time_format: '',
      }
    },
  }),
  getters: {
    getIsLoaded: state => state.isLoaded,
    getIsAuthenticated: state => state.isAuthenticated,
    getUserContext: state => state.userContext,
  },
  actions: {
    setUserContext(userContext: UserProfile) {
      this.userContext = userContext;

      this.isLoaded = true;
      this.isAuthenticated = true;
    },
  },
});
