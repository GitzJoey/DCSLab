import { defineStore } from "pinia";
import type { UserProfile } from "@/types/models/UserProfile";

export interface UserContextState {
    isLoaded: boolean,
    isAuthenticated: boolean,
    userContext: UserProfile,
}

const initialUserContext: UserProfile = {
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
  },
  two_factor: false,
  personal_access_tokens: 0,
};

export const useUserContextStore = defineStore({
  id: "userContext",
  state: (): UserContextState => ({
    isLoaded: false,
    isAuthenticated: false,
    userContext: initialUserContext,
  }),
  getters: {
    getIsLoaded(state: UserContextState) {
      return state.isLoaded;
    },
    getIsAuthenticated(state: UserContextState) {
      return state.isAuthenticated;
    },
    getUserContext(state: UserContextState) {
      return state.userContext;
    },
  },
  actions: {
    setUserContext(userContext: UserProfile) {
      this.userContext = userContext;

      this.isLoaded = true;
      this.isAuthenticated = true;
    },
  },
});