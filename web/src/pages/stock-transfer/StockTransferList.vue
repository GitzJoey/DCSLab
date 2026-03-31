<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { DataListFlex } from '@/components/DataList';
import Button from '@/components/Base/Button';
import Lucide from '@/components/Base/Lucide';
import { Dialog } from '@/components/Base/Headless';
import StockTransferService from '@/services/StockTransferService';
import WarehouseService from '@/services/WarehouseService';
import { StockTransfer } from '@/types/models/StockTransfer';
import { NotificationData } from '@/types/models/NotificationData';
import { Collection } from '@/types/resources/Collection';
import { DataListEmittedData } from '@/components/DataList/DataList.vue';
import { ServiceResponse } from '@/types/services/ServiceResponse';
import { StockTransferReadAnyPaginateRequest } from '@/types/services/stock-transfer/StockTransferRequest';
import { useRouter } from 'vue-router';
import { ViewMode } from '@/types/enums/ViewMode';
import { useSelectedUserLocationStore } from '@/stores/selected-user-location';
import { ErrorCode } from '@/types/enums/ErrorCode';
import { formatDate, formatCurrency } from '@/utils/helper';
import type { AlertPlaceholderProps } from '@/components/AlertPlaceholder/AlertPlaceholder.vue';
import { FormInputDateTime, FormLabel, FormSelectSearch } from '@/components/Base/Form';
import { DropDownOption } from '@/types/models/DropDownOption';

const { t } = useI18n();
const router = useRouter();
const stockTransferService = new StockTransferService();
const warehouseService = new WarehouseService();
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
const showAdvancedFilters = ref<boolean>(false);
const selectedSourceWarehouseId = ref<string | null>(null);
const selectedDestinationWarehouseId = ref<string | null>(null);

const stockTransferLists = ref<Collection<Array<StockTransfer>> | null>({
  data: [],
  meta: {
    current_page: 1,
    from: null,
    last_page: 0,
    path: '',
    per_page: 10,
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

const sourceWarehouseDDL = ref<Array<DropDownOption> | null>(null);
const sourceWarehouseSearch = ref<string>('');
const sourceWarehouseOptions = computed(() =>
  (sourceWarehouseDDL.value ?? []).map((item) => ({
    value: item.code,
    label: item.name,
  })),
);

const destinationWarehouseDDL = ref<Array<DropDownOption> | null>(null);
const destinationWarehouseSearch = ref<string>('');
const destinationWarehouseOptions = computed(() =>
  (destinationWarehouseDDL.value ?? []).map((item) => ({
    value: item.code,
    label: item.name,
  })),
);

onMounted(async () => {
  emits('mode-state', ViewMode.LIST);

  if (!isUserLocationSelected.value) {
    router.push({
      name: 'side-menu-error-code',
      params: { code: ErrorCode.USERLOCATION_REQUIRED },
    });
    return;
  }

  const now = new Date();
  const startOfMonth = new Date(now.getFullYear(), now.getMonth(), 1, 0, 0, 0);
  const endOfMonth = new Date(now.getFullYear(), now.getMonth() + 1, 0, 23, 59, 59);
  startDate.value = formatDate(startOfMonth.toString(), 'YYYY-MM-DD HH:mm:ss');
  endDate.value = formatDate(endOfMonth.toString(), 'YYYY-MM-DD HH:mm:ss');

  await getStockTransfers('', true, 1, 10);
});

const getStockTransfers = async (search: string, refresh: boolean, page: number, per_page: number) => {
  emits('loading-state', true);
  searchText.value = search;
  const safePage = page > 0 ? page : 1;
  const safePerPage = per_page > 0 ? per_page : 10;

  const request: StockTransferReadAnyPaginateRequest = {
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    branch_id: selectedUserLocation.value.branch.id,
    search,
    start_date: startDate.value || null,
    end_date: endDate.value || null,
    source_warehouse_id: selectedSourceWarehouseId.value,
    destination_warehouse_id: selectedDestinationWarehouseId.value,
    refresh,
    page: safePage,
    per_page: safePerPage,
  };

  const result: ServiceResponse<Collection<Array<StockTransfer>> | null> =
    await stockTransferService.readAnyPaginate(request);

  if (result.success && result.data) {
    stockTransferLists.value = result.data;
    showAlertPlaceholder('hidden', '', null);
  } else {
    showAlertPlaceholder('danger', '', result.errors as Record<string, Array<string>>);
  }

  emits('loading-state', false);
};

const handleDataListChange = async (data: DataListEmittedData) => {
  await getStockTransfers(data.search.text, true, data.pagination.page, data.pagination.per_page);
};

const handleDateFilterChange = async () => {
  const perPage = stockTransferLists.value?.meta.per_page || 10;
  await getStockTransfers(searchText.value, true, 1, perPage);
};

const handleSourceWarehouseFilterChange = async () => {
  const perPage = stockTransferLists.value?.meta.per_page || 10;
  await getStockTransfers(searchText.value, true, 1, perPage);
};

const handleDestinationWarehouseFilterChange = async () => {
  const perPage = stockTransferLists.value?.meta.per_page || 10;
  await getStockTransfers(searchText.value, true, 1, perPage);
};

const loadSourceWarehouseDDL = async (search = '') => {
  if (!selectedUserLocation.value) return;

  const result = await warehouseService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    branch_id: selectedUserLocation.value.branch.id,
    search,
    include_id: undefined,
    status: undefined,
    refresh: false,
    limit: 20,
  });

  if (result.success && result.data) {
    sourceWarehouseDDL.value = result.data.data.map((item: any) => ({
      code: item.id,
      name: item.name,
    }));
  }
};

const loadDestinationWarehouseDDL = async (search = '') => {
  if (!selectedUserLocation.value) return;

  const result = await warehouseService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    branch_id: selectedUserLocation.value.branch.id,
    search,
    include_id: undefined,
    status: undefined,
    refresh: false,
    limit: 20,
  });

  if (result.success && result.data) {
    destinationWarehouseDDL.value = result.data.data.map((item: any) => ({
      code: item.id,
      name: item.name,
    }));
  }
};

