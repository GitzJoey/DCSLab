<template>
    <div class="intro-y box">
        <div class="flex flex-col sm:flex-row items-center p-5 border-b border-gray-200 dark:border-dark-5">
            <h2 class="font-medium text-base mr-auto">Vimeo Player Demo</h2>
        </div>
        <div class="loader-container">
            <div class="grid grid-col-4">
                
            </div>
            <div data-vimeo-id="19231868" data-vimeo-width="640" id="handstick"></div>
        </div>
        <div class="loader-overlay" v-if="loading"></div>
    </div>
</template>

<script setup>
//#region Imports
import { onMounted, ref } from "vue";
import Player from "@vimeo/player";
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
    const player = new Player('handstick', {
        id: 19231868,
        width: 640
    });

    player.on('play', function() {
        console.log('played the video!');
    })
});
//#endregion

//#region Computed
//#endregion

//#region Watcher
//#endregion
</script>
