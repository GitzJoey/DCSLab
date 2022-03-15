<template>
    <div class="relative">
        <div class="grid grid-cols-12 gap-6">
            <div class="col-span-12 xl:col-span-9 2xl:col-span-9 z-10">
            </div>
            <div class="report-box-3 px-5 pt-8 pb-14 col-span-12 z-10">
                <div class="col-span-12">
                    <div class="intro-y flex items-center h-10">
                        <h2 class="text-lg font-medium truncate mr-5">
                            {{ greet }}
                        </h2>
                        <a href="" class="ml-auto flex items-center text-primary"> <RefreshCcwIcon class="w-4 h-4 mr-3" /></a>
                    </div>
                    <div class="grid grid-cols-12 gap-6 mt-5">
                        <div class="col-span-12 sm:col-span-6 xl:col-span-3 intro-y">
                            <div class="report-box zoom-in">
                                <div class="box p-5">
                                    <div class="flex">
                                        <PlusCircleIcon class="report-box__icon text-primary"/> 
                                        <div class="ml-auto">
                                            <div class="report-box__indicator bg-success tooltip cursor-pointer" title=""><ChevronUpIcon class="w-4 h-4 ml-0.5"/></div>
                                        </div>
                                    </div>
                                    <div class="text-3xl font-medium leading-8 mt-6"></div>
                                    <div class="text-base text-slate-500 mt-1"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-6 xl:col-span-3 intro-y">
                            <div class="report-box zoom-in">
                                <div class="box p-5">
                                    <div class="flex">
                                        <PlusCircleIcon class="report-box__icon text-pending"/> 
                                        <div class="ml-auto">
                                            <div class="report-box__indicator bg-danger tooltip cursor-pointer" title=""> <ChevronDownIcon class="w-4 h-4 ml-0.5"/></div>
                                        </div>
                                    </div>
                                    <div class="text-3xl font-medium leading-8 mt-6"></div>
                                    <div class="text-base text-slate-500 mt-1"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-6 xl:col-span-3 intro-y">
                            <div class="report-box zoom-in">
                                <div class="box p-5">
                                    <div class="flex">
                                        <PlusCircleIcon class="report-box__icon text-warning"/> 
                                        <div class="ml-auto">
                                            <div class="report-box__indicator bg-success tooltip cursor-pointer" title=""><ChevronUpIcon class="w-4 h-4 ml-0.5"/></div>
                                        </div>
                                    </div>
                                    <div class="text-3xl font-medium leading-8 mt-6"></div>
                                    <div class="text-base text-slate-500 mt-1"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-6 xl:col-span-3 intro-y">
                            <div class="report-box zoom-in">
                                <div class="box p-5">
                                    <div class="flex">
                                        <PlusCircleIcon class="report-box__icon text-success"/> 
                                        <div class="ml-auto">
                                            <div class="report-box__indicator bg-success tooltip cursor-pointer" title=""><ChevronUpIcon class="w-4 h-4 ml-0.5"/></div>
                                        </div>
                                    </div>
                                    <div class="text-3xl font-medium leading-8 mt-6"></div>
                                    <div class="text-base text-slate-500 mt-1"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="report-box-3 report-box-3--content grid grid-cols-12 gap-6 xl:-mt-5 2xl:-mt-8 -mb-10 z-40 2xl:z-10">
        <div class="col-span-12 2xl:col-span-9">
            <div class="grid grid-cols-12 gap-6">
                <div class="col-span-12 xl:col-span-4 mt-6">
                    <div class="intro-y flex items-center h-10">
                    </div>
                    <div class="mt-5">
                        <br v-for="n in 1000" />
                    </div>
                </div>
                <div class="col-span-12 mt-6">
                    <div class="intro-y overflow-auto lg:overflow-visible mt-8 sm:mt-0">
                    </div>
                    <div class="intro-y flex flex-wrap sm:flex-row sm:flex-nowrap items-center mt-3">
                    </div>
                </div>
            </div>
        </div>
        <div class="col-span-12 2xl:col-span-3 relative z-10">
            <div class="2xl:border-l border-theme-31 pb-10 intro-y">
                <div class="2xl:pl-6 grid grid-cols-12 gap-6">
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
//#region Imports
import { ref, defineComponent, watch, onMounted, computed } from "vue";
import { useUserContextStore } from "@/stores/user-context";
import { useI18n } from "vue-i18n";
//#endregion

//#region Declarations
const { t } = useI18n();
//#endregion

//#region Data - Pinia
const userContextStore = useUserContextStore();
const userContext = computed(() => userContextStore.userContext);
//#endregion

//#region Data - UI
const greet = ref('');
//#endregion

//#region onMounted
onMounted(() => {
    greetings();
});
//#endregion

//#region Methods
function greetings() {
    greet.value = 'Welcome, ' + userContext.value.name;
}
//#endregion

//#region Watcher
watch(userContext, () => {
    greetings();
});
//#endregion
</script>
