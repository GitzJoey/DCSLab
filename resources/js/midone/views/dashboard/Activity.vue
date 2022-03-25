<template>
    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">
            {{ t('views.activity.title') }}
        </h2>
    </div>
    <div class="intro-y mt-5">
        <div class="container">
            <div class="flex flex-col md:grid grid-cols-9 mx-auto p-2">
                <template v-if="isEmptyObject(activity)">
                    <LoadingIcon icon="puff" class="w-5 h-5" />
                </template>
                <template v-for="(a, aIdx) in activity" v-if="!isEmptyObject(activity)">
                    <template v-if="a.pos === 'left'">
                        <div class="flex flex-row-reverse md:contents">
                            <div class="box col-start-1 col-end-5 p-4 rounded-xl my-4 ml-auto shadow-md">
                                <h3 class="font-semibold text-lg mb-1">{{ a.title }}</h3>
                                <p class="leading-tight text-justify">
                                    <span class="italic text-xs">{{ a.timestamp }}</span>
                                    <br/>
                                    <br/>
                                    {{ a.description }}
                                </p>
                            </div>
                            <div class="col-start-5 col-end-6 md:mx-auto relative mr-10">
                                <div class="h-full w-10 flex items-center justify-center">
                                    <div class="bg-white h-full w-1 pointer-events-none"></div>
                                </div>
                                <div class="bg-white w-10 h-10 absolute top-1/2 -mt-3 rounded-full shadow"></div>
                                <div class="w-10 h-10 absolute top-1/2 -mt-3 text-center">
                                    <RefreshCwIcon class="m-2" v-if="a.log_name === 'RoutingActivity'" />
                                    <KeyIcon class="m-2" v-if="a.log_name === 'AuthActivity'" />
                                </div>
                            </div>
                        </div>
                    </template>
                    <template v-if="a.pos === 'right'">
                        <div class="flex md:contents">
                            <div class="col-start-5 col-end-6 mr-10 md:mx-auto relative">
                                <div class="h-full w-10 flex items-center justify-center">
                                    <div class="bg-white h-full w-1 pointer-events-none"></div>
                                </div>
                                <div class="bg-white w-10 h-10 absolute top-1/2 -mt-3 rounded-full shadow"></div>
                                <div class="w-10 h-10 absolute top-1/2 -mt-3 text-center">
                                    <RefreshCwIcon class="m-2" v-if="a.log_name === 'RoutingActivity'" />
                                    <KeyIcon class="m-2" v-if="a.log_name === 'AuthActivity'" />
                                </div>
                            </div>
                            <div class="box col-start-6 col-end-10 p-4 rounded-xl my-4 mr-auto shadow-md">
                                <h3 class="font-semibold text-lg mb-1">{{ a.title }}</h3>
                                <p class="leading-tight text-justify">
                                    <span class="italic text-xs">{{ a.timestamp }}</span>
                                    <br/>
                                    <br/>
                                    {{ a.description }}
                                </p>
                            </div>
                        </div>
                    </template>
                </template>
            </div>
        </div>
    </div>
</template>

<script setup>
//#region Imports
import { onMounted, ref } from "vue";
import axios from "@/axios";
import { route } from "@/ziggy";
import { useI18n } from "vue-i18n";
//#endregion

//#region Declarations
const { t } = useI18n();
//#endregion

//#region Data - Views
const activity = ref({});
//#endregion

//#region onMounted
onMounted(() => {
    getActivity();
});
//#endregion

//#region Methods
const getActivity = () => {
    axios.get(route('api.get.db.core.activity.route.list')).then(response => {
        activity.value = response.data;
    });
}

const isEmptyObject = (obj) => {
    return _.isEmpty(obj);
}
//#endregion
</script>
