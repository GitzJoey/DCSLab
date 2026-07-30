<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import { Dialog } from '@/components/Base/Headless';
import { FormInputDateTime, FormLabel, FormSelectSearch } from '@/components/Base/Form';
import { DataListFlex } from '@/components/DataList';
import Button from '@/components/Base/Button';
import Lucide from '@/components/Base/Lucide';
import CustomerService from '@/services/CustomerService';
import SalesOrderDeliveryService from '@/services/SalesOrderDeliveryService';
import SalesOrderService from '@/services/SalesOrderService';
import WarehouseService from '@/services/WarehouseService';
import { useSelectedUserLocationStore } from '@/stores/selected-user-location';
import { ErrorCode } from '@/types/enums/ErrorCode';
import { ViewMode } from '@/types/enums/ViewMode';
import type { SalesOrderDelivery } from '@/types/models/SalesOrderDelivery';
import type { DropDownOption } from '@/types/models/DropDownOption';
import type { NotificationData } from '@/types/models/NotificationData';
import type { Collection } from '@/types/resources/Collection';
import type { DataListEmittedData } from '@/components/DataList/DataList.vue';
import type { SalesOrderDeliveryReadAnyPaginateRequest } from '@/types/services/sales-order-delivery/SalesOrderDeliveryRequest';
import type { ServiceResponse } from '@/types/services/ServiceResponse';
import type { AlertPlaceholderProps } from '@/components/AlertPlaceholder/AlertPlaceholder.vue';
import { formatCurrency, formatDate } from '@/utils/helper';

const { t } = useI18n();
const router = useRouter();
const salesOrderDeliveryService = new SalesOrderDeliveryService();
const salesOrderService = new SalesOrderService();
const customerService = new CustomerService();
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
const selectedCustomerId = ref<string | null>(null);
const selectedSalesOrderId = ref<string | null>(null);
const selectedWarehouseId = ref<string | null>(null);
const selectedIsPosted = ref<string | null>(null);

const salesOrderDeliveryLists = ref<Collection<Array<SalesOrderDelivery>> | null>({
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

const customerDDL = ref<Array<DropDownOption> | null>(null);
const customerSearch = ref<string>('');
const customerOptions = computed(() =>
  (customerDDL.value ?? []).map((item) => ({
    value: item.code,
    label: item.name,
  })),
);

const salesOrderDDL = ref<Array<DropDownOption> | null>(null);
const salesOrderSearch = ref<string>('');
const salesOrderOptions = computed(() =>
  (salesOrderDDL.value ?? []).map((item) => ({
    value: item.code,
    label: item.name,
  })),
);

const warehouseDDL = ref<Array<DropDownOption> | null>(null);
const warehouseSearch = ref<string>('');
const warehouseOptions = computed(() =>
  (warehouseDDL.value ?? []).map((item) => ({
    value: item.code,
    label: item.name,
  })),
);

const postedOptions = computed(() => [
  { value: 'true', label: t('components.buttons.yes') },
  { value: 'false', label: t('components.buttons.no') },
]);

const formatQuantityValue = (value: number | string | null | undefined, precision = 4) =>
  new Intl.NumberFormat('id-ID', {
    minimumFractionDigits: 0,
    maximumFractionDigits: precision,
  }).format(Number(value ?? 0));

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

  await Promise.all([loadCustomerDDL(), loadSalesOrderDDL(), loadWarehouseDDL()]);
  await getSalesOrderDeliveries('', true, 1, 10);
});

const getSalesOrderDeliveries = async (search: string, refresh: boolean, page: number, perPage: number) => {
  emits('loading-state', true);
  searchText.value = search;

  const request: SalesOrderDeliveryReadAnyPaginateRequest = {
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    branch_id: selectedUserLocation.value.branch.id,
    search,
    start_date: startDate.value,
    end_date: endDate.value,
    customer_id: selectedCustomerId.value,
    sales_order_id: selectedSalesOrderId.value,
    warehouse_id: selectedWarehouseId.value,
    is_posted: selectedIsPosted.value === null ? null : selectedIsPosted.value === 'true',
    refresh,
    page,
    per_page: perPage,
  };

  const result = (await salesOrderDeliveryService.readAnyPaginate(
    request,
  )) as ServiceResponse<Collection<Array<SalesOrderDelivery>> | null>;

  if (result.success && result.data) {
    salesOrderDeliveryLists.value = result.data;
    showAlertPlaceholder('hidden', '', null);
  } else {
    showAlertPlaceholder('danger', '', result.errors as Record<string, Array<string>>);
  }

  emits('loading-state', false);
};

