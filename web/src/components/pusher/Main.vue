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
import { onMounted, ref, provide, computed, watch } from "vue";
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
    
});
//#endregion

//#region Methods
const listenPusherPublic = () => {

}

const listenPusherPrivate = (hId) => {

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
