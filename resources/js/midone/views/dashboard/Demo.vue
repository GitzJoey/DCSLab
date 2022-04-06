<template>

    <div class="intro-y box">
        <div class="flex flex-col sm:flex-row items-center p-5 border-b border-gray-200 dark:border-dark-5">
            <h2 class="font-medium text-base mr-auto">Vimeo Player Demo</h2>
        </div>
        <div class="loader-container">
            <div class="grid grid-cols-6 my-3">
                <div class="col-span-4">
                    <div class="flex flex-col justify-center items-center">
                        <div class="box-content p-3 shadow-md rounded-lg aspect-w-16 aspect-h-9">
                            <div id="handstick"></div>
                        </div>
                        <h3 class="text-2xl font-medium leading-none mt-3">{{ playingVid.vimeoData.name }}</h3>
                    </div>
                </div>
                <div class="col-span-2 border-l">
                    <div class="flex flex-col justify-center items-center">
                        <div class="box-content w-64 p-3 shadow-lg mb-2 hover:cursor-pointer" v-for="p in playLists" @click="playListsClick(p.vId)">
                            <img class="hover:border" :src="p.vimeoData.pictures.sizes[2].link" alt="" />
                            <h5 class="text-lg font-medium leading-none mt-3">{{ truncateString(p.vimeoData.name, 20) }}</h5>
                            <div class="italic">{{ p.vimeoData.user.name }}</div>
                            <br>
                            <div>{{ truncateString(p.vimeoData.description, 120) }} </div>
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
import { onMounted, ref, watch } from "vue";
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
//cab792ad9307652baa044008e13a97a0
const vimeoAccessToken = ref('');
const playLists = ref([]);
const playingVid = ref({
    vId: '',
    vimeoData: {}
});
var player = undefined;
//#endregion

//#region Data - Views
//#endregion

//#region onMounted
onMounted(() => {
    getPlayLists();
    firstLoad();
});
//#endregion

//#region Methods
const firstLoad = async () => {
    let response = await plainAxios.get('https://api.vimeo.com/videos/19231868', {
        headers: {
            'Authorization': 'Bearer ' + vimeoAccessToken.value
        }
    });

    playingVid.value = {
        vId: '19231868',
        vimeoData: response.data
    };
}

const playListsClick = (vId) => {
    let o = _.find(playLists.value, { vId: vId });
    playingVid.value = {
        vId: o.vId,
        vimeoData: o.vimeoData
    };
}

const loadVimeoData = async (vId) => {
    let response = await plainAxios.get('https://api.vimeo.com/videos/' + vId, {
        headers: {
            'Authorization': 'Bearer ' + vimeoAccessToken.value
        }
    });

    return response.data;
}

const getPlayLists = () => {
    let vIds = [
        '691786534', '656704401', '194312144', '271420004', '676300668',
        '290120770', '283265177', '286216759', '680668269', '676644046',
        '242979596', '281866463', '585797977', '572113578', '251997032',
        '290120770', '292251793', '292658104', '297648729', '300061053',
    ];

    _.map(vIds, async (v) => {
        let d = await loadVimeoData(v);

        playLists.value.push({
            vId: v,
            vimeoData: d
        });
    }); 
}

const truncateString = (str, n) => {
    if (!str) return str;
    
    return (str.length > n) ? str.substring(0, n) + "..." : str;
}

const playVideo = () => {
    if (player === undefined) {
        player = new Player('handstick', {
            id: playingVid.value.vId,
            width: 640,
            title: false,
            autoplay: true,
            byline: false
        });
    } else {
        player.loadVideo({id: playingVid.value.vId});
    }
}
//#endregion

//#region Computed
//#endregion

//#region Watcher
watch(playingVid, () => {
    playVideo();
});
//#endregion
</script>
