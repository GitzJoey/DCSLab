<script setup lang="ts">
//#region Imports
import { onMounted, ref } from "vue";
import router from "vue-router";
import { useI18n } from "vue-i18n";
//#endregion

//#region Declarations
const { t } = useI18n();
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

});
//#endregion

//#region Computed
//#endregion

//#region Watcher
//#endregion
</script>

<template>
  <div class="flex items-center mt-8 intro-y">
    <h2 class="mr-auto text-lg font-medium">{{ t('views.profile.title') }}</h2>
  </div>
  <div class="grid grid-cols-12 gap-6 mt-5">
    
  </div>
</template>