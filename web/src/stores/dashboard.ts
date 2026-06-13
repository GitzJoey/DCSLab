import { ref, computed } from "vue";
import { defineStore } from "pinia";

export const useDashboardStore = defineStore("dashboardStore", () => {
    const screenMaskValue = ref<boolean>(false);

    const getScreenMaskValue = computed(() => screenMaskValue.value);

    const setScreenMaskValue = (screenMaskVal: boolean) => {
        screenMaskValue.value = screenMaskVal;
    };

    const toggleScreenMaskValue = () => {
        screenMaskValue.value = !screenMaskValue.value;
    };

    return {
        getScreenMaskValue,
        setScreenMaskValue,
        toggleScreenMaskValue,
    };
});