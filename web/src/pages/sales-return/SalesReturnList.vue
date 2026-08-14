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
import SalesReturnService from '@/services/SalesReturnService';
import WarehouseService from '@/services/WarehouseService';
import { useSelectedUserLocationStore } from '@/stores/selected-user-location';
import { ErrorCode } from '@/types/enums/ErrorCode';
import { ViewMode } from '@/types/enums/ViewMode';
import { SalesReturn } from '@/types/models/SalesReturn';
import { DropDownOption } from '@/types/models/DropDownOption';
import { NotificationData } from '@/types/models/NotificationData';
import { Collection } from '@/types/resources/Collection';
import { DataListEmittedData } from '@/components/DataList/DataList.vue';
import { SalesReturnReadAnyPaginateRequest } from '@/types/services/sales-return/SalesReturnRequest';
import { ServiceResponse } from '@/types/services/ServiceResponse';
import type { AlertPlaceholderProps } from '@/components/AlertPlaceholder/AlertPlaceholder.vue';
import { formatCurrency, formatDate } from '@/utils/helper';

const { t } = useI18n();
const router = useRouter();
const salesReturnService = new SalesReturnService();
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
const selectedWarehouseId = ref<string | null>(null);
const selectedIsSettled = ref<string | null>(null);

const formatCurrencyRounded = (value: number | string, precision = 2) =>
  formatCurrency(Number(Number(value ?? 0).toFixed(precision)));

const formatQuantityValue = (value: number | string, precision = 4) =>
  new Intl.NumberFormat('id-ID', {
    minimumFractionDigits: 0,
    maximumFractionDigits: precision,
  }).format(Number(value ?? 0));

const getSettledBadgeClass = (isSettled: boolean | null | undefined) =>
  isSettled ? 'bg-success/15 text-success' : 'bg-warning/15 text-warning';

const salesReturnLists = ref<Collection<Array<SalesReturn>> | null>({
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

const warehouseDDL = ref<Array<DropDownOption> | null>(null);
const warehouseSearch = ref<string>('');
const warehouseOptions = computed(() =>
  (warehouseDDL.value ?? []).map((item) => ({
    value: item.code,
    label: item.name,
  })),
);

const isSettledOptions = computed(() => [
  { value: 'true', label: t('views.sales_return.filters.is_settled_yes') },
  { value: 'false', label: t('views.sales_return.filters.is_settled_no') },
]);

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

  await Promise.all([loadCustomerDDL(), loadWarehouseDDL()]);
  await getSalesReturns('', true, 1, 10);
});

