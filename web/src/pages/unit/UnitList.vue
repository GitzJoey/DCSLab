<script setup lang="ts">
// #region Imports
import { onMounted, ref, computed, ErrorCodes } from "vue";
import AlertPlaceholder from "../../base-components/AlertPlaceholder";
import DataList from "../../base-components/DataList";
import { useI18n } from "vue-i18n";
import Button from "../../base-components/Button";
import Lucide from "../../base-components/Lucide";
import Table from "../../base-components/Table";
import UnitService from "../../services/UnitService";
import { Unit } from "../../types/models/Unit";
import { Collection } from "../../types/resources/Collection";
import { DataListEmittedData } from "../../base-components/DataList/DataList.vue";
import { ServiceResponse } from "../../types/services/ServiceResponse";
import { Resource } from "../../types/resources/Resource";
import { ReadAnyRequest } from "../../types/services/ServiceRequest";
import { useRouter } from "vue-router";
import { Dialog } from "../../base-components/Headless";
import { useSelectedUserLocationStore } from "../../stores/user-location";
import { ErrorCode } from "../../types/enums/ErrorCode";
import { ViewMode } from "../../types/enums/ViewMode";
// #endregion

// #region Interfaces
// #endregion

// #region Declarations
const { t } = useI18n();
const router = useRouter();
const unitServices = new UnitService();
const selectedUserLocationStore = useSelectedUserLocationStore();
// #endregion

// #region Props, Emits
const emits = defineEmits(['mode-state', 'loading-state']);
// #endregion

// #region Refs
const datalistErrors = ref<Record<string, Array<string>> | null>(null);
const deleteUlid = ref<string>('');
const deleteModalShow = ref<boolean>(false);
const expandDetail = ref<number | null>(null);
const unitLists = ref<Collection<Array<Unit>> | null>({
  data: [],
  meta: {
    current_page: 0,
    from: null,
    last_page: 0,
    path: '',
    per_page: 0,
    to: null,
    total: 0,
  },
  links: {
    first: '',
    last: '',
    prev: null,
    next: null,
  }
});
// #endregion

// #region Computed
const isUserLocationSelected = computed(() => selectedUserLocationStore.isUserLocationSelected);
const selectedUserLocation = computed(() => selectedUserLocationStore.selectedUserLocation);
// #endregion

// #region Lifecycle Hooks
onMounted(async () => {
  emits('mode-state', ViewMode.LIST);

  if (!isUserLocationSelected.value) {
    router.push({ name: 'side-menu-error-code', params: { code: ErrorCode.USERLOCATION_REQUIRED } });
  }

  await getUnits('', true, true, 1, 10);
});
// #endregion

// #region Methods
const getUnits = async (search: string, refresh: boolean, paginate: boolean, page: number, per_page: number) => {
  emits('loading-state', true);

  let company_id = selectedUserLocation.value.company.id;

  const searchReq: ReadAnyRequest = {
    company_id: company_id,
    search: search,
    refresh: refresh,
    paginate: paginate,
    page: page,
    per_page: per_page
  };

  let result: ServiceResponse<Collection<Array<Unit>> | Resource<Array<Unit>> | null> = await unitServices.readAny(searchReq);

  if (result.success && result.data) {
    unitLists.value = result.data as Collection<Array<Unit>>;
  } else {
    datalistErrors.value = result.errors as Record<string, Array<string>>;
  }

  emits('loading-state', false);
}

const onDataListChanged = async (data: DataListEmittedData) => {
  await getUnits(data.search.text, false, true, data.pagination.page, data.pagination.per_page);
}

const viewSelected = (idx: number) => {
  if (expandDetail.value === idx) {
    expandDetail.value = null;
  } else {
    expandDetail.value = idx;
  }
};

const editSelected = (itemIdx: number) => {
  if (!unitLists.value) return;

  let ulid = unitLists.value.data[itemIdx].ulid;
  router.push({ name: 'side-menu-product-unit-edit', params: { ulid: ulid } });
}

const deleteSelected = (itemIdx: number) => {
  if (!unitLists.value) return;

  let itemUlid = unitLists.value.data[itemIdx].ulid;

  deleteUlid.value = itemUlid;
  deleteModalShow.value = true;
}

const confirmDelete = async () => {
  deleteModalShow.value = false;
  emits('loading-state', true);

  let result: ServiceResponse<boolean | null> = await unitServices.delete(deleteUlid.value);

  if (result.success) {
    await getUnits('', true, true, 1, 10);
  }

  emits('loading-state', false);
}
// #endregion

// #region Watchers
// #endregion
</script>

