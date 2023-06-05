import { defineStore } from "pinia";

export interface DashboardState {
    screenMaskValue: boolean,
    layoutValue: 'side-menu' | 'simple-menu' | undefined
}

export const useDashboardStore = defineStore("dashboardStore", {
    state: (): DashboardState => ({
        screenMaskValue: false,
        layoutValue: 'side-menu'
    }),
    getters: {
        getScreenMaskValue: state => state.screenMaskValue,
        getLayoutValue: state => state.layoutValue
    },
    actions: {
        setScreenMaskValue(screenMaskVal: boolean) {
            this.screenMaskValue = screenMaskVal;
        },
        toggleLayoutValue() {
            if (this.layoutValue == 'side-menu') this.layoutValue = 'simple-menu';
            else this.layoutValue = 'side-menu';
        },
        toggleScreenMaskValue() {
            this.screenMaskValue = !this.screenMaskValue;
        }
    }
});