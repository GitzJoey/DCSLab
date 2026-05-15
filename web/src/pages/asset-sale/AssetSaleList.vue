<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { DataListFlex } from '@/components/DataList';
import Button from '@/components/Base/Button';
import Lucide from '@/components/Base/Lucide';
import { Dialog } from '@/components/Base/Headless';
import { FormInputDateTime, FormLabel } from '@/components/Base/Form';
import AssetSaleService from '@/services/AssetSaleService';
import type { AssetSale } from '@/types/models/AssetSale';
import type { NotificationData } from '@/types/models/NotificationData';
import type { Collection } from '@/types/resources/Collection';
import type { DataListEmittedData } from '@/components/DataList/DataList.vue';
import type { ServiceResponse } from '@/types/services/ServiceResponse';
import type { AssetSaleReadAnyPaginateRequest } from '@/types/services/asset-sale/AssetSaleRequest';
import { useRouter } from 'vue-router';
import { ViewMode } from '@/types/enums/ViewMode';
import { useSelectedUserLocationStore } from '@/stores/selected-user-location';
import { ErrorCode } from '@/types/enums/ErrorCode';
import { formatDate, formatCurrency } from '@/utils/helper';
import type { AlertPlaceholderProps } from '@/components/AlertPlaceholder/AlertPlaceholder.vue';

const { t } = useI18n();
const router = useRouter();
const assetSaleService = new AssetSaleService();
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
const startDate = ref<string | null>(null);
const endDate = ref<string | null>(null);
const searchText = ref<string>('');

