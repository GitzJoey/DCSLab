<script setup lang="ts">
//#region Import
import { ref } from "vue";
import { onMounted } from "vue";
import { useI18n } from "vue-i18n";
import { ViewTitleLayout } from "../../base-components/FormLayout";
import { ViewMode } from "../../types/enums/ViewMode";
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
  console.log("a");
});
//#endregion
</script>

<template>
  <div class="mt-8">
    <LoadingOverlay :visible="loading">
      <div v-if="mode == ViewMode.LIST"></div>
      <ViewTitleLayout>
        <template #title>{{ t("views.company.page_title") }}</template>
        <template #optional>
          <div class="flex w-full mt-4 sm:w-auto sm:mt-0">
            <Button as="a" href="#" variant="primary" class="shadow-md">
              <Lucide icon="Plus" class="w-4 h-4" />&nbsp;{{
                t("components.buttons.create_new")
              }}
            </Button>
          </div>
        </template>
      </ViewTitleLayout>
    </LoadingOverlay>
  </div>
</template>
