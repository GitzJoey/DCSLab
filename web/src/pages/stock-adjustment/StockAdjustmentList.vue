<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { DataListFlex } from '@/components/DataList';
import Button from '@/components/Base/Button';
import Lucide from '@/components/Base/Lucide';
import { Dialog } from '@/components/Base/Headless';
import StockAdjustmentService from '@/services/StockAdjustmentService';
import { StockAdjustment } from '@/types/models/StockAdjustment';
import { NotificationData } from '@/types/models/NotificationData';
import { Collection } from '@/types/resources/Collection';
import { DataListEmittedData } from '@/components/DataList/DataList.vue';
import { ServiceResponse } from '@/types/services/ServiceResponse';
import { StockAdjustmentReadAnyPaginateRequest } from '@/types/services/stock-adjustment/StockAdjustmentRequest';
import { useRouter } from 'vue-router';
import { ViewMode } from '@/types/enums/ViewMode';
import { useSelectedUserLocationStore } from '@/stores/selected-user-location';
import { ErrorCode } from '@/types/enums/ErrorCode';
import { formatDate, formatCurrency } from '@/utils/helper';
import type { AlertPlaceholderProps } from '@/components/AlertPlaceholder/AlertPlaceholder.vue';
import { FormInputDateTime, FormLabel, FormSelectSearch } from '@/components/Base/Form';
import StockAdjustmentCategoryService from '@/services/StockAdjustmentCategoryService';
import WarehouseService from '@/services/WarehouseService';
import { DropDownOption } from '@/types/models/DropDownOption';

const { t } = useI18n();
const router = useRouter();
const stockAdjustmentService = new StockAdjustmentService();
const stockAdjustmentCategoryService = new StockAdjustmentCategoryService();
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
const selectedCategoryId = ref<string | null>(null);
const selectedInWarehouseId = ref<string | null>(null);
const selectedOutWarehouseId = ref<string | null>(null);

const stockAdjustmentLists = ref<Collection<Array<StockAdjustment>> | null>({
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

const categoryDDL = ref<Array<DropDownOption> | null>(null);
const categorySearch = ref<string>('');
const categoryOptions = computed(() =>
  (categoryDDL.value ?? []).map((item) => ({
    value: item.code,
    label: item.name,
  })),
);

const inWarehouseDDL = ref<Array<DropDownOption> | null>(null);
const inWarehouseSearch = ref<string>('');
const inWarehouseOptions = computed(() =>
  (inWarehouseDDL.value ?? []).map((item) => ({
    value: item.code,
    label: item.name,
  })),
);

const outWarehouseDDL = ref<Array<DropDownOption> | null>(null);
const outWarehouseSearch = ref<string>('');
const outWarehouseOptions = computed(() =>
  (outWarehouseDDL.value ?? []).map((item) => ({
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

  await getStockAdjustments('', true, 1, 10);
});

const getStockAdjustments = async (search: string, refresh: boolean, page: number, per_page: number) => {
  emits('loading-state', true);
  searchText.value = search;

  const request: StockAdjustmentReadAnyPaginateRequest = {
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    branch_id: selectedUserLocation.value.branch.id,
    search,
    start_date: startDate.value || null,
    end_date: endDate.value || null,
    category_id: selectedCategoryId.value,
    in_warehouse_id: selectedInWarehouseId.value,
    out_warehouse_id: selectedOutWarehouseId.value,
    refresh,
    page,
    per_page,
  };

  const result: ServiceResponse<Collection<Array<StockAdjustment>> | null> =
    await stockAdjustmentService.readAnyPaginate(request);

  if (result.success && result.data) {
    stockAdjustmentLists.value = result.data;
    showAlertPlaceholder('hidden', '', null);
  } else {
    showAlertPlaceholder('danger', '', result.errors as Record<string, Array<string>>);
  }

  emits('loading-state', false);
};

const handleDataListChange = async (data: DataListEmittedData) => {
  await getStockAdjustments(
    data.search.text,
    true,
    data.pagination.page,
    data.pagination.per_page
  );
};

const handleDateFilterChange = async () => {
  const perPage = stockAdjustmentLists.value?.meta.per_page || 10;
  await getStockAdjustments(searchText.value, true, 1, perPage);
};

const handleCategoryFilterChange = async () => {
  const perPage = stockAdjustmentLists.value?.meta.per_page || 10;
  await getStockAdjustments(searchText.value, true, 1, perPage);
};

const handleInWarehouseFilterChange = async () => {
  const perPage = stockAdjustmentLists.value?.meta.per_page || 10;
  await getStockAdjustments(searchText.value, true, 1, perPage);
};

const handleOutWarehouseFilterChange = async () => {
  const perPage = stockAdjustmentLists.value?.meta.per_page || 10;
  await getStockAdjustments(searchText.value, true, 1, perPage);
};

const loadInWarehouseDDL = async (search = '') => {
  if (!selectedUserLocation.value) return;

  const result = await warehouseService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    branch_id: selectedUserLocation.value.branch.id,
    search,
    status: undefined,
    refresh: false,
    limit: 20,
  });

  if (result.success && result.data) {
    inWarehouseDDL.value = result.data.data.map((item: any) => ({
      code: item.id,
      name: item.name,
    }));
  }
};

const loadOutWarehouseDDL = async (search = '') => {
  if (!selectedUserLocation.value) return;

  const result = await warehouseService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    branch_id: selectedUserLocation.value.branch.id,
    search,
    status: undefined,
    refresh: false,
    limit: 20,
  });

  if (result.success && result.data) {
    outWarehouseDDL.value = result.data.data.map((item: any) => ({
      code: item.id,
      name: item.name,
    }));
  }
};

