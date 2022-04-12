<template>
</template>

<script setup>
//#region Imports
import { onMounted, ref } from "vue";
//#endregion

//#region Declarations
//#endregion

//#region Data - Pinia
//#endregion

//#region Data - UI
const loading = ref(false);
//#endregion

//#region Data - Views
//#endregion

//#region onMounted
onMounted(() => {
   listenPusher(); 
});
//#endregion

//#region Methods
const listenPusher = () => {
    Echo.channel('pusher-channel').listen('..pusher-event', (e) => {
        console.log(e);
        console.log('triggered');
    });
}
//#endregion

//#region Computed
//#endregion

//#region Watcher
//#endregion
</script>
