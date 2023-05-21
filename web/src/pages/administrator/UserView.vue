<script setup lang="ts">
//#region Imports
import { onMounted, onUnmounted, ref, computed, watch } from "vue";
import AlertPlaceholder from "../../base-components/AlertPlaceholder";
import DataList from "../../base-components/DataList";
import LoadingOverlay from "../../base-components/LoadingOverlay";
import { useI18n } from "vue-i18n";
import Button from "../../base-components/Button";
import Lucide from "../../base-components/Lucide";
import Table from "../../base-components/Table";
import {
  ViewTitleLayout,
  ThreeColsLayout,
} from "../../base-components/FormLayout";
import { ViewMode } from "../../types/enums/ViewMode";
import UserService from "../../services/UserService";
//#endregion

//#region Declarations
const { t } = useI18n();
const userServices = new UserService()
//#endregion

//#region Data - Pinia
//#endregion

//#region Data - UI
const mode = ref<ViewMode>(ViewMode.LIST);
const loading = ref<boolean>(false);
const alertErrors = ref([]);
const deleteId = ref<string>("");
const deleteModalShow = ref<boolean>(false);
const expandDetail = ref<number | null>(null);
//#endregion

//#region Data - Views
const user = ref({
  roles: [],
  selected_roles: [],
  profile: {
    country: "",
    status: "ACTIVE",
    img_path: "",
  },
  selected_settings: {
    theme: "side-menu-light-full",
    date_Format: "",
    time_format: "",
  },
});
const userList = ref({});
const rolesDDL = ref([]);
const statusDDL = ref([]);
const countriesDDL = ref([]);
//#endregion

//#region onMounted
onMounted(() => {
  getUser()
});

onUnmounted(() => {});
//#endregion

//#region Computed
//#endregion

//#region Methods
const toggleDetail = (idx: number) => {
  if (expandDetail.value === idx) {
    expandDetail.value = null;
  } else {
    expandDetail.value = idx;
  }
};

async function getUser() {
  try {
    // let data = await userServices.readAny()
    // console.log(data)
  } catch (error) {
    throw error
  }
}
//#endregion

//#region Watcher
//#endregion
</script>

<template>
  <div class="mt-8">
    <LoadingOverlay :visible="loading">
      <div v-if="mode == ViewMode.LIST">
        <ViewTitleLayout>
          <template #title>{{ t("views.user.page_title") }}</template>
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

        <AlertPlaceholder />
        <DataList>
          <template #table="tableProps">
            <Table class="mt-5" :hover="true">
              <Table.Thead variant="light">
                <Table.Tr>
                  <Table.Th class="whitespace-nowrap">{{
                    t("views.user.table.cols.name")
                  }}</Table.Th>
                  <Table.Th class="whitespace-nowrap">{{
                    t("views.user.table.cols.email")
                  }}</Table.Th>
                  <Table.Th class="whitespace-nowrap">{{
                    t("views.user.table.cols.roles")
                  }}</Table.Th>
                  <Table.Th class="whitespace-nowrap">{{
                    t("views.user.table.cols.status")
                  }}</Table.Th>
                  <Table.Th class="whitespace-nowrap"></Table.Th>
                </Table.Tr>
              </Table.Thead>
              <Table.Tbody v-if="tableProps?.dataList !== undefined">
                <template
                  v-for="(item, itemIdx) in tableProps?.dataList?.data"
                  :key="item.ulid"
                >
                  <Table.Tr class="intro-x">
                    <Table.Td>{{ item.name }}</Table.Td>
                    <Table.Td
                      ><a
                        href=""
                        class="hover:animate-pulse"
                        @click.prevent="toggleDetail(itemIdx)"
                        >{{ item.email }}</a
                      ></Table.Td
                    >
                    <Table.Td>
                      <CheckCircleIcon
                        v-if="item.profile.status === 'ACTIVE'"
                      />
                      <XIcon v-if="item.profile.status === 'INACTIVE'" />
                    </Table.Td>
                    <Table.Td>@angelinajolie</Table.Td>
                  </Table.Tr>
                  <Table.Tr
                    :class="{
                      'intro-x': true,
                      'hidden transition-all': expandDetail !== itemIdx,
                    }"
                  >
                    <Table.Td>2</Table.Td>
                    <Table.Td>Brad</Table.Td>
                    <Table.Td>Pitt</Table.Td>
                    <Table.Td>@bradpitt</Table.Td>
                  </Table.Tr>
                </template>
              </Table.Tbody>
            </Table>
          </template>
        </DataList>
      </div>
      <div v-else></div>
    </LoadingOverlay>
  </div>
</template>
