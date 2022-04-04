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

    loadPlaylist();
});
//#endregion

//#region Methods
const loadPlaylist = () => {
    let data = {

    };

    let headers = {
        'Authorization': 'basic base64_encode(gitzjoey:pIdeOO1232$',
        'Content-Type': 'application/json',
        'Accept': 'application/vnd.vimeo.*+json;version=3.4'
    };

    axios.post('https://api.vimeo.com/oauth/authorize/client', data, {
        headers: headers
    }).then((response) => {
        console.log(response);
    }).catch((error) => {
        console.log(error);
    })
}
//#endregion

//#region Computed
//#endregion

//#region Watcher
//#endregion
</script>
