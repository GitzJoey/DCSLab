import { defineStore } from "pinia";
import axios from "@/axios";

export const useUserContextStore = defineStore("userContext", {
    state: () => ({
        userContext: {},
        selectedUserCompany: '',
        selectedUserBranch: ''
    }),
    getter: {
        getUserContext: state => state.userContext,
        getSelectedUserCompany: state => state.selectedUserCompany,
        getSelectedUserBranch: state => state.selectedUserBranch
    },
    actions: {
        fetchUserContext() {
            axios.get('/api/get/dashboard/core/profile/read').then(response => {
                this.userContext = response.data;
            });
        },
        setSelectedUserCompany(hId) {
            this.selectedUserCompany = hId;
        },
        setSelectedUserBranch(hId) {
            this.selectedUserBranch = hId;
        }
    }
});