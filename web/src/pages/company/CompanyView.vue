<script setup lang="ts">
//#region Import
import { ref, reactive } from "vue";
import { onMounted } from "vue";
import { useI18n } from "vue-i18n";
import { ViewTitleLayout } from "../../base-components/FormLayout";
import { ViewMode } from "../../types/enums/ViewMode";
import DataList from "../../base-components/DataList/DataList.vue";
import Table from "../../base-components/Table";
import { CompanyType } from "../../types/resources/CompanyType";
import { ServiceResponseType } from "../../types/ServiceResponseType";
import CompanyService from "../../services/CompanyService";
import Lucide from "../../base-components/Lucide";


//#endregion

//#region Declarations
const { t } = useI18n();
const companyService = new CompanyService();
//#endregion

//#region Data - UI
const mode = ref<ViewMode>(ViewMode.LIST);
const loading = ref<boolean>(false);
const companyList = ref({});
const expandDetail = ref(null);

//#endregion

//#region onMounted
onMounted(() => {
  getCompanyList();

});
//#endregion

// # Region method
async function getCompanyList() {
  try {
    companyList.value = {};
    let data = await companyService.read();
    companyList.value = data;
  } catch (e) {
    throw e;
  }
}

const toggleDetail = (idx: any) => {
  if (expandDetail.value === idx) {
    expandDetail.value = null;
  } else {
    expandDetail.value = idx;
  }
};
// # End Region method
</script>

<template>
  <div class="mt-8">
    <LoadingOverlay :visible="loading">
      <div v-if="mode == ViewMode.LIST"></div>
      <ViewTitleLayout>
        <template #title>
          {{ t("views.company.page_title") }}
        </template>
      </ViewTitleLayout>
      <DataList :data="companyList"  :title="t('views.company.table.title')" >
        <template #table="tableProps">
          <Table class="mt-5" :hover="true">
            <Table.Thead variant="light">
              <Table.Tr>
                <Table.Th class="whitespace-nowrap">
                  {{ t("views.company.table.cols.code") }}
                </Table.Th>
                <Table.Th class="whitespace-nowrap">
                  {{ t("views.company.table.cols.name") }}
                </Table.Th>
                <Table.Th class="whitespace-nowrap">
                  {{ t("views.company.table.cols.default") }}
                </Table.Th>
                <Table.Th class="whitespace-nowrap">
                  {{ t("views.company.table.cols.status") }}
                </Table.Th>
              </Table.Tr>
            </Table.Thead>

            <Table.Tbody>
              <template
                v-if="tableProps.dataList !== undefined"
                v-for="(item, index) in tableProps.dataList.data"
              >
                <tr
                  :class="{
                    'intro-x': true,
                    'line-through': item.status === 'DELETED',
                  }"
                >
                  <td>
                    {{ item.code }}
                  </td>
                  <td>
                    <a
                      href=""
                      @click.prevent="toggleDetail(index)"
                      class="hover:animate-pulse"
                    >
                      {{ item.name }}
                    </a>
                  </td>

                  <td>
                    <Lucide icon="CheckCircleIcon"  v-if="item.default" />
                    <Lucide icon="XIcon" v-else />
                  </td>

                  <td>
                    <Lucide icon="CheckCircleIcon" v-if="item.status === 'ACTIVE'" />
                  </td>
                </tr>
              </template>
            </Table.Tbody>
          </Table>
        </template>
      </DataList>
    </LoadingOverlay>
  </div>
</template>
