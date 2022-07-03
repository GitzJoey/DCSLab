<template>
    <Notification refKey="pusherNotification" class="flex flex-col sm:flex-row" :options="{ duration: 3000 }">
        <BellRingIcon class="animate-bounce"/>
        <div class="ml-4 mr-4">
            <div class="font-medium">{{ pusherNotificationTitle }}</div>
            <div class="text-slate-500 mt-1">{{ pusherNotificationMessage }}</div>
        </div>
    </Notification>
</template>

<script setup>
//#region Imports
import { onMounted, ref, provide, inject, computed, watch } from "vue";
import { useUserContextStore } from "../../stores/user-context";
//#endregion

//#region Declarations
const pusherNotification = ref();

provide("bind[pusherNotification]", (el) => {
    pusherNotification.value = el;
});

provide('triggerPusherNotification', (message) => {
    pusherNotificationToast(message);
});

//#endregion

//#region Data - Pinia
//#region Data - Pinia
const userContextStore = useUserContextStore();
const userContext = computed(() => userContextStore.userContext);
//#endregion

//#endregion

//#region Data - UI
//#endregion

//#region Data - Views
const pusherNotificationTitle = ref('');
const pusherNotificationMessage = ref('');
//#endregion

//#region onMounted
onMounted(() => {
    if (typeof(Echo) !== 'undefined') listenPusherPublic(); 
});
//#endregion

//#region Methods
const listenPusherPublic = () => {
    Echo.channel('public-channel').listen('.event-public-pusher', (e) => { 
        pusherNotificationToast('Beep...Beep!', e.message);
    });
}

const listenPusherPrivate = (hId) => {
    if (Echo.connector.channels.hasOwnProperty('private-' + 'channel-' + hId)) return;

    Echo.private('channel-' + hId).listen('.event-private-pusher', (e) => { 
        pusherNotificationToast('Message from ' + e.fromName, e.message);
    });
}

const pusherNotificationToast = (title, message) => {
    pusherNotificationTitle.value = title;
    pusherNotificationMessage.value = message;
    pusherNotification.value.showToast();
} 
//#endregion

//#region Computed
//#endregion

//#region Watcher
watch(
  userContext, (newV, oldV) => {
    if(oldV.hId != newV.hId) listenPusherPrivate(newV.hId); 
});
//#endregion
</script>
