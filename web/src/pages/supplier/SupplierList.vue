<script setup lang="ts">
// #region Imports
import { onMounted, ref, computed } from "vue";
import { useI18n } from "vue-i18n";
import { useRouter } from "vue-router";
import { storeToRefs } from "pinia";
import DataList from "@/components/DataList";
import Button from "@/components/Base/Button";
import Lucide from "@/components/Base/Lucide";
import Table from "@/components/Base/Table";
import SupplierService from "@/services/SupplierService";
import { useSelectedUserLocationStore } from "@/stores/selected-user-location";
import { Supplier } from "@/types/models/Supplier";
import { Collection } from "@/types/resources/Collection";
import { DataListEmittedData } from "@/components/DataList/DataList.vue";
import { ServiceResponse } from "@/types/services/ServiceResponse";
import { Dialog } from "@/components/Base/Headless";
import { SupplierReadAnyPaginateRequest } from "@/types/services/supplier/SupplierRequest";
import { ViewMode } from "@/types/enums/ViewMode";
import { type AlertPlaceholderProps } from "@/components/AlertPlaceholder/AlertPlaceholder.vue";
import { ErrorCode } from "@/types/enums/ErrorCode";
// #endregion

// #region Declarations
const { t } = useI18n();
const router = useRouter();
const supplierServices = new SupplierService();
const selectedUserLocationStore = useSelectedUserLocationStore();
// #endregion

// #region Props, Emits
const emits = defineEmits([
  "mode-state",
  "loading-state",
  "update-profile",
  "show-alert-placeholder",
  "show-notification",
]);
// #endregion

// #region Refs
const deleteUlid = ref<string>("");
const deleteModalShow = ref<boolean>(false);
const expandDetail = ref<number | null>(null);
const supplierLists = ref<Collection<Array<Supplier>> | null>({
  data: [],
  meta: {
    current_page: 0,
    from: null,
    last_page: 0,
    path: "",
    per_page: 0,
    to: null,
    total: 0,
  },
  links: {
    first: "",
    last: "",
    prev: null,
    next: null,
  },
});
// #endregion

// #region Computed
const isUserLocationSelected = computed(
  () => selectedUserLocationStore.isUserLocationSelected,
);
const selectedUserLocation = computed(
  () => selectedUserLocationStore.selectedUserLocation,
);
// #endregion

// #region Methods
const getSuppliers = async (
  search: string,

  refresh: boolean,
  page: number,
  per_page: number,
) => {
  emits("loading-state", true);

  const requestParams: SupplierReadAnyPaginateRequest = {
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    search: search,
    refresh: refresh,
    page: page,
    per_page: per_page,
  };

  let result: ServiceResponse<Collection<Array<Supplier>> | null> =
    await supplierServices.readAnyPaginate(requestParams);

  if (result.success && result.data) {
    supplierLists.value = result.data;
    emits("show-alert-placeholder", {
      alertType: "hidden",
      title: "",
      alertList: null,
    });
  } else {
    emits("show-alert-placeholder", {
      alertType: "danger",
      title: "",
      alertList: result.errors,
    });
  }

  emits("loading-state", false);
};

const handleDataListChange = async (data: DataListEmittedData) => {
  await getSuppliers(
    data.search.text,
    false,
    data.pagination.page,
    data.pagination.per_page,
  );
};

const viewSelected = (idx: number) => {
  if (expandDetail.value === idx) {
    expandDetail.value = null;
  } else {
    expandDetail.value = idx;
  }
};

const editSelected = (idx: number) => {
  if (!supplierLists.value) return;
  const ulid = supplierLists.value.data[idx].ulid;
  router.push({
    name: "side-menu-supplier-supplier-edit",
    params: { ulid: ulid },
  });
};

const deleteSelected = (idx: number) => {
  if (!supplierLists.value) return;
  deleteUlid.value = supplierLists.value.data[idx].ulid;
  deleteModalShow.value = true;
};

const confirmDelete = async () => {
  deleteModalShow.value = false;
  emits("loading-state", true);

  const result: ServiceResponse<boolean | null> = await supplierServices.delete(
    deleteUlid.value,
  );

  if (result.success) {
    emits("update-profile");
    await getSuppliers("", true, 1, 10);
    emits("show-notification", {
      title: t("views.supplier.alert.delete.title"),
      content: t("views.supplier.alert.delete.message"),
    });
  } else {
    emits("show-alert-placeholder", {
      alertType: "danger",
      title: "",
      alertList: result.errors,
    });
  }

  emits("loading-state", false);
};
// #endregion

// #region Lifecycle Hooks
onMounted(async () => {
  emits("mode-state", ViewMode.LIST);

  if (!isUserLocationSelected.value) {
    router.push({
      name: "side-menu-error-code",
      params: { code: ErrorCode.USERLOCATION_REQUIRED },
    });
    return;
  }

  await getSuppliers("", true, 1, 10);
});
// #endregion

// #region Watchers
// #endregion
</script>