const clearSourceWarehouseFilter = async () => {
  selectedSourceWarehouseId.value = null;
  await handleSourceWarehouseFilterChange();
};

const clearDestinationWarehouseFilter = async () => {
  selectedDestinationWarehouseId.value = null;
  await handleDestinationWarehouseFilterChange();
};

const toggleAdvancedFilters = async () => {
  showAdvancedFilters.value = !showAdvancedFilters.value;
  if (showAdvancedFilters.value) {
    await Promise.all([
      loadSourceWarehouseDDL(sourceWarehouseSearch.value),
      loadDestinationWarehouseDDL(destinationWarehouseSearch.value),
    ]);
  }
};

const viewSelected = (idx: number) => {
  expandDetail.value = expandDetail.value === idx ? null : idx;
};

const editSelected = (idx: number) => {
  if (!stockTransferLists.value) return;

  const ulid = stockTransferLists.value.data[idx].ulid;

  emits('mode-state', ViewMode.FORM_EDIT);
  router.push({
    name: 'side-menu-stock-transfer-edit',
    params: { ulid },
  });
};

const deleteSelected = (idx: number) => {
  if (!stockTransferLists.value) return;

  deleteUlid.value = stockTransferLists.value.data[idx].ulid;
  deleteModalShow.value = true;
};

