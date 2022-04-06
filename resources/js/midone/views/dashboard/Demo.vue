<template>

    <div class="intro-y box">
        <div class="flex flex-col sm:flex-row items-center p-5 border-b border-gray-200 dark:border-dark-5">
            <h2 class="font-medium text-base mr-auto">Vimeo Player Demo</h2>
        </div>
        <div class="loader-container">
            <div class="grid grid-cols-6 my-3">
                <div class="col-span-4">
                    <div class="flex justify-center items-center">
                        <div class="box-content m-2 px-3 aspect-w-16 aspect-h-9">
                            <div id="handstick"></div>
                        </div>
                    </div>
                </div>
                <div class="col-span-2 border-l">
                    <div class="flex flex-col justify-center items-center">
                        <div class="box-content w-64 h-48 m-2 border" v-for="p in playLists">
                            <img src="https://i.vimeocdn.com/video/1400315973-aae13400bbd6fb92520a09e07790db858747c056025772ab5cb1485760caac27-d_100x75?r=pad" alt=""/>
                        </div>
                    </div>
                </div>                
            </div>
            <button class="btn" type="button" @click="loadVimeoThumb('300061053')">Click</button>
        </div>
        <div class="loader-overlay" v-if="loading"></div>
    </div>
</template>

<script setup>
//#region Imports
import { onMounted, ref } from "vue";
import Player from "@vimeo/player";
import { default as axios, plainAxios } from "@/axios";
import _ from "lodash";
//#endregion

//#region Declarations
//#endregion

//#region Data - Pinia
//#endregion

//#region Data - UI
const loading = ref(false);
const vimeoAccessToken = ref('cab792ad9307652baa044008e13a97a0');
const playLists = ref([]);
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

    getPlayLists();
});
//#endregion

//#region Methods
const loadVimeoThumb = (vId) => {
    plainAxios.get('https://api.vimeo.com/videos/' + vId + '/pictures', {
        headers: {
            'Authorization': 'Bearer cab792ad9307652baa044008e13a97a0'
        }
    }).then(response => {
        return response.data.data[0].sizes[0].link;
    })
}

const getPlayLists = () => {
    let vIds = [
        '691786534', '656704401', '194312144', '271420004', '676300668',
        '290120770', '283265177', '286216759', '680668269', '676644046',
        '242979596', '281866463', '585797977', '572113578', '251997032',
        '290120770', '292251793', '292658104', '297648729', '300061053',
    ];

    setTimeout(() => {
        _.map(vIds, async (v) => {
            let thumbLink = await loadVimeoThumb(v);
            console.log(thumbLink);
            playLists.value.push({
                vId: v,
                thumb: thumbLink
            });
        });
    }, 5000)
 
}
//#endregion

//#region Computed
//#endregion

//#region Watcher
//#endregion
</script>
