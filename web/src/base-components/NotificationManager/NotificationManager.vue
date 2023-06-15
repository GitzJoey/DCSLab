<script setup lang="ts">
import { ref, provide } from "vue";
import Lucide from "../Lucide";
import Notification, { NotificationElement } from "../Notification";

provide("bind[mainNotification]", (el: NotificationElement) => {
    mainNotification.value = el;
});

const mainNotification = ref<NotificationElement>();

const mainNotificationToggle = () => {
    if (mainNotification.value) {
        mainNotification.value.showToast();
    }
};
</script>

<template>
    <Notification ref-key="mainNotification" :options="{ duration: 3000, }" class="flex">
        <Lucide icon="CheckCircle" class="text-success" />
        <div class="ml-4 mr-4">
            <div class="font-medium">Message Saved!</div>
            <div class="mt-1 text-slate-500">
                The message will be sent in 5 minutes.
            </div>
        </div>
    </Notification>
</template>