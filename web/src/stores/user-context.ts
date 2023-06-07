import { defineStore } from "pinia";
import { UserProfileType } from "../types/resources/UserProfileType";

export interface UserContextState {
  isAuthenticatedValue: boolean,
  userContextValue: UserProfileType,
  selectedUserLocationValue: {
    company: {
      id: string,
      ulid: string,
      name: string
    },
    branch: {
      id: string,
      ulid: string,
      name: string
    }
  }
}

export const useUserContextStore = defineStore("userContext", {
  state: (): UserContextState => ({
    isAuthenticatedValue: false,
    userContextValue: {
      id: '',
      ulid: '',
      name: '',
      email: '',
      email_verified: false,
      profile: {
        full_name: '',
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
      companies: [],
    },
    selectedUserLocationValue: {
      company: {
        id: '',
        ulid: '',
        name: ''
      },
      branch: {
        id: '',
        ulid: '',
        name: ''
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
    clearSelectedUserLocation() {
      this.selectedUserLocationValue.company = { id: '', ulid: '', name: '' };
      this.selectedUserLocationValue.branch = { id: '', ulid: '', name: '' };
    },
    setSelectedUserLocationCompany(companyId: string, companyUlid: string, companyName: string) {
      this.clearSelectedUserLocation();

      this.selectedUserLocationValue.company.id = companyId;
      this.selectedUserLocationValue.company.ulid = companyUlid;
      this.selectedUserLocationValue.company.name = companyName;
    },
    setSelectedUserLocationBranch(branchId: string, branchUlid: string, branchName: string) {
      this.clearSelectedUserLocation();

      this.selectedUserLocationValue.branch.id = branchId;
      this.selectedUserLocationValue.branch.ulid = branchUlid;
      this.selectedUserLocationValue.branch.name = branchName;
    }
  },
});