const loadCustomerDDL = async (search = '') => {
  if (!selectedUserLocation.value) return;

  const result = await customerService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    search,
    status: undefined,
    include_id: selectedCustomerId.value ?? undefined,
    refresh: false,
    limit: 100,
  });

  if (result.success && result.data) {
    customerDDL.value = result.data.data.map((item: any) => ({
      code: item.id,
      name: item.name,
    }));
  }
};

const loadSalesOrderDDL = async (search = '') => {
  if (!selectedUserLocation.value) return;

  const result = await salesOrderService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    branch_id: selectedUserLocation.value.branch.id,
    search,
    start_date: null,
    end_date: null,
    customer_id: selectedCustomerId.value,
    refresh: false,
    limit: 100,
  });

  if (result.success && result.data) {
    salesOrderDDL.value = result.data.data.map((item) => ({
      code: item.id,
      name: `${item.code} - ${item.customer?.name ?? '-'}`,
    }));
  }
};

const loadWarehouseDDL = async (search = '') => {
  if (!selectedUserLocation.value) return;

  const result = await warehouseService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    branch_id: selectedUserLocation.value.branch.id,
    search,
    include_id: selectedWarehouseId.value ?? undefined,
    status: undefined,
    refresh: false,
    limit: 100,
  });

  if (result.success && result.data) {
    warehouseDDL.value = result.data.data.map((item: any) => ({
      code: item.id,
      name: item.name,
    }));
  }
};

const reloadList = async () => {
  await getSalesOrderDeliveries(searchText.value, true, 1, salesOrderDeliveryLists.value?.meta.per_page ?? 10);
};

const clearCustomerFilter = async () => {
  selectedCustomerId.value = null;
  await loadSalesOrderDDL();
  await reloadList();
};

const clearSalesOrderFilter = async () => {
  selectedSalesOrderId.value = null;
  await reloadList();
};

const clearWarehouseFilter = async () => {
  selectedWarehouseId.value = null;
  await reloadList();
};

const handleDataListChange = async (emittedData: DataListEmittedData) => {
  await getSalesOrderDeliveries(
    emittedData.search.text,
    true,
    emittedData.pagination.page,
    emittedData.pagination.per_page,
  );
};

const viewSelected = (index: number) => {
  expandDetail.value = expandDetail.value === index ? null : index;
};

const editSelected = (index: number) => {
  if (!salesOrderDeliveryLists.value?.data?.[index]) return;

  router.push({
    name: 'side-menu-sales-delivery-edit',
    params: { ulid: salesOrderDeliveryLists.value.data[index].ulid },
  });
};

