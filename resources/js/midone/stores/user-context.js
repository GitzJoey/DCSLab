import { defineStore } from "pinia";
import axios from "@/axios";

export const useUserContextStore = defineStore("userContext", {
    state: () => ({
        userContext: {},
        selectedUserCompany: ''
    }),
    getter: {
        getUserContext: state => state.userContext,
        getSelectedUserCompany: state => state.selectedUserCompany
    },
    actions: {
        fetchUserContext() {
            axios.get('/api/get/dashboard/core/profile/read').then(response => {
                this.userContext = response.data;
            });
        },
        setSelectedUserCompany(hId) {
            this.selectedUserCompany = hId;
        }
    }
});