const getSalesReturns = async (search: string, refresh: boolean, page: number, perPage: number) => {
  emits('loading-state', true);
  searchText.value = search;

  const request: SalesReturnReadAnyPaginateRequest = {
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    branch_id: selectedUserLocation.value.branch.id,
    search,
    start_date: startDate.value,
    end_date: endDate.value,
    customer_id: selectedCustomerId.value,
    sales_invoice_id: null,
    warehouse_id: selectedWarehouseId.value,
    is_settled: selectedIsSettled.value === null ? null : selectedIsSettled.value === 'true',
    refresh,
    page,
    per_page: perPage,
  };

  const result = (await salesReturnService.readAnyPaginate(
    request,
  )) as ServiceResponse<Collection<Array<SalesReturn>> | null>;

  if (result.success && result.data) {
    salesReturnLists.value = result.data;
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

const loadWarehouseDDL = async (search = '') => {
  if (!selectedUserLocation.value) return;

  const result = await warehouseService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    branch_id: selectedUserLocation.value.branch.id,
    search,
    status: undefined,
    include_id: selectedWarehouseId.value ?? undefined,
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

const clearCustomerFilter = async () => {
  selectedCustomerId.value = null;
  await getSalesReturns(searchText.value, true, 1, salesReturnLists.value?.meta.per_page ?? 10);
};

const clearWarehouseFilter = async () => {
  selectedWarehouseId.value = null;
  await getSalesReturns(searchText.value, true, 1, salesReturnLists.value?.meta.per_page ?? 10);
};

const clearIsSettledFilter = async () => {
  selectedIsSettled.value = null;
  await getSalesReturns(searchText.value, true, 1, salesReturnLists.value?.meta.per_page ?? 10);
};

const handleDataListChange = async (emittedData: DataListEmittedData) => {
  await getSalesReturns(
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
  if (!salesReturnLists.value?.data?.[index]) return;
  router.push({
    name: 'side-menu-sales-return-edit',
    params: { ulid: salesReturnLists.value.data[index].ulid },
  });
};

const deleteSelected = (index: number) => {
  if (!salesReturnLists.value?.data?.[index]) return;
  deleteUlid.value = salesReturnLists.value.data[index].ulid;
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
  const result = await salesReturnService.delete(deleteUlid.value);
  emits('loading-state', false);
  deleteModalShow.value = false;

  if (result.success) {
    showAlertPlaceholder('hidden', '', null);
    emits('update-profile');
    showNotification(t('views.sales_return.alert.delete.title'), t('views.sales_return.alert.delete.message'));
    await getSalesReturns(searchText.value, true, salesReturnLists.value?.meta.current_page ?? 1, salesReturnLists.value?.meta.per_page ?? 10);
  } else {
    showAlertPlaceholder('danger', '', (result.errors as Record<string, Array<string>>) ?? null);
  }
};
</script>

<template>
  <div class="grid grid-cols-12 gap-6 mt-5">
    <div class="col-span-12">
      <div class="grid grid-cols-12 gap-4 mb-5">
        <div class="col-span-12 md:col-span-6 lg:col-span-3">
          <FormLabel>{{ t('views.sales_return.fields.start_date') }}</FormLabel>
          <FormInputDateTime v-model="startDate"
            @change="getSalesReturns(searchText, true, 1, salesReturnLists?.meta.per_page ?? 10)" />
        </div>
        <div class="col-span-12 md:col-span-6 lg:col-span-3">
          <FormLabel>{{ t('views.sales_return.fields.end_date') }}</FormLabel>
          <FormInputDateTime v-model="endDate"
            @change="getSalesReturns(searchText, true, 1, salesReturnLists?.meta.per_page ?? 10)" />
        </div>
        <div class="col-span-12 md:col-span-6 lg:col-span-2">
          <FormLabel>{{ t('views.sales_return.fields.customer_id') }}</FormLabel>
          <FormSelectSearch v-model="selectedCustomerId" v-model:search="customerSearch" :options="customerOptions"
            :placeholder="t('components.dropdown.placeholder')"
            @change="getSalesReturns(searchText, true, 1, salesReturnLists?.meta.per_page ?? 10)"
            @search="loadCustomerDDL" @clear="clearCustomerFilter" />
        </div>
        <div class="col-span-12 md:col-span-6 lg:col-span-2">
          <FormLabel>{{ t('views.sales_return.fields.warehouse_id') }}</FormLabel>
          <FormSelectSearch v-model="selectedWarehouseId" v-model:search="warehouseSearch" :options="warehouseOptions"
            :placeholder="t('components.dropdown.placeholder')"
            @change="getSalesReturns(searchText, true, 1, salesReturnLists?.meta.per_page ?? 10)"
            @search="loadWarehouseDDL" @clear="clearWarehouseFilter" />
        </div>
        <div class="col-span-12 md:col-span-6 lg:col-span-2">
          <FormLabel>{{ t('views.sales_return.filters.is_settled') }}</FormLabel>
          <FormSelectSearch v-model="selectedIsSettled" :options="isSettledOptions"
            :placeholder="t('components.dropdown.placeholder')"
            @change="getSalesReturns(searchText, true, 1, salesReturnLists?.meta.per_page ?? 10)"
            @clear="clearIsSettledFilter" />
        </div>
      </div>

      <DataListFlex :data="salesReturnLists" :enable-search="true" :can-print="true" :can-export="true"
        :rows="salesReturnLists?.data ?? []" row-class="bg-white dark:bg-darkmode-600"
        :pagination="salesReturnLists ? salesReturnLists.meta : null" @dataListChanged="handleDataListChange">
        <template #row="{ item, index }">
          <div class="col-span-12 md:col-span-12 lg:col-span-4 self-start">
            <div class="space-y-2">
              <div class="text-primary text-xs font-semibold uppercase tracking-wide">
                {{ t('views.sales_return.page_title') }}
              </div>
              <div class="grid grid-cols-12 items-center gap-x-3 gap-y-2 text-xs">
                <div class="col-span-4 text-slate-500">{{ t('views.sales_return.fields.code') }}</div>
                <div class="col-span-8 text-slate-700 dark:text-slate-200">{{ (item as SalesReturn).code ?? '-' }}</div>
                <div class="col-span-4 text-slate-500">{{ t('views.sales_return.fields.date') }}</div>
                <div class="col-span-8 text-slate-700 dark:text-slate-200 break-words">
                  {{ (item as SalesReturn).date ? formatDate((item as SalesReturn).date, 'DD-MMM-YYYY HH:mm:ss') : '-' }}
                </div>
                <div class="col-span-4 text-slate-500">{{ t('views.sales_return.fields.customer_id') }}</div>
                <div class="col-span-8 text-slate-700 dark:text-slate-200 break-words">
                  {{ (item as SalesReturn).customer?.name ?? '-' }}
                </div>
                <div class="col-span-4 text-slate-500">{{ t('views.sales_return.fields.sales_invoice_id') }}</div>
                <div class="col-span-8 text-slate-700 dark:text-slate-200 break-words">
                  {{ (item as SalesReturn).sales_invoice?.code ?? '-' }}
                </div>
                <div class="col-span-4 text-slate-500">{{ t('views.sales_return.fields.warehouse_id') }}</div>
                <div class="col-span-8 text-slate-700 dark:text-slate-200 break-words">
                  {{ (item as SalesReturn).warehouse?.name ?? '-' }}
                </div>
                <div class="col-span-4 text-slate-500">{{ t('views.sales_return.fields.remarks') }}</div>
                <div class="col-span-8 text-slate-700 dark:text-slate-200 break-words">
                  {{ (item as SalesReturn).remarks?.trim() || '-' }}
                </div>
              </div>
            </div>
          </div>

          <div class="col-span-12 md:col-span-6 lg:col-span-4 self-start md:pr-3 lg:pr-4">
            <div class="space-y-2">
              <div class="text-primary text-xs font-semibold uppercase tracking-wide">
                {{ t('views.sales_return.field_groups.summary') }}
              </div>
              <div class="grid grid-cols-12 items-center gap-x-3 gap-y-2 text-xs">
                <div class="col-span-7 text-slate-500">{{ t('views.sales_return.fields.item_total_before_global_discount') }}</div>
                <div class="col-span-5 text-right text-slate-700 dark:text-slate-200">
                  {{ formatCurrency((item as SalesReturn).item_total_before_global_discount ?? 0) }}
                </div>
                <div class="col-span-7 text-slate-500">{{ t('views.sales_return.fields.global_discount') }}</div>
                <div class="col-span-5 text-right text-slate-700 dark:text-slate-200">
                  {{ formatCurrency((item as SalesReturn).global_discount ?? 0) }}
                </div>
                <div class="col-span-7 text-slate-500">{{ t('views.sales_return.fields.item_total_after_global_discount') }}</div>
                <div class="col-span-5 text-right text-slate-700 dark:text-slate-200">
                  {{ formatCurrency((item as SalesReturn).item_total_after_global_discount ?? 0) }}
                </div>
                <div class="col-span-7 text-slate-500">{{ t('views.sales_return.fields.vat_base') }}</div>
                <div class="col-span-5 text-right text-slate-700 dark:text-slate-200">
                  {{ formatCurrencyRounded((item as SalesReturn).vat_base ?? 0) }}
                </div>
                <div class="col-span-7 text-slate-500">{{ t('views.sales_return.fields.vat') }}</div>
                <div class="col-span-5 text-right text-slate-700 dark:text-slate-200">
                  {{ formatCurrencyRounded((item as SalesReturn).vat ?? 0) }}
                </div>
                <div class="col-span-7 text-slate-500">{{ t('views.sales_return.fields.rounding') }}</div>
                <div class="col-span-5 text-right text-slate-700 dark:text-slate-200">
                  {{ formatCurrency((item as SalesReturn).rounding ?? 0) }}
                </div>
                <div class="col-span-4 text-primary font-medium">{{ t('views.sales_return.fields.amount_payable') }}</div>
                <div class="col-span-8 text-right text-primary font-medium">
                  {{ formatCurrency((item as SalesReturn).amount_payable ?? 0) }}
                </div>
              </div>
            </div>
          </div>

          <div class="col-span-12 md:col-span-5 lg:col-span-3 self-start md:pl-3 lg:pl-4">
            <div class="space-y-2">
              <div class="text-primary text-xs font-semibold uppercase tracking-wide">
                {{ t('views.sales_return.field_groups.refunds') }}
              </div>
              <div class="rounded-md border border-slate-200/60 dark:border-darkmode-400 px-3 py-3">
                <div class="flex items-center justify-between gap-2">
                  <span class="text-xs text-slate-500">{{ t('views.sales_return.fields.is_settled') }}</span>
                  <span class="inline-flex rounded-full px-2 py-1 text-[11px] font-medium"
                    :class="getSettledBadgeClass((item as SalesReturn).is_settled)">
                    {{ (item as SalesReturn).is_settled
                      ? t('views.sales_return.filters.is_settled_yes')
                      : t('views.sales_return.filters.is_settled_no') }}
                  </span>
                </div>
                <div class="mt-3 grid grid-cols-12 gap-x-3 gap-y-2 text-xs">
                  <div class="col-span-7 text-slate-500">{{ t('views.sales_return.fields.amount_allocated_to_invoice') }}</div>
                  <div class="col-span-5 text-right text-slate-700 dark:text-slate-200">
                    {{ formatCurrency((item as SalesReturn).amount_allocated_to_invoice ?? 0) }}
                  </div>
                  <div class="col-span-7 text-slate-500">{{ t('views.sales_return.fields.amount_received_total') }}</div>
                  <div class="col-span-5 text-right text-slate-700 dark:text-slate-200">
                    {{ formatCurrency((item as SalesReturn).amount_received_total ?? 0) }}
                  </div>
                  <div class="col-span-7 text-slate-500">{{ t('views.sales_return.fields.amount_settled_total') }}</div>
                  <div class="col-span-5 text-right text-slate-700 dark:text-slate-200">
                    {{ formatCurrency((item as SalesReturn).amount_settled_total ?? 0) }}
                  </div>
                  <div class="col-span-7 text-primary font-medium">{{ t('views.sales_return.fields.amount_available') }}</div>
                  <div class="col-span-5 text-right text-primary font-medium">
                    {{ formatCurrency((item as SalesReturn).amount_available ?? 0) }}
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div
            class="col-span-12 md:col-span-1 lg:col-span-1 self-center flex justify-end items-center gap-2 pt-2 md:flex-col lg:flex-col">
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

          <div v-if="expandDetail === index"
            class="col-span-12 border-t border-slate-200 dark:border-darkmode-400 mt-2 pt-3">
            <div class="grid grid-cols-12 gap-4">
              <div class="col-span-12 lg:col-span-6 space-y-3">
                <div class="text-primary text-xs font-semibold uppercase tracking-wide">
                  {{ t('views.sales_return.field_groups.items') }}
                </div>
                <div v-if="!(item as SalesReturn).items?.length" class="text-xs text-slate-500">
                  {{ t('components.data-list.data_not_found') }}
                </div>
                <div v-else class="space-y-3 rounded-md border border-slate-200/60 dark:border-darkmode-400 p-3">
                  <div v-for="returnItem in (item as SalesReturn).items" :key="returnItem.id"
                    class="border-b border-slate-200/60 pb-3 text-sm last:border-b-0 last:pb-0 dark:border-darkmode-400">
                    <div class="font-medium text-slate-700 dark:text-slate-200 break-words">
                      {{ returnItem.product_unit?.product?.name ?? returnItem.product?.name ?? '-' }}
                    </div>
                    <div class="mt-1 text-xs text-slate-500 break-words">
                      {{ returnItem.product_unit?.code ?? '-' }}
                    </div>
                    <div class="mt-2 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-slate-600 dark:text-slate-300">
                      <span>
                        {{ t('views.sales_return.fields.qty') }}
                        <span class="text-slate-700 dark:text-slate-200">{{ formatQuantityValue(returnItem.qty ?? 0) }}</span>
                      </span>
                      <span class="text-slate-400">|</span>
                      <span>
                        {{ t('views.sales_return.fields.unit_name') }}
                        <span class="text-slate-700 dark:text-slate-200">{{ returnItem.product_unit?.unit?.name ?? '-' }}</span>
                      </span>
                      <span class="text-slate-400">|</span>
                      <span>
                        {{ t('views.sales_return.fields.product_unit_price') }}
                        <span class="text-slate-700 dark:text-slate-200">{{ formatCurrency(returnItem.product_unit_price ?? 0) }}</span>
                      </span>
                      <span class="text-slate-400">|</span>
                      <span>
                        {{ t('views.sales_return.fields.total_cogs') }}
                        <span class="text-slate-700 dark:text-slate-200">{{ formatCurrency(returnItem.total_cogs ?? 0) }}</span>
                      </span>
                    </div>
                    <div v-if="returnItem.serials?.length" class="mt-2 text-xs text-slate-500 break-words">
                      {{ returnItem.serials.map((serial) => serial.serial).join(', ') }}
                    </div>
                    <div v-if="returnItem.remarks?.trim()" class="mt-2 text-xs text-slate-500 break-words">
                      {{ returnItem.remarks }}
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-span-12 lg:col-span-6 space-y-3">
                <div class="text-primary text-xs font-semibold uppercase tracking-wide">
                  {{ t('views.sales_return.fields.refunds') }}
                </div>
                <div v-if="!(item as SalesReturn).refunds?.length" class="text-xs text-slate-500">
                  {{ t('components.data-list.data_not_found') }}
                </div>
                <div v-else class="space-y-2">
                  <div v-for="refund in (item as SalesReturn).refunds" :key="refund.id"
                    class="rounded-md border border-slate-200/60 dark:border-darkmode-400 px-3 py-2 grid grid-cols-12 gap-3 text-xs">
                    <div class="col-span-4 text-slate-700 dark:text-slate-200">{{ refund.code }}</div>
                    <div class="col-span-4 text-slate-500 break-words">{{ refund.cash_account?.name ?? '-' }}</div>
                    <div class="col-span-4 text-right text-slate-700 dark:text-slate-200">
                      {{ formatCurrency(refund.amount ?? 0) }}
                    </div>
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
