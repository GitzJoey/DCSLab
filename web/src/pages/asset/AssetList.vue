<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import DataList from '@/components/DataList';
import { useI18n } from 'vue-i18n';
import Button from '@/components/Base/Button';
import Lucide from '@/components/Base/Lucide';
import Table from '@/components/Base/Table';
import AssetService from '@/services/AssetService';
import DashboardService from '@/services/DashboardService';
import { Asset } from '@/types/models/Asset';
import { Collection } from '@/types/resources/Collection';
import { DataListEmittedData } from '@/components/DataList/DataList.vue';
import { ServiceResponse } from '@/types/services/ServiceResponse';
import { AssetReadAnyPaginateRequest } from '@/types/services/asset/AssetRequest';
import { useRouter } from 'vue-router';
import { Dialog } from '@/components/Base/Headless';
import { ViewMode } from '@/types/enums/ViewMode';
import { useSelectedUserLocationStore } from '@/stores/selected-user-location';
import { ErrorCode } from '@/types/enums/ErrorCode';
import { NotificationData } from '@/types/models/NotificationData';
import { type AlertPlaceholderProps } from '@/components/AlertPlaceholder/AlertPlaceholder.vue';
import { DropDownOption } from '@/types/models/DropDownOption';

const { t } = useI18n();
const router = useRouter();
const assetService = new AssetService();
const dashboardService = new DashboardService();
const selectedUserLocationStore = useSelectedUserLocationStore();

const emits = defineEmits([
  'mode-state',
  'loading-state',
  'update-profile',
  'show-alertplaceholder',
  'show-notification',
]);

const deleteUlid = ref<string>('');
const deleteModalShow = ref<boolean>(false);
const expandDetail = ref<number | null>(null);
const assetLists = ref<Collection<Array<Asset>> | null>({
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
  },
});
const statusDDL = ref<Array<DropDownOption>>([]);

const isUserLocationSelected = computed(() => selectedUserLocationStore.isUserLocationSelected);
const selectedUserLocation = computed(() => selectedUserLocationStore.selectedUserLocation);

onMounted(async () => {
  emits('mode-state', ViewMode.LIST);

  if (!isUserLocationSelected.value) {
    router.push({
      name: 'side-menu-error-code',
      params: { code: ErrorCode.USERLOCATION_REQUIRED },
    });
    return;
  }

  await loadStatuses();
  await getAssets('', true, 1, 10);
});

const loadStatuses = async () => {
  const result = await dashboardService.getStatusDDL(false);
  if (result) {
    statusDDL.value = result;
  }
};

const getStatusLabel = (status: number) => {
  const selected = statusDDL.value.find((item) => Number(item.code) === Number(status));
  return selected?.name ?? status;
};

const getAssets = async (search: string, refresh: boolean, page: number, per_page: number) => {
  emits('loading-state', true);

  const searchReq: AssetReadAnyPaginateRequest = {
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    search,
    include_id: undefined,
    refresh,
    page,
    per_page,
  };

  const result: ServiceResponse<Collection<Array<Asset>> | null> = await assetService.readAnyPaginate(searchReq);

  if (result.success && result.data) {
    assetLists.value = result.data;
    showAlertPlaceholder('hidden', '', null);
  } else {
    showAlertPlaceholder('danger', '', result.errors as Record<string, Array<string>>);
  }

  emits('loading-state', false);
};

const onDataListChanged = async (data: DataListEmittedData) => {
  await getAssets(data.search.text, false, data.pagination.page, data.pagination.per_page);
};

const viewSelected = (idx: number) => {
  if (expandDetail.value === idx) {
    expandDetail.value = null;
  } else {
    expandDetail.value = idx;
  }
};

const editSelected = (itemIdx: number) => {
  if (!assetLists.value) return;

  const ulid = assetLists.value.data[itemIdx].ulid;
  router.push({
    name: 'side-menu-asset-edit',
    params: { ulid },
  });
};

const deleteSelected = (itemIdx: number) => {
  if (!assetLists.value) return;

  deleteUlid.value = assetLists.value.data[itemIdx].ulid;
  deleteModalShow.value = true;
};

const confirmDelete = async () => {
  deleteModalShow.value = false;
  emits('loading-state', true);

  const result = await assetService.delete(deleteUlid.value);

  if (result.success) {
    emits('update-profile');
    await getAssets('', true, 1, 10);
    showNotification(t('views.asset.alert.delete_asset.title'), t('views.asset.alert.delete_asset.content'));
  } else {
    showAlertPlaceholder('danger', '', result.errors as Record<string, Array<string>>);
  }

  emits('loading-state', false);
};

const showNotification = (pTitle: string, pContent: string) => {
  const n: NotificationData = {
    title: pTitle,
    content: pContent,
  };

  emits('show-notification', n);
};

const showAlertPlaceholder = (
  pAlertType: 'hidden' | 'danger' | 'success' | 'warning' | 'pending' | 'dark',
  pTitle: string,
  pAlertList: Record<string, Array<string>> | null,
) => {
  const ap: AlertPlaceholderProps = {
    alertType: pAlertType,
    title: pTitle,
    alertList: pAlertList,
  };

  emits('show-alertplaceholder', ap);
};
</script>