const assetSaleLists = ref<Collection<Array<AssetSale>> | null>({
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

const isUserLocationSelected = computed(() => selectedUserLocationStore.isUserLocationSelected);
const selectedUserLocation = computed(() => selectedUserLocationStore.selectedUserLocation);

onMounted(async () => {
  emits('mode-state', ViewMode.LIST);

  if (!isUserLocationSelected.value) {
    router.push({ name: 'side-menu-error-code', params: { code: ErrorCode.USERLOCATION_REQUIRED } });
    return;
  }

  const now = new Date();
  const startOfMonth = new Date(now.getFullYear(), now.getMonth(), 1, 0, 0, 0);
  const endOfMonth = new Date(now.getFullYear(), now.getMonth() + 1, 0, 23, 59, 59);
  startDate.value = formatDate(startOfMonth.toString(), 'YYYY-MM-DD HH:mm:ss');
  endDate.value = formatDate(endOfMonth.toString(), 'YYYY-MM-DD HH:mm:ss');

  await getAssetSales('', true, 1, 10);
});

const getAssetSales = async (search: string, refresh: boolean, page: number, perPage: number) => {
  emits('loading-state', true);
  searchText.value = search;

  const request: AssetSaleReadAnyPaginateRequest = {
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    branch_id: selectedUserLocation.value.branch.id,
    search,
    start_date: startDate.value || null,
    end_date: endDate.value || null,
    refresh,
    page,
    per_page: perPage,
  };

  const result: ServiceResponse<Collection<Array<AssetSale>> | null> = await assetSaleService.readAnyPaginate(request);

  if (result.success && result.data) {
    assetSaleLists.value = result.data;
    showAlertPlaceholder('hidden', '', null);
  } else {
    showAlertPlaceholder('danger', '', result.errors as Record<string, Array<string>>);
  }

  emits('loading-state', false);
};

const handleDataListChange = async (data: DataListEmittedData) => {
  await getAssetSales(data.search.text, true, data.pagination.page, data.pagination.per_page);
};

const handleDateFilterChange = async () => {
  const perPage = assetSaleLists.value?.meta.per_page || 10;
  await getAssetSales(searchText.value, true, 1, perPage);
};

const viewSelected = (idx: number) => {
  expandDetail.value = expandDetail.value === idx ? null : idx;
};

const editSelected = (idx: number) => {
  if (!assetSaleLists.value) return;

  const ulid = assetSaleLists.value.data[idx].ulid;
  emits('mode-state', ViewMode.FORM_EDIT);
  router.push({ name: 'side-menu-asset-sale-edit', params: { ulid } });
};

const deleteSelected = (idx: number) => {
  if (!assetSaleLists.value) return;

  deleteUlid.value = assetSaleLists.value.data[idx].ulid;
  deleteModalShow.value = true;
};

const confirmDelete = async () => {
  deleteModalShow.value = false;
  emits('loading-state', true);

  const result = await assetSaleService.delete(deleteUlid.value);

  emits('loading-state', false);

  if (result.success) {
    emits('update-profile');
    await getAssetSales(searchText.value, true, 1, assetSaleLists.value?.meta.per_page || 10);
    showNotification(t('views.asset_sale.alert.delete.title'), t('views.asset_sale.alert.delete.message'));
  } else {
    showAlertPlaceholder('danger', '', result.errors as Record<string, Array<string>>);
  }
};

const showNotification = (pTitle: string, pContent: string) => {
  const notification: NotificationData = { title: pTitle, content: pContent };
  emits('show-notification', notification);
};

const showAlertPlaceholder = (
  pAlertType: 'hidden' | 'danger' | 'success' | 'warning' | 'pending' | 'dark',
  pTitle: string,
  pAlertList: Record<string, Array<string>> | null,
) => {
  const alertPlaceholder: AlertPlaceholderProps = {
    alertType: pAlertType,
    title: pTitle,
    alertList: pAlertList,
  };

  emits('show-alertplaceholder', alertPlaceholder);
};
</script>

<template>
  <div class="grid grid-cols-12 gap-6 mt-5">
    <div class="col-span-12 intro-y lg:col-span-12">
      <div class="grid grid-cols-12 gap-4 gap-y-3 mb-3">
        <div class="col-span-12 lg:col-span-3">
          <FormLabel>{{ t('views.asset_sale.fields.start_date') }}</FormLabel>
          <FormInputDateTime v-model="startDate" @change="handleDateFilterChange" />
        </div>
        <div class="col-span-12 lg:col-span-3">
          <FormLabel>{{ t('views.asset_sale.fields.end_date') }}</FormLabel>
          <FormInputDateTime v-model="endDate" @change="handleDateFilterChange" />
        </div>
      </div>

      <DataListFlex
        :data="assetSaleLists"
        :enable-search="true"
        :can-print="true"
        :can-export="true"
        :rows="assetSaleLists?.data ?? []"
        row-class="bg-white dark:bg-darkmode-600"
        :pagination="assetSaleLists ? assetSaleLists.meta : null"
        @dataListChanged="handleDataListChange"
      >
        <template #row="{ item, index }">
          <div class="col-span-12 lg:col-span-4 md:col-span-4 self-start">
            <div class="text-primary text-xs font-semibold uppercase tracking-wide mb-1">
              {{ t('views.asset_sale.page_title') }}
            </div>
            <div class="text-slate-500 text-xs whitespace-nowrap">
              {{ t('views.asset_sale.fields.code') }}: {{ (item as AssetSale).code ?? '-' }}
            </div>
            <div class="text-slate-500 text-xs whitespace-nowrap">
              {{ t('views.asset_sale.fields.date') }}:
              {{ (item as AssetSale).date ? formatDate((item as AssetSale).date, 'DD-MMM-YYYY HH:mm:ss') : '-' }}
            </div>
            <div class="text-slate-500 text-xs whitespace-nowrap">
              {{ t('views.asset_sale.fields.due_days') }}: {{ (item as AssetSale).due_days ?? 0 }}
            </div>
          </div>

          <div class="col-span-12 lg:col-span-4 md:col-span-4 self-start">
            <div class="text-primary text-xs font-semibold uppercase tracking-wide mb-1">
              {{ t('views.asset_sale.fields.customer_id') }}
            </div>
            <div class="text-slate-500 text-xs whitespace-nowrap">
              [{{ (item as AssetSale).customer?.code ?? '-' }}] {{ (item as AssetSale).customer?.name ?? '-' }}
            </div>
            <div class="text-slate-500 text-xs whitespace-nowrap">
              {{ t('views.asset_sale.fields.item_total') }}:
              {{ formatCurrency((item as AssetSale).item_total ?? 0) }}
            </div>
            <div class="text-slate-500 text-xs whitespace-nowrap">
              {{ t('views.asset_sale.fields.amount_receivable') }}:
              {{ formatCurrency((item as AssetSale).amount_receivable ?? 0) }}
            </div>
          </div>

          <div class="col-span-12 lg:col-span-3 md:col-span-2 self-start">
            <div class="text-primary text-xs font-semibold uppercase tracking-wide mb-1">
              {{ t('views.asset_sale.fields.is_posted') }}
            </div>
            <div class="text-slate-500 text-xs whitespace-nowrap mb-1">
              <span class="inline-flex items-center">
                <Lucide v-if="(item as AssetSale).is_posted" icon="CheckCircle" class="w-4 h-4 text-success" />
                <Lucide v-else icon="X" class="w-4 h-4 text-danger" />
              </span>
            </div>
            <div v-if="(item as AssetSale).remarks?.trim()" class="text-slate-500 text-xs break-all">
              {{ t('views.asset_sale.fields.remarks') }}: {{ (item as AssetSale).remarks }}
            </div>
          </div>

          <div class="col-span-12 lg:col-span-1 md:col-span-2 flex justify-end items-start gap-2">
            <Button size="sm" variant="outline-secondary" class="flex items-center gap-1" @click="viewSelected(index)">
              <Lucide icon="Info" class="w-4 h-4" />
            </Button>
            <Button size="sm" variant="outline-secondary" class="flex items-center gap-1" @click="editSelected(index)">
              <Lucide icon="Pen" class="w-4 h-4" />
            </Button>
            <Button size="sm" variant="outline-secondary" class="flex items-center gap-1" @click="deleteSelected(index)">
              <Lucide icon="Trash2" class="w-4 h-4 text-danger" />
            </Button>
          </div>

          <div v-if="expandDetail === index" class="col-span-12 border-t border-slate-200 dark:border-darkmode-400 mt-2 pt-3">
            <div class="text-primary text-xs font-semibold uppercase tracking-wide mb-1">
              {{ t('views.asset_sale.field_groups.items') }}
            </div>
            <div v-if="((item as AssetSale).items?.length ?? 0) === 0" class="text-slate-500 text-xs italic">
              {{ t('components.data-list.data_not_found') }}
            </div>
            <div v-else class="space-y-2 text-xs">
              <div v-for="(assetItem, assetIdx) in (item as AssetSale).items" :key="assetItem.ulid" class="flex gap-2">
                <div class="w-6 text-right text-slate-500">{{ assetIdx + 1 }}.</div>
                <div class="flex-1">
                  <div class="font-medium break-all">[{{ assetItem.asset.code }}] {{ assetItem.asset.name }}</div>
                  <div class="text-slate-500">
                    {{ t('views.asset_sale.table.cols.qty') }}:
                    <span class="font-medium">{{ formatCurrency(assetItem.qty) }}</span>
                  </div>
                  <div class="text-slate-500">
                    {{ t('views.asset_sale.table.cols.unit_price') }}:
                    <span class="font-medium">{{ formatCurrency(assetItem.unit_price) }}</span>
                  </div>
                  <div class="text-slate-500">
                    {{ t('views.asset_sale.table.cols.subtotal') }}:
                    <span class="font-medium">{{ formatCurrency(assetItem.subtotal) }}</span>
                  </div>
                  <div class="text-slate-500 mt-0.5 break-all">
                    {{ t('views.product.fields.serial_number') }}:
                    <span v-if="assetItem.serials?.length">{{ assetItem.serials.map((serial) => serial.serial).join(', ') }}</span>
                    <span v-else>{{ t('components.data-list.data_not_found') }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </template>
      </DataListFlex>
    </div>
  </div>

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
        <Button type="button" variant="outline-secondary" @click="() => { deleteModalShow = false; }" class="w-24 mr-1">
          {{ t('components.buttons.cancel') }}
        </Button>
        <Button type="button" variant="danger" class="w-24" @click="confirmDelete">
          {{ t('components.buttons.delete') }}
        </Button>
      </div>
    </Dialog.Panel>
  </Dialog>
</template>
