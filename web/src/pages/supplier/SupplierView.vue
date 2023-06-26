<script setup lang="ts">
//#region Import
import { ref } from "vue";
import { onMounted } from "vue";
import { useI18n } from "vue-i18n";
import { TitleLayout } from "../../base-components/Form/FormLayout";
import { ViewMode } from "../../types/enums/ViewMode";

import FormSupplier from '../../base-components/Form/FormSupplier'
//#endregion

//#region Declarations
const { t } = useI18n();
//#endregion

// Define Type
// End Define Type

//#region Data - UI
const mode = ref<ViewMode>(ViewMode.LIST);
const loading = ref<boolean>(false);

//#endregion

// Start Method
// End Method

//#region onMounted
onMounted(() => {
});
//#endregion
</script>

<template>
  <div class="mt-8">
    <LoadingOverlay :visible="loading">
      <div v-if="mode == ViewMode.LIST"></div>
      <TitleLayout>
        <template #title>{{ t("views.supplier.page_title") }}</template>
      </TitleLayout>

      <FormSupplier />

    </LoadingOverlay>
  </div>
</template>