const confirmDelete = async () => {
  deleteModalShow.value = false;
  emits('loading-state', true);

  const result = await stockTransferService.delete(deleteUlid.value);

  emits('loading-state', false);

  if (result.success) {
    emits('update-profile');
    await getStockTransfers('', true, 1, 10);
    showNotification(
      t('views.stock_transfer.alert.delete.title'),
      t('views.stock_transfer.alert.delete.message'),
    );
  } else {
    showAlertPlaceholder('danger', '', result.errors as Record<string, Array<string>>);
  }
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
  <div class="grid grid-cols-12 gap-6 mt-5">
    <div class="col-span-12 intro-y lg:col-span-12">
      <div class="grid grid-cols-12 gap-4 gap-y-3 mb-3">
        <div class="col-span-12 lg:col-span-3">
          <FormLabel>
            {{ t('views.stock_transfer.fields.start_date') }}
          </FormLabel>
          <FormInputDateTime v-model="startDate" @change="handleDateFilterChange" />
        </div>
        <div class="col-span-12 lg:col-span-3">
          <FormLabel>
            {{ t('views.stock_transfer.fields.end_date') }}
          </FormLabel>
          <FormInputDateTime v-model="endDate" @change="handleDateFilterChange" />
        </div>
        <div class="col-span-12 md:col-span-12 lg:col-span-1 flex items-end">
          <Button variant="soft-secondary"
            class="shadow-sm border-slate-300 bg-slate-100/80 hover:bg-slate-200 hover:border-slate-400 dark:border-darkmode-300 dark:bg-darkmode-300/40 dark:hover:bg-darkmode-300"
            @click="toggleAdvancedFilters">
            <Lucide icon="Filter" class="w-4 h-5" />
          </Button>
        </div>
      </div>

      <div v-if="showAdvancedFilters" class="grid grid-cols-12 gap-4 gap-y-3 mb-3">
        <div class="col-span-12 md:col-span-6 lg:col-span-3">
          <FormLabel>
            {{ t('views.stock_transfer.fields.source_warehouse_id') }}
          </FormLabel>
          <div class="flex items-center gap-2">
            <div class="flex-1">
              <FormSelectSearch
                v-model="selectedSourceWarehouseId"
                v-model:search="sourceWarehouseSearch"
                :options="sourceWarehouseOptions"
                :placeholder="t('components.dropdown.placeholder')"
                @change="handleSourceWarehouseFilterChange"
                @search="loadSourceWarehouseDDL"
                @clear="clearSourceWarehouseFilter"
              />
            </div>
          </div>
        </div>
        <div class="col-span-12 md:col-span-6 lg:col-span-3">
          <FormLabel>
            {{ t('views.stock_transfer.fields.destination_warehouse_id') }}
          </FormLabel>
          <div class="flex items-center gap-2">
            <div class="flex-1">
              <FormSelectSearch
                v-model="selectedDestinationWarehouseId"
                v-model:search="destinationWarehouseSearch"
                :options="destinationWarehouseOptions"
                :placeholder="t('components.dropdown.placeholder')"
                @change="handleDestinationWarehouseFilterChange"
                @search="loadDestinationWarehouseDDL"
                @clear="clearDestinationWarehouseFilter"
              />
            </div>
          </div>
        </div>
      </div>

      <DataListFlex
        :data="stockTransferLists"
        :enable-search="true"
        :can-print="true"
        :can-export="true"
        :rows="stockTransferLists?.data ?? []"
        row-class="bg-white dark:bg-darkmode-600"
        :pagination="stockTransferLists ? stockTransferLists.meta : null"
        @dataListChanged="handleDataListChange"
      >
        <template #row="{ item, index }">
          <div class="col-span-12 lg:col-span-5 md:col-span-4 self-start">
            <div class="text-primary text-xs font-semibold uppercase tracking-wide mb-1">
              {{ t('views.stock_transfer.page_title') }}
            </div>
            <div class="text-slate-500 text-xs whitespace-nowrap">
              {{ t('views.stock_transfer.fields.code') }}:
              {{ (item as StockTransfer).code ?? '-' }}
            </div>
            <div class="text-slate-500 text-xs whitespace-nowrap">
              {{ t('views.stock_transfer.fields.date') }}:
              {{ (item as StockTransfer).date ? formatDate((item as StockTransfer).date, 'DD-MMM-YYYY HH:mm:ss') : '-' }}
            </div>
            <div v-if="(item as StockTransfer).remarks?.trim()" class="text-slate-500 text-xs">
              {{ t('views.stock_transfer.fields.remarks') }}:
              {{ (item as StockTransfer).remarks }}
            </div>
          </div>

          <div class="col-span-12 lg:col-span-4 md:col-span-4 self-start">
            <div class="text-primary text-xs font-semibold uppercase tracking-wide mb-1">
              {{ t('views.warehouse.page_title') }}
            </div>
            <div class="text-slate-500 text-xs whitespace-nowrap">
              {{ t('views.stock_transfer.fields.source_warehouse_id') }}:
              {{ (item as StockTransfer).source_warehouse?.name ?? '-' }}
            </div>
            <div class="text-slate-500 text-xs whitespace-nowrap">
              {{ t('views.stock_transfer.fields.destination_warehouse_id') }}:
              {{ (item as StockTransfer).destination_warehouse?.name ?? '-' }}
            </div>
          </div>

          <div class="col-span-12 lg:col-span-2 md:col-span-2 self-start">
            <div class="text-primary text-xs font-semibold uppercase tracking-wide mb-1">
              {{ t('views.stock_transfer.fields.is_posted') }}
            </div>
            <div class="text-slate-500 text-xs whitespace-nowrap">
              <span class="inline-flex items-center">
                <Lucide v-if="(item as StockTransfer).is_posted" icon="CheckCircle" class="w-4 h-4 text-success" />
                <Lucide v-else icon="X" class="w-4 h-4 text-danger" />
              </span>
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

          <div
            v-if="expandDetail === index"
            class="col-span-12 border-t border-slate-200 dark:border-darkmode-400 mt-2 pt-3"
          >
            <div class="text-primary text-xs font-semibold uppercase tracking-wide mb-1">
              {{ t('views.stock_transfer.field_groups.product_units') }}
            </div>
            <div
              v-if="((item as StockTransfer).product_units?.length ?? 0) === 0"
              class="text-slate-500 text-xs italic"
            >
              {{ t('components.data-list.data_not_found') }}
            </div>
            <div v-else class="space-y-2 text-xs">
              <div
                v-for="(productUnit, productIdx) in (item as StockTransfer).product_units"
                :key="productUnit.ulid"
                class="flex gap-2"
              >
                <div class="w-6 text-right text-slate-500">{{ productIdx + 1 }}.</div>
                <div class="flex-1">
                  <div class="font-medium break-all">
                    [{{ productUnit.product_unit.code }}] {{ productUnit.product_unit.product.name }}
                  </div>
                  <div class="text-slate-500">
                    {{ t('views.stock_transfer_product_unit.fields.qty') }}:
                    <span class="font-medium">
                      {{ formatCurrency(productUnit.qty) }} {{ productUnit.product_unit.unit.name }}
                    </span>
                  </div>
                  <div v-if="Number(productUnit.product_unit_conversion_value ?? 1) > 1" class="text-slate-500">
                    {{ t('views.stock_transfer_product_unit.fields.product_unit_conversion_value') }}:
                    <span class="font-medium">{{ formatCurrency(productUnit.product_unit_conversion_value) }}</span>
                  </div>
                  <div v-if="productUnit.remarks?.trim()" class="text-slate-500">
                    {{ t('views.stock_transfer_product_unit.fields.remarks') }}:
                    <span class="font-medium">{{ productUnit.remarks }}</span>
                  </div>
                  <div v-if="productUnit.product_unit?.product?.is_use_serial_number" class="text-slate-500 mt-0.5">
                    {{ t('views.product.fields.serial_number') }}:
                    <span v-if="productUnit.serials?.length" class="break-all">
                      {{ productUnit.serials.map((serial) => serial.serial).join(', ') }}
                    </span>
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
        <div class="mt-5 text-3xl">
          {{ t('components.delete-modal.title') }}
        </div>
        <div class="mt-2 text-slate-500">
          {{ t('components.delete-modal.desc_1') }}
          <br />
          {{ t('components.delete-modal.desc_2') }}
        </div>
      </div>
      <div class="px-5 pb-8 text-center">
        <Button type="button" variant="outline-secondary" class="w-24 mr-1" @click="deleteModalShow = false">
          {{ t('components.buttons.cancel') }}
        </Button>
        <Button type="button" variant="danger" class="w-24" @click="confirmDelete">
          {{ t('components.buttons.delete') }}
        </Button>
      </div>
    </Dialog.Panel>
  </Dialog>
</template>
