<script setup lang="ts">
// #region Imports
import { computed, onMounted, ref } from "vue";
import DataList from "@/components/DataList";
import { useI18n } from "vue-i18n";
import Button from "@/components/Base/Button";
import Lucide from "@/components/Base/Lucide";
import Table from "@/components/Base/Table";
import CustomerService from "@/services/CustomerService";
import { Customer } from "@/types/models/Customer";
import { Collection } from "@/types/resources/Collection";
import { DataListEmittedData } from "@/components/DataList/DataList.vue";
import { ServiceResponse } from "@/types/services/ServiceResponse";
import { CustomerReadAnyPaginateRequest } from "@/types/services/customer/CustomerRequest";
import { useRouter } from "vue-router";
import { Dialog } from "@/components/Base/Headless";
import { ViewMode } from "@/types/enums/ViewMode";
import { NotificationData } from "@/types/models/NotificationData";
import { type AlertPlaceholderProps } from "@/components/AlertPlaceholder/AlertPlaceholder.vue";
import { useSelectedUserLocationStore } from "@/stores/selected-user-location";
import { ErrorCode } from "@/types/enums/ErrorCode";
// #endregion

// #region Interfaces
// #endregion

// #region Declarations
const { t } = useI18n();
const router = useRouter();
const customerService = new CustomerService();
const selectedUserLocationStore = useSelectedUserLocationStore();
// #endregion

// #region Props, Emits
const emits = defineEmits([
  "mode-state",
  "loading-state",
  "update-profile",
  "show-alertplaceholder",
  "show-notification",
]);
// #endregion