const loadCategoryDDL = async (search = '') => {
  if (!selectedUserLocation.value) return;

  const result = await stockAdjustmentCategoryService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    search,
    include_id: undefined,
    refresh: false,
    limit: 20,
  });

  if (result.success && result.data) {
    categoryDDL.value = result.data.data.map((item: any) => ({
      code: item.id,
      name: item.name,
    }));
  }
};

const clearCategoryFilter = () => {
  selectedCategoryId.value = null;
  categorySearch.value = '';
};

const clearInWarehouseFilter = () => {
  selectedInWarehouseId.value = null;
  inWarehouseSearch.value = '';
};

const clearOutWarehouseFilter = () => {
  selectedOutWarehouseId.value = null;
  outWarehouseSearch.value = '';
};

const toggleAdvancedFilters = async () => {
  showAdvancedFilters.value = !showAdvancedFilters.value;
  if (showAdvancedFilters.value) {
    await Promise.all([
      loadCategoryDDL(categorySearch.value),
      loadInWarehouseDDL(inWarehouseSearch.value),
      loadOutWarehouseDDL(outWarehouseSearch.value),
    ]);
  }
};

const viewSelected = (idx: number) => {
  if (expandDetail.value === idx) {
    expandDetail.value = null;
  } else {
    expandDetail.value = idx;
  }
};

const editSelected = (idx: number) => {
  if (!stockAdjustmentLists.value) return;

  const ulid = stockAdjustmentLists.value.data[idx].ulid;

  emits('mode-state', ViewMode.FORM_EDIT);
  router.push({
    name: 'side-menu-stock-adjustment-edit',
    params: { ulid: ulid },
  });
};

const deleteSelected = (idx: number) => {
  if (!stockAdjustmentLists.value) return;

  const ulid = stockAdjustmentLists.value.data[idx].ulid;
  deleteUlid.value = ulid;
  deleteModalShow.value = true;
};

