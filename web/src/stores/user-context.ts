import { ref, readonly } from "vue";
import { defineStore } from "pinia";
import type { UserProfile } from "@/types/models/UserProfile";

export const useUserContextStore = defineStore("userContext", () => {
    const _isAuthenticated = ref(false);    
    const userContext = ref<UserProfile | null>(null);

    const isAuthenticated = readonly(_isAuthenticated);

    const setUserContext = (val: UserProfile) => {
        userContext.value = val;
        _isAuthenticated.value = true;
    };

    const clearUserContext = () => {
        userContext.value = null;
        _isAuthenticated.value = false;
    };

    return {
        isAuthenticated,
        userContext,
        setUserContext,
        clearUserContext
    };
});