const deleteSelected = (index: number) => {
  if (!salesOrderDeliveryLists.value?.data?.[index]) return;

  deleteUlid.value = salesOrderDeliveryLists.value.data[index].ulid;
  deleteModalShow.value = true;
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

const showNotification = (title: string, content: string) => {
  const notification: NotificationData = {
    title,
    content,
  };

  emits('show-notification', notification);
};

const confirmDelete = async () => {
  emits('loading-state', true);
  const result = await salesOrderDeliveryService.delete(deleteUlid.value);
  emits('loading-state', false);
  deleteModalShow.value = false;

  if (result.success) {
    showAlertPlaceholder('hidden', '', null);
    emits('update-profile');
    showNotification(
      t('views.sales_order_delivery.alert.delete.title'),
      t('views.sales_order_delivery.alert.delete.message'),
    );
    await getSalesOrderDeliveries(
      searchText.value,
      true,
      salesOrderDeliveryLists.value?.meta.current_page ?? 1,
      salesOrderDeliveryLists.value?.meta.per_page ?? 10,
    );
  } else {
    showAlertPlaceholder('danger', '', result.errors as Record<string, Array<string>>);
  }
};
</script>

<template>
  <div class="mt-5 grid grid-cols-12 gap-6">
    <div class="col-span-12">
      <div class="mb-5 grid grid-cols-12 gap-4">
        <div class="col-span-12 md:col-span-3">
          <FormLabel>{{ t('views.sales_order_delivery.fields.start_date') }}</FormLabel>
          <FormInputDateTime
            v-model="startDate"
            @change="getSalesOrderDeliveries(searchText, true, 1, salesOrderDeliveryLists?.meta.per_page ?? 10)"
          />
        </div>
        <div class="col-span-12 md:col-span-3">
          <FormLabel>{{ t('views.sales_order_delivery.fields.end_date') }}</FormLabel>
          <FormInputDateTime
            v-model="endDate"
            @change="getSalesOrderDeliveries(searchText, true, 1, salesOrderDeliveryLists?.meta.per_page ?? 10)"
          />
        </div>
        <div class="col-span-12 md:col-span-3">
          <FormLabel>{{ t('views.sales_order_delivery.fields.customer_id') }}</FormLabel>
          <FormSelectSearch
            v-model="selectedCustomerId"
            v-model:search="customerSearch"
            :options="customerOptions"
            :placeholder="t('components.dropdown.placeholder')"
            @change="async () => { await loadSalesOrderDDL(); await getSalesOrderDeliveries(searchText, true, 1, salesOrderDeliveryLists?.meta.per_page ?? 10); }"
            @search="loadCustomerDDL"
            @clear="clearCustomerFilter"
          />
        </div>
        <div class="col-span-12 md:col-span-3">
          <FormLabel>{{ t('views.sales_order_delivery.fields.sales_order_id') }}</FormLabel>
          <FormSelectSearch
            v-model="selectedSalesOrderId"
            v-model:search="salesOrderSearch"
            :options="salesOrderOptions"
            :placeholder="t('components.dropdown.placeholder')"
            @change="getSalesOrderDeliveries(searchText, true, 1, salesOrderDeliveryLists?.meta.per_page ?? 10)"
            @search="loadSalesOrderDDL"
            @clear="clearSalesOrderFilter"
          />
        </div>
        <div class="col-span-12 md:col-span-3">
          <FormLabel>{{ t('views.sales_order_delivery.fields.warehouse_id') }}</FormLabel>
          <FormSelectSearch
            v-model="selectedWarehouseId"
            v-model:search="warehouseSearch"
            :options="warehouseOptions"
            :placeholder="t('components.dropdown.placeholder')"
            @change="getSalesOrderDeliveries(searchText, true, 1, salesOrderDeliveryLists?.meta.per_page ?? 10)"
            @search="loadWarehouseDDL"
            @clear="clearWarehouseFilter"
          />
        </div>
        <div class="col-span-12 md:col-span-3">
          <FormLabel>{{ t('views.sales_order_delivery.fields.is_posted') }}</FormLabel>
          <FormSelectSearch
            v-model="selectedIsPosted"
            :options="postedOptions"
            :placeholder="t('components.dropdown.placeholder')"
            @change="getSalesOrderDeliveries(searchText, true, 1, salesOrderDeliveryLists?.meta.per_page ?? 10)"
            @clear="getSalesOrderDeliveries(searchText, true, 1, salesOrderDeliveryLists?.meta.per_page ?? 10)"
          />
        </div>
      </div>

      <DataListFlex
        :data="salesOrderDeliveryLists"
        :title="t('views.sales_order_delivery.table.title')"
        :enable-search="true"
        :can-print="true"
        :can-export="true"
        :rows="salesOrderDeliveryLists?.data ?? []"
        row-class="bg-white dark:bg-darkmode-600"
        :pagination="salesOrderDeliveryLists ? salesOrderDeliveryLists.meta : null"
        @dataListChanged="handleDataListChange"
      >
        <template #row="{ item, index }">
          <div class="col-span-12 self-start lg:col-span-5">
            <div class="space-y-2">
              <div class="text-xs font-semibold uppercase tracking-wide text-primary">
                {{ t('views.sales_order_delivery.page_title') }}
              </div>
              <div class="grid grid-cols-12 items-center gap-x-3 gap-y-2 text-xs">
                <div class="col-span-4 text-slate-500">{{ t('views.sales_order_delivery.fields.code') }}</div>
                <div class="col-span-8 break-words text-slate-700 dark:text-slate-200">{{ (item as SalesOrderDelivery).code ?? '-' }}</div>
                <div class="col-span-4 text-slate-500">{{ t('views.sales_order_delivery.fields.date') }}</div>
                <div class="col-span-8 break-words text-slate-700 dark:text-slate-200">
                  {{ (item as SalesOrderDelivery).date ? formatDate((item as SalesOrderDelivery).date, 'DD-MMM-YYYY HH:mm:ss') : '-' }}
                </div>
                <div class="col-span-4 text-slate-500">{{ t('views.sales_order_delivery.fields.customer_id') }}</div>
                <div class="col-span-8 break-words text-slate-700 dark:text-slate-200">{{ (item as SalesOrderDelivery).customer?.name ?? '-' }}</div>
                <div class="col-span-4 text-slate-500">{{ t('views.sales_order_delivery.fields.sales_order_id') }}</div>
                <div class="col-span-8 break-words text-slate-700 dark:text-slate-200">{{ (item as SalesOrderDelivery).sales_order?.code ?? '-' }}</div>
                <div class="col-span-4 text-slate-500">{{ t('views.sales_order_delivery.fields.warehouse_id') }}</div>
                <div class="col-span-8 break-words text-slate-700 dark:text-slate-200">{{ (item as SalesOrderDelivery).warehouse?.name ?? '-' }}</div>
              </div>
            </div>
          </div>

          <div class="col-span-12 self-start lg:col-span-3">
            <div class="space-y-2">
              <div class="text-xs font-semibold uppercase tracking-wide text-primary">
                {{ t('views.sales_order_delivery.field_groups.summary') }}
              </div>
              <div class="grid grid-cols-12 items-center gap-x-3 gap-y-2 text-xs">
                <div class="col-span-7 text-slate-500">{{ t('views.sales_order_delivery.fields.is_posted') }}</div>
                <div class="col-span-5 text-right text-slate-700 dark:text-slate-200">
                  {{ (item as SalesOrderDelivery).is_posted ? t('components.buttons.yes') : t('components.buttons.no') }}
                </div>
                <div class="col-span-7 text-slate-500">{{ t('views.sales_order_delivery.fields.item_count') }}</div>
                <div class="col-span-5 text-right text-slate-700 dark:text-slate-200">{{ (item as SalesOrderDelivery).items?.length ?? 0 }}</div>
                <div class="col-span-7 text-slate-500">{{ t('views.sales_order_delivery.fields.cost_count') }}</div>
                <div class="col-span-5 text-right text-slate-700 dark:text-slate-200">{{ (item as SalesOrderDelivery).costs?.length ?? 0 }}</div>
                <div class="col-span-7 text-slate-500">{{ t('views.sales_order_delivery.fields.total_cogs') }}</div>
                <div class="col-span-5 text-right text-slate-700 dark:text-slate-200">{{ formatCurrency((item as SalesOrderDelivery).total_cogs ?? 0) }}</div>
                <div class="col-span-7 text-primary font-medium">{{ t('views.sales_order_delivery.fields.total_cost') }}</div>
                <div class="col-span-5 text-right text-primary font-medium">{{ formatCurrency((item as SalesOrderDelivery).total_cost ?? 0) }}</div>
              </div>
            </div>
          </div>

          <div class="col-span-12 self-start lg:col-span-3">
            <div class="space-y-2">
              <div class="text-xs font-semibold uppercase tracking-wide text-primary">
                {{ t('views.sales_order_delivery.field_groups.remarks') }}
              </div>
              <div class="rounded-md border border-slate-200/70 p-3 text-xs text-slate-700 dark:border-darkmode-400 dark:text-slate-200">
                {{ (item as SalesOrderDelivery).remarks?.trim() || '-' }}
              </div>
            </div>
          </div>

          <div class="col-span-12 flex items-center justify-end gap-2 self-center pt-2 md:col-span-1 md:flex-col lg:col-span-1 lg:flex-col">
            <Button size="sm" variant="outline-secondary" class="flex items-center gap-1" @click="viewSelected(index)">
              <Lucide icon="Info" class="h-4 w-4" />
            </Button>
            <Button size="sm" variant="outline-secondary" class="flex items-center gap-1" @click="editSelected(index)">
              <Lucide icon="Pen" class="h-4 w-4" />
            </Button>
            <Button size="sm" variant="outline-secondary" class="flex items-center gap-1" @click="deleteSelected(index)">
              <Lucide icon="Trash2" class="h-4 w-4 text-danger" />
            </Button>
          </div>

          <div v-if="expandDetail === index" class="col-span-12 mt-2 border-t border-slate-200 pt-3 dark:border-darkmode-400">
            <div class="grid grid-cols-12 gap-4">
              <div class="col-span-12 space-y-3 lg:col-span-6">
                <div class="text-xs font-semibold uppercase tracking-wide text-primary">
                  {{ t('views.sales_order_delivery.field_groups.items') }}
                </div>
                <div v-if="!(item as SalesOrderDelivery).items?.length" class="text-xs text-slate-500">
                  {{ t('views.sales_order_delivery.fields.items_empty') }}
                </div>
                <div
                  v-for="(deliveryItem, itemIndex) in (item as SalesOrderDelivery).items ?? []"
                  :key="deliveryItem.ulid ?? `${(item as SalesOrderDelivery).ulid}-item-${itemIndex}`"
                  class="rounded-md border border-slate-200/70 p-3 dark:border-darkmode-400"
                >
                  <div class="grid grid-cols-12 gap-x-3 gap-y-2 text-xs">
                    <div class="col-span-12 font-medium text-slate-700 dark:text-slate-200">
                      {{ deliveryItem.product_unit?.product?.name ?? deliveryItem.product_unit?.code ?? '-' }}
                    </div>
                    <div class="col-span-4 text-slate-500">{{ t('views.sales_order_delivery.fields.product_unit_id') }}</div>
                    <div class="col-span-8 text-slate-700 dark:text-slate-200">{{ deliveryItem.product_unit?.code ?? '-' }}</div>
                    <div class="col-span-4 text-slate-500">{{ t('views.sales_order_delivery.fields.qty') }}</div>
                    <div class="col-span-8 text-slate-700 dark:text-slate-200">{{ formatQuantityValue(deliveryItem.qty) }}</div>
                    <div class="col-span-4 text-slate-500">{{ t('views.sales_order_delivery.fields.product_unit_conversion_value') }}</div>
                    <div class="col-span-8 text-slate-700 dark:text-slate-200">{{ formatQuantityValue(deliveryItem.product_unit_conversion_value) }}</div>
                    <div class="col-span-4 text-slate-500">{{ t('views.sales_order_delivery.fields.base_unit_cogs') }}</div>
                    <div class="col-span-8 text-slate-700 dark:text-slate-200">{{ formatCurrency(Number(deliveryItem.base_unit_cogs ?? 0)) }}</div>
                    <div class="col-span-4 text-slate-500">{{ t('views.sales_order_delivery.fields.total_cogs') }}</div>
                    <div class="col-span-8 text-slate-700 dark:text-slate-200">{{ formatCurrency(Number(deliveryItem.total_cogs ?? 0)) }}</div>
                    <div class="col-span-4 text-slate-500">{{ t('views.sales_order_delivery.fields.serial_count') }}</div>
                    <div class="col-span-8 text-slate-700 dark:text-slate-200">{{ deliveryItem.serials?.length ?? 0 }}</div>
                    <div class="col-span-4 text-slate-500">{{ t('views.sales_order_delivery.fields.remarks') }}</div>
                    <div class="col-span-8 break-words text-slate-700 dark:text-slate-200">{{ deliveryItem.remarks?.trim() || '-' }}</div>
                  </div>
                </div>
              </div>

              <div class="col-span-12 space-y-3 lg:col-span-6">
                <div class="text-xs font-semibold uppercase tracking-wide text-primary">
                  {{ t('views.sales_order_delivery.field_groups.costs') }}
                </div>
                <div v-if="!(item as SalesOrderDelivery).costs?.length" class="text-xs text-slate-500">
                  {{ t('views.sales_order_delivery.fields.costs_empty') }}
                </div>
                <div
                  v-for="(cost, costIndex) in (item as SalesOrderDelivery).costs ?? []"
                  :key="cost.ulid ?? `${(item as SalesOrderDelivery).ulid}-cost-${costIndex}`"
                  class="rounded-md border border-slate-200/70 p-3 dark:border-darkmode-400"
                >
                  <div class="grid grid-cols-12 gap-x-3 gap-y-2 text-xs">
                    <div class="col-span-4 text-slate-500">{{ t('views.sales_order_delivery.fields.code') }}</div>
                    <div class="col-span-8 text-slate-700 dark:text-slate-200">{{ cost.code ?? '-' }}</div>
                    <div class="col-span-4 text-slate-500">{{ t('views.sales_order_delivery.fields.name') }}</div>
                    <div class="col-span-8 break-words text-slate-700 dark:text-slate-200">{{ cost.name ?? '-' }}</div>
                    <div class="col-span-4 text-slate-500">{{ t('views.sales_order_delivery.fields.cash_account_id') }}</div>
                    <div class="col-span-8 text-slate-700 dark:text-slate-200">{{ cost.cash_account?.name ?? '-' }}</div>
                    <div class="col-span-4 text-slate-500">{{ t('views.sales_order_delivery.fields.amount') }}</div>
                    <div class="col-span-8 text-right text-slate-700 dark:text-slate-200">{{ formatCurrency(cost.amount ?? 0) }}</div>
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
        <Lucide icon="XCircle" class="mx-auto mt-3 h-16 w-16 text-danger" />
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
        <Button variant="outline-secondary" type="button" class="mr-1 w-24" @click="deleteModalShow = false">
          {{ t('components.buttons.cancel') }}
        </Button>
        <Button variant="danger" type="button" class="w-24" @click="confirmDelete">
          {{ t('components.buttons.delete') }}
        </Button>
      </div>
    </Dialog.Panel>
  </Dialog>
</template>
