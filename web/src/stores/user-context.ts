import { defineStore } from "pinia";
import { userProfileType } from "../types/userProfileType";
import { companyType } from "../types/companyType";

interface UserContextState {
  userContextValue: userProfileType,
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
    userContextValue: {
      uuid: '',
      name: '',
      email: '',
      emailVerified: false,
      profile: {
        firstName: '',
        lastName: '',
        status: '',
        imgPath: '',
        remarks: '',
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
    getUserContext: state => state.userContextValue,
    getSelectedUserLocation: state => state.selectedUserLocationValue,
    getSelectedUserCompany: state => state.selectedUserLocationValue.company,
    getSelectedUserBranch: state => state.selectedUserLocationValue.branch,
  },
  actions: {
    setUserContext(userContext: UserContextState) {
      this.userContextValue = userContext.userContextValue;
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
