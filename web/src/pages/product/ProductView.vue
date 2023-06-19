<script setup lang="ts">
//#region Import
import { ref, computed } from "vue";
import { onMounted } from "vue";
import { useI18n } from "vue-i18n";
import { TitleLayout } from "../../base-components/Form/FormLayout";
import { ViewMode } from "../../types/enums/ViewMode";
import DataList from "../../base-components/DataList/DataList.vue";
import Table from "../../base-components/Table";
import Lucide from "../../base-components/Lucide";
import ProductService from "../../services/ProductService";
import { useUserContextStore } from '../../stores/user-context'
import ProductSearch from "../../base-components/ProductSearch";
import { Collection } from "lodash";
//#endregion

//#region Declarations
const { t } = useI18n();
const userContextStore = useUserContextStore();
const userLocation = computed(() => userContextStore.selectedUserLocationValue);
const productService = new ProductService();


//#endregion

//#region Data - UI
const mode = ref<ViewMode>(ViewMode.LIST);
const loading = ref<boolean>(false);
const productList = ref({});

//#endregion

//#Method
async function getProductList() {
  try {
    // productList.value = [];
    const companyId = userLocation.value.company.id;
    const data = await productService.readAny(companyId, "");
    productList.value = data?.data?.data;
  } catch (error) {
    throw error;
  }
}

//#region onMounted
onMounted(() => {
  getProductList();
});
//#endregion
</script>

<template>
  <div class="mt-8">
    <LoadingOverlay :visible="loading">
      <div v-if="mode == ViewMode.LIST"></div>
      <TitleLayout>
        <template #title>{{ t("views.product.page_title") }}</template>
      </TitleLayout>

      <ProductSearch :productList="productList" />
    </LoadingOverlay>
    
  </div>
</template>