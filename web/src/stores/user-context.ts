import { defineStore } from "pinia";
import { UserProfileType } from "../types/resources/UserProfileType";

export interface UserContextState {
  isAuthenticatedValue: boolean,
  userContextValue: UserProfileType,
  selectedUserLocationValue: {
    company: {
      uuid: string,
    },
    branch: {
      uuid: string,
    }
  }
}

export const useUserContextStore = defineStore("userContext", {
  state: (): UserContextState => ({
    isAuthenticatedValue: false,
    userContextValue: {
      uuid: '',
      name: '',
      email: '',
      email_verified: false,
      profile: {
        full_name: '',
      },
      companies: [],
    },
    selectedUserLocationValue: {
      company: {
        uuid: '',
      },
      branch: {
        uuid: '',
      }
    }
  }),
  getters: {
    getIsAuthenticated: state => state.isAuthenticatedValue,
    getUserContext: state => state.userContextValue,
    getSelectedUserLocation: state => state.selectedUserLocationValue,
    getSelectedUserCompany: state => state.selectedUserLocationValue.company,
    getSelectedUserBranch: state => state.selectedUserLocationValue.branch,
  },
  actions: {
    setUserContext(userContext: UserProfileType) {
      this.userContextValue = userContext;

      this.isAuthenticatedValue = true;
    },
    setSelectedUserLocation(companyId: string, branchId: string) {
      this.selectedUserLocationValue.company.uuid = companyId;
      this.selectedUserLocationValue.branch.uuid = branchId;
    },
    setSelectedUserCompany(uuid: string) {
      this.selectedUserLocationValue.company.uuid = uuid;
    },
    setSelectedUserBranch(uuid: string) {
      this.selectedUserLocationValue.branch.uuid = uuid;
    }
  },  
});
