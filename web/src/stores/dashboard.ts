import { defineStore } from "pinia";

export interface DashboardState {
    screenMaskValue: boolean;
}

export const useDashboardStore = defineStore("dashboardStore", {
    state: (): DashboardState => ({
        screenMaskValue: false,
    }),
    getters: {
        getScreenMaskValue: state => state.screenMaskValue,
    },
    actions: {
        setScreenMaskValue(screenMaskVal: boolean) {
            this.screenMaskValue = screenMaskVal;
        },
        toggleScreenMaskValue() {
            this.screenMaskValue = !this.screenMaskValue;
        }
    } 
});