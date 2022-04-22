<template>
    <Notification refKey="pusherNotification" class="flex flex-col sm:flex-row" :options="{ duration: 3000 }">
        <div class="ml-4 mr-4">
            <div class="font-medium">Beep...Beep!</div>
            <div class="text-slate-500 mt-1">{{ pusherNotificationMessage }}</div>
        </div>
    </Notification>
</template>

<script setup>
//#region Imports
import { onMounted, ref, provide } from "vue";
//#endregion

//#region Declarations
const pusherNotification = ref();
//#endregion

//#region 
provide("bind[pusherNotification]", (el) => {
  pusherNotification.value = el;
});

provide('triggerPusherNotification', (message) => {
  pusherNotificationToast(message);
});
//#endregion

//#region Data - Pinia
//#endregion

//#region Data - UI
//#endregion

//#region Data - Views
const pusherNotificationMessage = ref('');
//#endregion

//#region onMounted
onMounted(() => {
   listenPusher(); 
});
//#endregion

//#region Methods
const listenPusher = () => {
    Echo.channel('public-channel').listen('.event-pusher', (e) => { pusherNotificationToast(e); });
}

const pusherNotificationToast = (message) => {
    pusherNotificationMessage.value = message;
    pusherNotification.value.showToast();
} 
//#endregion

//#region Computed
//#endregion

//#region Watcher
//#endregion
</script>
