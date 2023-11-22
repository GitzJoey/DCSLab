import { defineStore } from "pinia";
import { NotificationWidget } from "../types/models/NotificationWidget";

export interface NotificationWidgetState {
    hasWork: boolean,
    notification: NotificationWidget,
}

export const useNotificationWidgetStore = defineStore("notificationWidgetStore", {
    state: (): NotificationWidgetState => ({
        hasWork: false,
        notification: {
            title: '',
            message: '',
            timeout: 30
        }
    }),
    getters: {
        notificationWidgetHasWork: state => state.hasWork,
        getNotificationWidgetValue: state => state.notification,
    },
    actions: {
        async setNotificationValue(notificationVal: NotificationWidget) {
            this.notification = notificationVal;
            this.hasWork = true;

            await new Promise(resolve => setTimeout(resolve, notificationVal.timeout * 1000));

            this.hasWork = false;
            this.notification = {
                title: '',
                message: '',
                timeout: 30
            }
        }
    }
});