<template>
  <AlertPlaceholder :errors="datalistErrors" />
  <DataList :title="t('views.company.table.title')" :enable-search="true" :can-print="true" :can-export="true"
    :pagination="unitLists ? unitLists.meta : null" @dataListChanged="onDataListChanged">
    <template #content>
      <Table class="mt-5" :hover="true">
        <Table.Thead variant="light">
          <Table.Tr>
            <Table.Th class="whitespace-nowrap">
              {{ t("views.unit.table.cols.code") }}
            </Table.Th>
            <Table.Th class="whitespace-nowrap">
              {{ t("views.unit.table.cols.name") }}
            </Table.Th>
            <Table.Th class="whitespace-nowrap">
              {{ t("views.unit.table.cols.description") }}
            </Table.Th>
            <Table.Th class="whitespace-nowrap">
              {{ t("views.unit.table.cols.category") }}
            </Table.Th>
            <Table.Th class="whitespace-nowrap"></Table.Th>
          </Table.Tr>
        </Table.Thead>
        <Table.Tbody v-if="unitLists !== null">
          <template v-if="unitLists.data.length == 0">
            <Table.Tr class="intro-x">
              <Table.Td colspan="5">
                <div class="flex justify-center italic">{{
                  t('components.data-list.data_not_found') }}</div>
              </Table.Td>
            </Table.Tr>
          </template>
          <template v-for="( item, itemIdx ) in unitLists.data" :key="item.ulid">
            <Table.Tr class="intro-x">
              <Table.Td>{{ item.code }}</Table.Td>
              <Table.Td>{{ item.name }}</Table.Td>
              <Table.Td>{{ item.description }}</Table.Td>
              <Table.Td>
                <span v-if="item.category === 'PRODUCTS'">{{ t('components.dropdown.values.productGroupCategoryDDL.product') }}</span>
                <span v-if="item.category === 'SERVICES'">{{ t('components.dropdown.values.productGroupCategoryDDL.service') }}</span>
              </Table.Td>
              <Table.Td>
                <div class="flex justify-end gap-1">
                  <Button variant="outline-secondary" @click="viewSelected(itemIdx)">
                    <Lucide icon="Info" class="w-4 h-4" />
                  </Button>
                  <Button variant="outline-secondary" @click="editSelected(itemIdx)">
                    <Lucide icon="CheckSquare" class="w-4 h-4" />
                  </Button>
                  <Button variant="outline-secondary" @click="deleteSelected(itemIdx)">
                    <Lucide icon="Trash2" class="w-4 h-4 text-danger" />
                  </Button>
                </div>
              </Table.Td>
            </Table.Tr>
            <Table.Tr :class="{ 'intro-x': true, 'hidden transition-all': expandDetail !== itemIdx }">
              <Table.Td colspan="5">
                <div class="flex flex-row">
                  <div class="ml-5 w-48 text-right pr-5">{{ t('views.unit.fields.code') }}</div>
                  <div class="flex-1">{{ item.code }}</div>
                </div>
                <div class="flex flex-row">
                  <div class="ml-5 w-48 text-right pr-5">{{ t('views.unit.fields.name') }}</div>
                  <div class="flex-1">{{ item.name }}</div>
                </div>
                <div class="flex flex-row">
                    <div class="ml-5 w-48 text-right pr-5">{{ t('views.unit.fields.category') }}</div>
                    <div class="flex-1">
                      <span v-if="item.category === 'PRODUCTS'">
                        {{ t('components.dropdown.values.unitCategoryDDL.product') }}
                      </span>
                      <span v-if="item.category === 'SERVICES'">
                        {{ t('components.dropdown.values.unitCategoryDDL.service') }}
                      </span>
                    </div>
                </div>
              </Table.Td>
            </Table.Tr>
          </template>
        </Table.Tbody>
      </Table>
      <Dialog :open="deleteModalShow" @close="() => { deleteModalShow = false; }">
        <Dialog.Panel>
          <div class="p-5 text-center">
            <Lucide icon="XCircle" class="w-16 h-16 mx-auto mt-3 text-danger" />
            <div class="mt-5 text-3xl">{{ t('components.delete-modal.title') }}</div>
            <div class="mt-2 text-slate-500">
              {{ t('components.delete-modal.desc_1') }}
              <br />
              {{ t('components.delete-modal.desc_2') }}
            </div>
          </div>
          <div class="px-5 pb-8 text-center">
            <Button type="button" variant="outline-secondary" class="w-24 mr-1"
              @click="() => { deleteModalShow = false; }">
              {{ t('components.buttons.cancel') }}
            </Button>
            <Button type="button" variant="danger" class="w-24" @click="(confirmDelete)">
              {{ t('components.buttons.delete') }}
            </Button>
          </div>
        </Dialog.Panel>
      </Dialog>
    </template>
  </DataList>
</template>
