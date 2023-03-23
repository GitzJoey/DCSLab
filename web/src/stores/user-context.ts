import { defineStore } from "pinia";
import { UserProfileType } from "../types/resources/UserProfileType";

export interface UserContextState {
  isAuthenticatedValue: boolean,
  userContextValue: UserProfileType,
  selectedUserLocationValue: {
    company: {
      ulid: string,
    },
    branch: {
      ulid: string,
    }
  }
}

export const useUserContextStore = defineStore("userContext", {
  state: (): UserContextState => ({
    isAuthenticatedValue: false,
    userContextValue: {
      ulid: '',
      name: '',
      email: '',
      email_verified: false,
      profile: {
        full_name: '',
        status: '',
        img_path: '',
        remarks: '',
      },
      companies: [],
    },
    selectedUserLocationValue: {
      company: {
        ulid: '',
      },
      branch: {
        ulid: '',
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
      this.selectedUserLocationValue.company.ulid = companyId;
      this.selectedUserLocationValue.branch.ulid = branchId;
    },
    setSelectedUserCompany(ulid: string) {
      this.selectedUserLocationValue.company.ulid = ulid;
    },
    setSelectedUserBranch(ulid: string) {
      this.selectedUserLocationValue.branch.ulid = ulid;
    }
  },  
});