<template>
  <DataList
    :title="t('views.supplier.table.title')"
    :data="supplierLists"
    :enable-search="true"
    :can-print="true"
    :can-export="true"
    :pagination="supplierLists ? supplierLists.meta : null"
    @dataListChanged="handleDataListChange"
  >
    <template #content>
      <Table class="mt-5" :hover="true">
        <Table.Thead variant="light">
          <Table.Tr>
            <Table.Th class="whitespace-nowrap">
              {{ t("views.supplier.table.cols.code") }}
            </Table.Th>
            <Table.Th class="whitespace-nowrap">
              {{ t("views.supplier.table.cols.name") }}
            </Table.Th>
            <Table.Th class="whitespace-nowrap">
              {{ t("views.supplier.table.cols.status") }}
            </Table.Th>
            <Table.Th class="whitespace-nowrap"> </Table.Th>
          </Table.Tr>
        </Table.Thead>
        <Table.Tbody v-if="supplierLists !== null">
          <template v-if="supplierLists.data.length === 0">
            <Table.Tr>
              <Table.Td colspan="4" class="text-center italic">
                {{ t("components.data-list.data_not_found") }}
              </Table.Td>
            </Table.Tr>
          </template>
          <template
            v-for="(item, itemIdx) in supplierLists.data"
            :key="item.ulid"
          >
            <Table.Tr class="intro-x">
              <Table.Td>{{ item.code }}</Table.Td>
              <Table.Td>{{ item.name }}</Table.Td>
              <Table.Td>
                <Lucide v-if="item.status === 'ACTIVE'" icon="CheckCircle" />
                <Lucide v-if="item.status === 'INACTIVE'" icon="X" />
              </Table.Td>
              <Table.Td>
                <div class="flex justify-end gap-1">
                  <Button
                    variant="outline-secondary"
                    @click="viewSelected(itemIdx)"
                  >
                    <Lucide icon="Info" class="w-4 h-4" />
                  </Button>
                  <Button
                    variant="outline-secondary"
                    @click="editSelected(itemIdx)"
                  >
                    <Lucide icon="Pen" class="w-4 h-4" />
                  </Button>
                  <Button
                    variant="outline-secondary"
                    @click="deleteSelected(itemIdx)"
                  >
                    <Lucide icon="Trash2" class="w-4 h-4 text-danger" />
                  </Button>
                </div>
              </Table.Td>
            </Table.Tr>
            <Table.Tr
              :class="{
                'intro-x': true,
                'hidden transition-all': expandDetail !== itemIdx,
              }"
            >
              <Table.Td colspan="4">
                <div class="flex flex-row">
                  <div class="ml-5 w-48 text-right pr-5">
                    {{ t("views.supplier.fields.code") }}
                  </div>
                  <div class="flex-1">{{ item.code }}</div>
                </div>
                <div class="flex flex-row">
                  <div class="ml-5 w-48 text-right pr-5">
                    {{ t("views.supplier.fields.name") }}
                  </div>
                  <div class="flex-1">{{ item.name }}</div>
                </div>
                <div class="flex flex-row">
                  <div class="ml-5 w-48 text-right pr-5">
                    {{ t("views.supplier.fields.address") }}
                  </div>
                  <div class="flex-1">{{ item.address }}</div>
                </div>
                <div class="flex flex-row">
                  <div class="ml-5 w-48 text-right pr-5">
                    {{ t("views.supplier.fields.tax_id") }}
                  </div>
                  <div class="flex-1">{{ item.tax_id }}</div>
                </div>
                <div class="flex flex-row">
                  <div class="ml-5 w-48 text-right pr-5">
                    {{ t("views.supplier.fields.status") }}
                  </div>
                  <div class="flex-1">
                    <span v-if="item.status === 'ACTIVE'">
                      {{ t("components.dropdown.values.statusDDL.active") }}
                    </span>
                    <span v-if="item.status === 'INACTIVE'">
                      {{ t("components.dropdown.values.statusDDL.inactive") }}
                    </span>
                  </div>
                </div>
                <div class="flex flex-row">
                  <div class="ml-5 w-48 text-right pr-5">
                    {{ t("views.supplier.fields.remarks") }}
                  </div>
                  <div class="flex-1">{{ item.remarks }}</div>
                </div>
              </Table.Td>
            </Table.Tr>
          </template>
        </Table.Tbody>
      </Table>
      <Dialog
        :open="deleteModalShow"
        @close="
          () => {
            deleteModalShow = false;
          }
        "
      >
        <Dialog.Panel>
          <div class="p-5 text-center">
            <Lucide icon="XCircle" class="w-16 h-16 mx-auto mt-3 text-danger" />
            <div class="mt-5 text-3xl">
              {{ t("components.delete-modal.title") }}
            </div>
            <div class="mt-2 text-slate-500">
              {{ t("components.delete-modal.desc_1") }}
              <br />
              {{ t("components.delete-modal.desc_2") }}
            </div>
          </div>
          <div class="px-5 pb-8 text-center">
            <Button
              type="button"
              variant="outline-secondary"
              class="w-24 mr-1"
              @click="
                () => {
                  deleteModalShow = false;
                }
              "
            >
              {{ t("components.buttons.cancel") }}
            </Button>
            <Button
              type="button"
              variant="danger"
              class="w-24"
              @click="confirmDelete"
            >
              {{ t("components.buttons.delete") }}
            </Button>
          </div>
        </Dialog.Panel>
      </Dialog>
    </template>
  </DataList>
</template>
