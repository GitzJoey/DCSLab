<template>
    <div class="intro-y box">
        <div class="flex flex-col sm:flex-row items-center p-5 border-b border-gray-200 dark:border-dark-5">
            <h2 class="font-medium text-base mr-auto">Vimeo Player Demo</h2>
        </div>
        <div class="loader-container">
            <div class="grid grid-cols-6 gap-3">
                <div class="col-span-4">
                    <div data-vimeo-id="19231868" data-vimeo-width="640" id="handstick"></div>
                </div>
                <div class="col-span-2">
                    aaaaaaaa
                </div>                
            </div>
        </div>
        <div class="loader-overlay" v-if="loading"></div>
    </div>
</template>

<script setup>
//#region Imports
import { onMounted, ref } from "vue";
import Player from "@vimeo/player";
import axios from "axios";
//#endregion

//#region Declarations
//#endregion

//#region Data - Pinia
//#endregion

//#region Data - UI
const loading = ref(false);
const vimeoAccessToken = ref('cab792ad9307652baa044008e13a97a0');
const playLists = ref([
    '691786534',
    '656704401',
    '194312144',
    '271420004',
    '676300668',
    '290120770',
    '283265177',
    '286216759',
    '680668269',
    '676644046',
    '242979596',
    '281866463',
    '585797977',
    '572113578',
    '251997032',
    '290120770',
    '292251793',
    '292658104',
    '297648729',
    '300061053',
]);
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
    });
});
//#endregion

//#region Methods
//#endregion

//#region Computed
//#endregion

//#region Watcher
//#endregion
</script>