<template>
  <DataList
    :title="t('views.asset.table.title')"
    :enable-search="true"
    :can-print="true"
    :can-export="true"
    :pagination="assetLists ? assetLists.meta : null"
    @dataListChanged="onDataListChanged"
  >
    <template #content>
      <Table class="mt-5" :hover="true">
        <Table.Thead variant="light">
          <Table.Tr>
            <Table.Th class="whitespace-nowrap">{{ t('views.asset.table.cols.code') }}</Table.Th>
            <Table.Th class="whitespace-nowrap">{{ t('views.asset.table.cols.name') }}</Table.Th>
            <Table.Th class="whitespace-nowrap">{{ t('views.asset.table.cols.asset_category') }}</Table.Th>
            <Table.Th class="whitespace-nowrap">{{ t('views.asset.table.cols.asset_unit') }}</Table.Th>
            <Table.Th class="whitespace-nowrap">{{ t('views.asset.table.cols.status') }}</Table.Th>
            <Table.Th class="whitespace-nowrap"></Table.Th>
          </Table.Tr>
        </Table.Thead>
        <Table.Tbody v-if="assetLists !== null">
          <template v-if="assetLists.data.length == 0">
            <Table.Tr class="intro-x">
              <Table.Td colspan="6">
                <div class="flex justify-center italic">
                  {{ t('components.data-list.data_not_found') }}
                </div>
              </Table.Td>
            </Table.Tr>
          </template>
          <template v-for="(item, itemIdx) in assetLists.data" :key="item.ulid">
            <Table.Tr class="intro-x">
              <Table.Td>{{ item.code }}</Table.Td>
              <Table.Td>{{ item.name }}</Table.Td>
              <Table.Td>{{ item.asset_category?.name || '-' }}</Table.Td>
              <Table.Td>{{ item.asset_unit?.name || '-' }}</Table.Td>
              <Table.Td>{{ getStatusLabel(item.status) }}</Table.Td>
              <Table.Td>
                <div class="flex justify-end gap-1">
                  <Button variant="outline-secondary" @click="viewSelected(itemIdx)">
                    <Lucide icon="Info" class="w-4 h-4" />
                  </Button>
                  <Button variant="outline-secondary" @click="editSelected(itemIdx)">
                    <Lucide icon="Pen" class="w-4 h-4" />
                  </Button>
                  <Button variant="outline-secondary" @click="deleteSelected(itemIdx)">
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
              <Table.Td colspan="6">
                <div class="flex flex-row">
                  <div class="ml-5 w-48 pr-5 text-right font-medium">{{ t('views.asset.fields.code') }}</div>
                  <div class="flex-1">{{ item.code }}</div>
                </div>
                <div class="mt-1 flex flex-row">
                  <div class="ml-5 w-48 pr-5 text-right font-medium">{{ t('views.asset.fields.name') }}</div>
                  <div class="flex-1">{{ item.name }}</div>
                </div>
                <div class="mt-1 flex flex-row">
                  <div class="ml-5 w-48 pr-5 text-right font-medium">{{ t('views.asset.fields.asset_category_id') }}</div>
                  <div class="flex-1">{{ item.asset_category?.name || '-' }}</div>
                </div>
                <div class="mt-1 flex flex-row">
                  <div class="ml-5 w-48 pr-5 text-right font-medium">{{ t('views.asset.fields.asset_unit_id') }}</div>
                  <div class="flex-1">{{ item.asset_unit?.name || '-' }}</div>
                </div>
                <div class="mt-1 flex flex-row">
                  <div class="ml-5 w-48 pr-5 text-right font-medium">{{ t('views.asset.fields.status') }}</div>
                  <div class="flex-1">{{ getStatusLabel(item.status) }}</div>
                </div>
                <div class="mt-1 flex flex-row">
                  <div class="ml-5 w-48 pr-5 text-right font-medium">{{ t('views.asset.fields.remarks') }}</div>
                  <div class="flex-1">{{ item.remarks || '-' }}</div>
                </div>
              </Table.Td>
            </Table.Tr>
          </template>
        </Table.Tbody>
      </Table>

      <Dialog :open="deleteModalShow" @close="() => { deleteModalShow = false; }">
        <Dialog.Panel>
          <div class="p-5 text-center">
            <Lucide icon="XCircle" class="mx-auto mt-3 h-16 w-16 text-danger" />
            <div class="mt-5 text-3xl">{{ t('components.delete-modal.title') }}</div>
            <div class="mt-2 text-slate-500">
              {{ t('components.delete-modal.desc_1') }}
              <br />
              {{ t('components.delete-modal.desc_2') }}
            </div>
          </div>
          <div class="px-5 pb-8 text-center">
            <Button type="button" variant="outline-secondary" class="mr-1 w-24" @click="deleteModalShow = false">
              {{ t('components.buttons.cancel') }}
            </Button>
            <Button type="button" variant="danger" class="w-24" @click="confirmDelete">
              {{ t('components.buttons.delete') }}
            </Button>
          </div>
        </Dialog.Panel>
      </Dialog>
    </template>
  </DataList>
</template>