// #region Refs
const deleteUlid = ref<string>("");
const deleteModalShow = ref<boolean>(false);
const expandDetail = ref<number | null>(null);
const customerLists = ref<Collection<Array<Customer>> | null>({
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
  () => selectedUserLocationStore.isUserLocationSelected
);
const selectedUserLocation = computed(
  () => selectedUserLocationStore.selectedUserLocation
);
// #endregion

// #region Lifecycle Hooks
onMounted(async () => {
  emits("mode-state", ViewMode.LIST);

  if (!isUserLocationSelected.value) {
    router.push({
      name: "side-menu-error-code",
      params: { code: ErrorCode.USERLOCATION_REQUIRED },
    });
  }

  await getCustomers("", true, 1, 10);
});
// #endregion

// #region Methods
const getCustomers = async (
  search: string,
  refresh: boolean,
  page: number,
  per_page: number
) => {
  emits("loading-state", true);

  const company_id = selectedUserLocation.value.company.id;

  const searchReq: CustomerReadAnyPaginateRequest = {
    with_trashed: false,
    company_id: company_id,
    search: search,
    status: null,
    include_id: undefined,
    refresh: refresh,
    page: page,
    per_page: per_page,
  };

  const result: ServiceResponse<Collection<Array<Customer>> | null> = await customerService.readAnyPaginate(searchReq);

  if (result.success && result.data) {
    customerLists.value = result.data;
  } else {
    showAlertPlaceholder(
      "danger",
      "",
      result.errors as Record<string, Array<string>>
    );
  }

  emits("loading-state", false);
};

const onDataListChanged = async (data: DataListEmittedData) => {
    await getCustomers(
      data.search.text,
      false,
      data.pagination.page,
      data.pagination.per_page
    );
};

const viewSelected = (idx: number) => {
  if (expandDetail.value === idx) {
    expandDetail.value = null;
  } else {
    expandDetail.value = idx;
  }
};

const editSelected = (itemIdx: number) => {
  if (!customerLists.value) return;

  let ulid = customerLists.value.data[itemIdx].ulid;

  router.push({
    name: "side-menu-customer-edit",
    params: { ulid: ulid },
  }); // Changed route name
};

const deleteSelected = (itemIdx: number) => {
  if (!customerLists.value) return;

  let itemUlid = customerLists.value.data[itemIdx].ulid;

  deleteUlid.value = itemUlid;
  deleteModalShow.value = true;
};

const confirmDelete = async () => {
  deleteModalShow.value = false;
  emits("loading-state", true);

  let result: ServiceResponse<boolean | null> = await customerService.delete(
    deleteUlid.value
  ); // Changed to customerService

  if (result.success) {
    emits("update-profile");
    await getCustomers("", true, 1, 10);
    showNotification(
      t("views.customer.alert.delete.title"),
      t("views.customer.alert.delete.message")
    ); // Changed translation path
  } else {
    showAlertPlaceholder(
      "danger",
      "",
      result.errors as Record<string, Array<string>>
    );
  }

  emits("loading-state", false);
};

const showNotification = (pTitle: string, pContent: string) => {
  let n: NotificationData = {
    title: pTitle,
    content: pContent,
  };

  emits("show-notification", n);
};

const showAlertPlaceholder = (
  pAlertType: "hidden" | "danger" | "success" | "warning" | "pending" | "dark",
  pTitle: string,
  pAlertList: Record<string, Array<string>> | null
) => {
  let ap: AlertPlaceholderProps = {
    alertType: pAlertType,
    title: pTitle,
    alertList: pAlertList,
  };

  emits("show-alertplaceholder", ap);
};
// #endregion

// #region Watchers
// #endregion
</script>

<template>
  <DataList
    :title="t('views.customer.table.title')"
    :enable-search="true"
    :can-print="true"
    :can-export="true"
    :pagination="customerLists ? customerLists.meta : null"
    @dataListChanged="onDataListChanged"
  >
    <template #content>
      <Table class="mt-5" :hover="true">
        <Table.Thead variant="light">
          <Table.Tr>
            <Table.Th class="whitespace-nowrap">
              {{ t("views.customer.table.cols.code") }}
            </Table.Th>
            <Table.Th class="whitespace-nowrap">
              {{ t("views.customer.table.cols.name") }}
            </Table.Th>
            <Table.Th class="whitespace-nowrap">
              {{ t("views.customer.table.cols.group") }}
            </Table.Th>
            <Table.Th class="whitespace-nowrap">
              {{ t("views.customer.table.cols.zone") }}
            </Table.Th>
            <Table.Th class="whitespace-nowrap"></Table.Th>
          </Table.Tr>
        </Table.Thead>
        <Table.Tbody v-if="customerLists !== null">
          <template v-if="customerLists.data.length == 0">
            <Table.Tr class="intro-x">
              <Table.Td colspan="5">
                <div class="flex justify-center italic">
                  {{ t("components.data-list.data_not_found") }}
                </div>
              </Table.Td>
            </Table.Tr>
          </template>
          <template
            v-for="(item, itemIdx) in customerLists.data"
            :key="item.ulid"
          >
            <Table.Tr class="intro-x">
              <Table.Td>{{ item.code }}</Table.Td>
              <Table.Td>{{ item.name }}</Table.Td>
              <Table.Td>{{ item.group ? item.group.name : "-" }}</Table.Td>
              <Table.Td>{{ item.zone || "-" }}</Table.Td>
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
              <Table.Td colspan="5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-y-1 gap-x-4">
                  <div class="flex flex-row mt-1">
                    <div class="w-48 text-left md:text-right pr-5 font-medium">
                      {{ t("views.customer.fields.code") }}
                    </div>
                    <div class="flex-1">{{ item.code }}</div>
                  </div>
                  <div class="flex flex-row mt-1">
                    <div class="w-48 text-left md:text-right pr-5 font-medium">
                      {{ t("views.customer.fields.name") }}
                    </div>
                    <div class="flex-1">{{ item.name }}</div>
                  </div>
                  <div class="flex flex-row mt-1">
                    <div class="w-48 text-left md:text-right pr-5 font-medium">
                      {{ t("views.customer.fields.group") }}
                    </div>
                    <div class="flex-1">
                      {{ item.group ? item.group.name : "-" }}
                    </div>
                  </div>
                  <div class="flex flex-row mt-1">
                    <div class="w-48 text-left md:text-right pr-5 font-medium">
                      {{ t("views.customer.fields.zone") }}
                    </div>
                    <div class="flex-1">{{ item.zone || "-" }}</div>
                  </div>
                  <div class="flex flex-row mt-1">
                    <div class="w-48 text-left md:text-right pr-5 font-medium">
                      {{ t("views.customer.fields.zone") }}
                    </div>
                    <div class="flex-1">{{ item.zone || "-" }}</div>
                  </div>
                  <div class="flex flex-row mt-1">
                    <div class="w-48 text-left md:text-right pr-5 font-medium">
                      {{ t("views.customer.fields.zone") }}
                    </div>
                    <div class="flex-1">{{ item.zone || "-" }}</div>
                  </div>
                  <div class="flex flex-row mt-1">
                    <div class="w-48 text-left md:text-right pr-5 font-medium">
                      {{ t("views.customer.fields.taxable_enterprise") }}
                    </div>
                    <div class="flex-1">
                      <span v-if="item.taxable_enterprise">{{
                        t("components.dropdown.values.switch.on")
                      }}</span>
                      <span v-else>{{
                        t("components.dropdown.values.switch.off")
                      }}</span>
                    </div>
                  </div>
                  <div class="flex flex-row mt-1">
                    <div class="w-48 text-left md:text-right pr-5 font-medium">
                      {{ t("views.customer.fields.tax_id") }}
                    </div>
                    <div class="flex-1">{{ item.tax_id || "-" }}</div>
                  </div>
                  <div class="flex flex-row mt-1 md:col-span-2">
                    <div class="w-48 text-left md:text-right pr-5 font-medium">
                      {{ t("views.customer.fields.remarks") }}
                    </div>
                    <div class="flex-1">{{ item.remarks || "-" }}</div>
                  </div>
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
