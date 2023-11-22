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

const countDown = ref<number>(0);

const mainNotification = ref<NotificationElement>();

const mainNotificationToggle = () => {
    if (mainNotification.value) {
        mainNotification.value.showToast();
    }
};

const startCountDown = () => {
    const interval = setInterval(() => {
        if (countDown.value != 0) {
            countDown.value--;
        } else {
            clearInterval(interval);
        }
    }, 1000);
}

watch(notificationWidgetHasWork, () => {
    if (notificationWidgetHasWork.value) {
        countDown.value = notificationWidgetValue.value.timeout;
        startCountDown();
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
        <Notification ref-key="mainNotification" :options="{ duration: notificationWidgetValue.timeout * 1000, }"
            class="flex">
            <Lucide icon="CheckCircle" class="text-success" />
            <div class="ml-4 mr-4">
                <div class="font-medium">{{ notificationWidgetValue.title }}</div>
                <div class="mt-1 text-slate-500">
                    {{ notificationWidgetValue.message }}&nbsp;({{ countDown }}s)
                </div>
            </div>
        </Notification>
    </div>
</template>