const confirmDelete = async () => {
  deleteModalShow.value = false;
  emits('loading-state', true);

  const result = await stockAdjustmentService.delete(deleteUlid.value);

  emits('loading-state', false);

  if (result.success) {
    emits('update-profile');
    await getStockAdjustments('', true, 1, 10);
    showNotification(
      t('views.stock_adjustment.alert.delete.title'),
      t('views.stock_adjustment.alert.delete.message'),
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
  <!-- page layout -->
  <div class="grid grid-cols-12 gap-6 mt-5">
    <div class="col-span-12 intro-y lg:col-span-12">
      <div class="grid grid-cols-12 gap-4 gap-y-3 mb-3">
        <div class="col-span-12 lg:col-span-3">
          <FormLabel>
            {{ t('views.stock_adjustment.fields.start_date') }}
          </FormLabel>
          <FormInputDateTime v-model="startDate" @change="handleDateFilterChange" />
        </div>
        <div class="col-span-12 lg:col-span-3">
          <FormLabel>
            {{ t('views.stock_adjustment.fields.end_date') }}
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
        <div class="col-span-12 md:col-span-4 lg:col-span-2">
          <FormLabel>
            {{ t('views.stock_adjustment.fields.category_id') }}
          </FormLabel>
          <div class="flex items-center gap-2">
            <div class="flex-1">
              <FormSelectSearch v-model="selectedCategoryId" v-model:search="categorySearch" :options="categoryOptions"
                :placeholder="t('components.dropdown.placeholder')" @change="handleCategoryFilterChange"
                @search="loadCategoryDDL" @clear="clearCategoryFilter" />
            </div>
          </div>
        </div>
        <div class="col-span-12 md:col-span-4 lg:col-span-2">
          <FormLabel>
            {{ t('views.stock_adjustment.fields.in_warehouse_id') }}
          </FormLabel>
          <div class="flex items-center gap-2">
            <div class="flex-1">
              <FormSelectSearch v-model="selectedInWarehouseId" v-model:search="inWarehouseSearch"
                :options="inWarehouseOptions" :placeholder="t('components.dropdown.placeholder')"
                @change="handleInWarehouseFilterChange" @search="loadInWarehouseDDL" @clear="clearInWarehouseFilter" />
            </div>
          </div>
        </div>
        <div class="col-span-12 md:col-span-4 lg:col-span-2">
          <FormLabel>
            {{ t('views.stock_adjustment.fields.out_warehouse_id') }}
          </FormLabel>
          <div class="flex items-center gap-2">
            <div class="flex-1">
              <FormSelectSearch v-model="selectedOutWarehouseId" v-model:search="outWarehouseSearch"
                :options="outWarehouseOptions" :placeholder="t('components.dropdown.placeholder')"
                @change="handleOutWarehouseFilterChange" @search="loadOutWarehouseDDL"
                @clear="clearOutWarehouseFilter" />
            </div>
          </div>
        </div>
      </div>
      <DataListFlex :data="stockAdjustmentLists" :enable-search="true" :can-print="true" :can-export="true"
        :rows="stockAdjustmentLists?.data ?? []" row-class="bg-white dark:bg-darkmode-600"
        :pagination="stockAdjustmentLists ? stockAdjustmentLists.meta : null" @dataListChanged="handleDataListChange">
        <template #row="{ item, index }">
          <div class="col-span-12 lg:col-span-5 md:col-span-4 self-start">
            <div class="text-primary text-xs font-semibold uppercase tracking-wide mb-1">
              {{ t('views.stock_adjustment.page_title') }}
            </div>
            <div class="text-slate-500 text-xs whitespace-nowrap">
              {{ t('views.stock_adjustment.fields.code') }}:
              {{ (item as StockAdjustment).code ?? '-' }}
            </div>
            <div class="text-slate-500 text-xs whitespace-nowrap">
              {{ t('views.stock_adjustment.fields.date') }}:
              {{ (item as StockAdjustment).date ? formatDate((item as StockAdjustment).date, 'DD-MMM-YYYY HH:mm:ss') :
              '-' }}
            </div>
            <div class="text-slate-500 text-xs whitespace-nowrap">
              {{ t('views.stock_adjustment.fields.category_id') }}:
              {{ (item as StockAdjustment).category?.name ?? '-' }}
            </div>
            <div v-if="(item as StockAdjustment).remarks?.trim()" class="text-slate-500 text-xs">
              {{ t('views.stock_adjustment.fields.remarks') }}:
              {{ (item as StockAdjustment).remarks }}
            </div>
          </div>

          <div class="col-span-12 lg:col-span-4 md:col-span-4 self-start">
            <div class="text-primary text-xs font-semibold uppercase tracking-wide mb-1">
              {{ t('views.warehouse.page_title') }}
            </div>
            <div class="text-slate-500 text-xs whitespace-nowrap">
              {{ t('views.stock_adjustment.fields.in_warehouse_id') }}:
              {{ (item as StockAdjustment).in_warehouse?.name ?? '-' }}
            </div>
            <div class="text-slate-500 text-xs whitespace-nowrap">
              {{ t('views.stock_adjustment.fields.out_warehouse_id') }}:
              {{ (item as StockAdjustment).out_warehouse?.name ?? '-' }}
            </div>
          </div>

          <div class="col-span-12 lg:col-span-2 md:col-span-2 self-start">
            <div class="text-primary text-xs font-semibold uppercase tracking-wide mb-1">
              {{ t('views.stock_adjustment.fields.is_posted') }}
            </div>
            <div class="text-slate-500 text-xs whitespace-nowrap">
              <span class="inline-flex items-center">
                <Lucide v-if="(item as StockAdjustment).is_posted" icon="CheckCircle" class="w-4 h-4 text-success" />
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
            <Button size="sm" variant="outline-secondary" class="flex items-center gap-1"
              @click="deleteSelected(index)">
              <Lucide icon="Trash2" class="w-4 h-4 text-danger" />
            </Button>
          </div>

          <div v-if="expandDetail === index"
            class="col-span-12 border-t border-slate-200 dark:border-darkmode-400 mt-2 pt-3">
            <div class="text-primary text-xs font-semibold uppercase tracking-wide mb-1">
              {{ t('views.stock_adjustment.field_groups.in_items') }}
            </div>
            <div v-if="((item as StockAdjustment).in_items?.length ?? 0) === 0"
              class="text-slate-500 text-xs italic">
              {{ t('components.data-list.data_not_found') }}
            </div>
            <div v-else class="space-y-2 text-xs mb-3">
              <div v-for="(p, productIdx) in (item as StockAdjustment).in_items" :key="p.ulid" class="flex gap-2">
                <div class="w-6 text-right text-slate-500">{{ productIdx + 1 }}.</div>
                <div class="flex-1">
                  <div class="font-medium break-all">
                    [{{ p.product_unit.code }}] {{ p.product_unit.product.name }}
                  </div>
                  <div class="text-slate-500">
                    {{ t('views.stock_adjustment_in_item.fields.qty') }}:
                    <span class="font-medium">{{ formatCurrency(p.qty) }} {{ p.product_unit.unit.name }}</span>
                  </div>
                  <div v-if="p.product_unit?.product?.is_use_serial_number" class="text-slate-500 mt-0.5">
                    {{ t('views.product.fields.serial_number') }}:
                    <span v-if="p.serials?.length" class="break-all">{{p.serials.map((s) => s.serial).join(', ')
                      }}</span>
                    <span v-else>{{ t('components.data-list.data_not_found') }}</span>
                  </div>
                </div>
              </div>
            </div>
            <div class="text-primary text-xs font-semibold uppercase tracking-wide mb-1">
              {{ t('views.stock_adjustment.field_groups.out_items') }}
            </div>
            <div v-if="((item as StockAdjustment).out_items?.length ?? 0) === 0"
              class="text-slate-500 text-xs italic">
              {{ t('components.data-list.data_not_found') }}
            </div>
            <div v-else class="space-y-2 text-xs">
              <div v-for="(p, productIdx) in (item as StockAdjustment).out_items" :key="p.ulid" class="flex gap-2">
                <div class="w-6 text-right text-slate-500">{{ productIdx + 1 }}.</div>
                <div class="flex-1">
                  <div class="font-medium break-all">
                    [{{ p.product_unit.code }}] {{ p.product_unit.product.name }}
                  </div>
                  <div class="text-slate-500">
                    {{ t('views.stock_adjustment_in_item.fields.qty') }}:
                    <span class="font-medium">{{ formatCurrency(p.qty) }} {{ p.product_unit.unit.name }}</span>
                  </div>
                  <div v-if="p.product_unit?.product?.is_use_serial_number" class="text-slate-500 mt-0.5">
                    {{ t('views.product.fields.serial_number') }}:
                    <span v-if="p.serials?.length" class="break-all">{{p.serials.map((s) => s.serial).join(', ')
                      }}</span>
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
  <!-- delete confirmation modal -->
  <Dialog :open="deleteModalShow" @close="
    () => {
      deleteModalShow = false;
    }
  ">
    <Dialog.Panel>
      <!-- modal content -->
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
      <!-- modal actions -->
      <div class="px-5 pb-8 text-center">
        <Button type="button" variant="outline-secondary" @click="
          () => {
            deleteModalShow = false;
          }
        " class="w-24 mr-1">
          {{ t('components.buttons.cancel') }}
        </Button>
        <Button type="button" variant="danger" class="w-24" @click="confirmDelete">
          {{ t('components.buttons.delete') }}
        </Button>
      </div>
    </Dialog.Panel>
  </Dialog>
</template>
