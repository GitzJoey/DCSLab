<script setup lang="ts">
import { ref, provide, computed, watch } from "vue";
import Lucide from "../Lucide";
import Notification, { NotificationElement } from "../Notification";
import Button from "../Button";
import { useNotificationWidgetStore } from "../../stores/notification-widget";

const notificationWidgetStore = useNotificationWidgetStore();
const notificationWidgetHasWork = computed(() => notificationWidgetStore.notificationWidgetHasWork);
const notificationWidgetValue = computed(() => notificationWidgetStore.getNotificationWidgetValue);

export interface NotificationManagerProps {
    debug: boolean,
}

const props = withDefaults(defineProps<NotificationManagerProps>(), {
    debug: false,
});

provide("bind[mainNotification]", (el: NotificationElement) => {
    mainNotification.value = el;
});

const mainNotification = ref<NotificationElement>();

const mainNotificationToggle = () => {
    if (mainNotification.value) {
        mainNotification.value.showToast();
    }
};

watch(notificationWidgetHasWork, (newVal, oldVal) => {
    if (notificationWidgetHasWork.value) {
        mainNotificationToggle();
    }
});
</script>

<template>
    <div>
        <Button v-if="props.debug" type="button" href="#" variant="soft-secondary" class="w-48 shadow-md"
            @click="mainNotificationToggle">
            Trigger Notification
        </Button>
        <Notification ref-key="mainNotification" :options="{ duration: notificationWidgetValue.timeout, }" class="flex">
            <Lucide icon="CheckCircle" class="text-success" />
            <div class="ml-4 mr-4">
                <div class="font-medium">{{ notificationWidgetValue.title }}</div>
                <div class="mt-1 text-slate-500">
                    {{ notificationWidgetValue.message }}
                </div>
            </div>
        </Notification>
    </div>
</template>