import { defineStore } from "pinia";
import axios from "@/axios";

export const useUserContextStore = defineStore("userContext", {
    state: () => ({
        userContext: {},
        selectedUserCompany: { hId: '', uuid: '' },
        selectedUserBranch: { hId: '', uuid: '' }
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
        setSelectedUserCompany(hId, uuid) {
            this.selectedUserCompany.hId = hId;
            this.selectedUserCompany.uuid = uuid;
        },
        setSelectedUserBranch(hId, uuid) {
            this.selectedUserBranch.hId = hId;
            this.selectedUserBranch.uuid = uuid;
        }
    }
});