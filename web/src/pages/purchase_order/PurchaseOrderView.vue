<script setup lang="ts">
//#region Import
import { ref } from "vue";
import { onMounted } from "vue";
import { useI18n } from "vue-i18n";
import { ViewTitleLayout } from "../../base-components/FormLayout";
import { ViewMode } from "../../types/enums/ViewMode";
import DataList from "../../base-components/DataList/DataList.vue";
import Table from "../../base-components/Table";
import Lucide from "../../base-components/Lucide";
//#endregion

//#region Declarations
const { t } = useI18n();
//#endregion

//#region Data - UI
const mode = ref<ViewMode>(ViewMode.LIST);
const loading = ref<boolean>(false);
//#endregion

//#region onMounted
onMounted(() => {

});
//#endregion
</script>

<template>
  <div class="mt-8">
    <LoadingOverlay :visible="loading">
      <div v-if="mode == ViewMode.LIST"></div>
      <ViewTitleLayout>
        <template #title>{{ t("views.purchase_order.page_title") }}</template>
      </ViewTitleLayout>
    </LoadingOverlay>
    
  </div>
</template>