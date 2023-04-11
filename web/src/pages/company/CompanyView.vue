<script setup lang="ts">
import { onMounted } from "vue";
import { useI18n } from "vue-i18n";

//#region Declarations
const { t } = useI18n();
//#endregion

//#region onMounted
onMounted(() => {});
//#endregion
</script>

<template>
  <div class="flex items-center mt-8 intro-y">
    <h2 class="mr-auto text-xl font-medium">
      {{ t("views.company.page_title") }}
    </h2>
  </div>
  <div class="grid grid-cols-12 gap-6 mt-5">
    <div class="col-span-12 intro-y p-5 box"></div>
  </div>
</template>
