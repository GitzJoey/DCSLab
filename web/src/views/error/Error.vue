<template>
    <div class="h-screen w-screen flex p-2">
        <div class="flex flex-col md:flex-row text-gray-700">
            <div class="max-w-md">
                <h1 class="text-2xl font-medium text-danger">{{ title }}</h1>
                <p class="font-light leading-normal">
                    {{ description }}
                </p>
                <p class="mb-8">
                    {{ resolution }}
                </p>
                <button class="btn btn-primary w-15" @click="router.push({ name: 'side-menu-dashboard-maindashboard' })">
                    Dashboard
                </button>
            </div>
            <div class="max-w-lg">
        
            </div>
        </div>
    </div>
</template>

<script setup>
//#region Imports
import { onMounted, ref } from "vue";
import { useI18n } from "vue-i18n";
import router from "@/router";
//#endregion

//#region Declarations
const { t } = useI18n();
//#endregion

//#region Data - Pinia
//#endregion

//#region Data - UI
const title = ref('Error');
const description = ref('');
const resolution = ref('');
//#endregion

//#region Data - Views
//#endregion

//#region onMounted
onMounted(() => {
    let code = router.currentRoute.value.params.code;
    console.log(router.currentRoute.value);
    switch (code) {
        case '403':
            title.value = 'Forbidden';
            description.value = 'Insufficient Access Rights';
            break;
        case '500':
            title.value = 'Error';
            description.value = 'Internal Server Error';
            break;
        default:
            break;
    }
});
//#endregion

//#region Methods
//#endregion

//#region Computed
//#endregion

//#region Watcher
//#endregion
</script>