<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import { Dialog } from '@/components/Base/Headless';
import { FormInputDateTime, FormLabel, FormSelectSearch } from '@/components/Base/Form';
import { DataListFlex } from '@/components/DataList';
import Button from '@/components/Base/Button';
import Lucide from '@/components/Base/Lucide';
import PurchaseInvoiceService from '@/services/PurchaseInvoiceService';
import PurchaseReturnService from '@/services/PurchaseReturnService';
import SupplierService from '@/services/SupplierService';
import WarehouseService from '@/services/WarehouseService';
import { useSelectedUserLocationStore } from '@/stores/selected-user-location';
import { ErrorCode } from '@/types/enums/ErrorCode';
import { ViewMode } from '@/types/enums/ViewMode';
import type { DataListEmittedData } from '@/components/DataList/DataList.vue';
import type { AlertPlaceholderProps } from '@/components/AlertPlaceholder/AlertPlaceholder.vue';
import type { DropDownOption } from '@/types/models/DropDownOption';
import type { NotificationData } from '@/types/models/NotificationData';
import type { PurchaseReturn } from '@/types/models/PurchaseReturn';
import type { Collection } from '@/types/resources/Collection';
import type { ServiceResponse } from '@/types/services/ServiceResponse';
import type { PurchaseReturnReadAnyPaginateRequest } from '@/types/services/purchase-return/PurchaseReturnRequest';
import { formatCurrency, formatDate } from '@/utils/helper';

const { t } = useI18n();
const router = useRouter();

const purchaseReturnService = new PurchaseReturnService();
const purchaseInvoiceService = new PurchaseInvoiceService();
const supplierService = new SupplierService();
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
const selectedSupplierId = ref<string | null>(null);
const selectedPurchaseInvoiceId = ref<string | null>(null);
const selectedWarehouseId = ref<string | null>(null);
const selectedIsSettled = ref<string | null>(null);

const purchaseReturnLists = ref<Collection<Array<PurchaseReturn>> | null>({
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

const supplierDDL = ref<Array<DropDownOption> | null>(null);
const supplierSearch = ref<string>('');
const supplierOptions = computed(() =>
  (supplierDDL.value ?? []).map((item) => ({
    value: item.code,
    label: item.name,
  })),
);

const purchaseInvoiceDDL = ref<Array<DropDownOption> | null>(null);
const purchaseInvoiceSearch = ref<string>('');
const purchaseInvoiceOptions = computed(() =>
  (purchaseInvoiceDDL.value ?? []).map((item) => ({
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

const booleanOptions = computed(() => [
  { value: 'true', label: t('components.buttons.yes') },
  { value: 'false', label: t('components.buttons.no') },
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

  await Promise.all([loadSupplierDDL(), loadPurchaseInvoiceDDL(), loadWarehouseDDL()]);
  await getPurchaseReturns('', true, 1, 10);
});

const getPurchaseReturns = async (search: string, refresh: boolean, page: number, perPage: number) => {
  emits('loading-state', true);
  searchText.value = search;

  const request: PurchaseReturnReadAnyPaginateRequest = {
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    branch_id: selectedUserLocation.value.branch.id,
    search,
    start_date: startDate.value,
    end_date: endDate.value,
    supplier_id: selectedSupplierId.value,
    purchase_invoice_id: selectedPurchaseInvoiceId.value,
    warehouse_id: selectedWarehouseId.value,
    is_settled: selectedIsSettled.value === null ? null : selectedIsSettled.value === 'true',
    refresh,
    page,
    per_page: perPage,
  };

  const result = (await purchaseReturnService.readAnyPaginate(request)) as ServiceResponse<Collection<
    Array<PurchaseReturn>
  > | null>;

  if (result.success && result.data) {
    purchaseReturnLists.value = result.data;
    showAlertPlaceholder('hidden', '', null);
  } else {
    showAlertPlaceholder('danger', '', result.errors as Record<string, Array<string>>);
  }

  emits('loading-state', false);
};

const loadSupplierDDL = async (search = '') => {
  if (!selectedUserLocation.value) return;

  const result = await supplierService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    search,
    status: undefined,
    include_id: selectedSupplierId.value ?? undefined,
    refresh: false,
    limit: 100,
  });

  if (result.success && result.data) {
    supplierDDL.value = result.data.data.map((item: any) => ({
      code: item.id,
      name: item.name,
    }));
  }
};

const loadPurchaseInvoiceDDL = async (search = '') => {
  if (!selectedUserLocation.value) return;

  const result = await purchaseInvoiceService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    branch_id: selectedUserLocation.value.branch.id,
    search,
    supplier_id: selectedSupplierId.value,
    include_id: selectedPurchaseInvoiceId.value ?? undefined,
    refresh: false,
    limit: 100,
  });

  if (result.success && result.data) {
    purchaseInvoiceDDL.value = result.data.data.map((item) => ({
      code: item.id,
      name: `${item.code} - ${item.supplier?.name ?? '-'}`,
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
  await getPurchaseReturns(searchText.value, true, 1, purchaseReturnLists.value?.meta.per_page ?? 10);
};

const clearSupplierFilter = async () => {
  selectedSupplierId.value = null;
  await loadPurchaseInvoiceDDL();
  await reloadList();
};

const clearPurchaseInvoiceFilter = async () => {
  selectedPurchaseInvoiceId.value = null;
  await reloadList();
};

const clearWarehouseFilter = async () => {
  selectedWarehouseId.value = null;
  await reloadList();
};

const handleDataListChange = async (emittedData: DataListEmittedData) => {
  await getPurchaseReturns(emittedData.search.text, true, emittedData.pagination.page, emittedData.pagination.per_page);
};

const viewSelected = (index: number) => {
  expandDetail.value = expandDetail.value === index ? null : index;
};

const editSelected = (index: number) => {
  if (!purchaseReturnLists.value?.data?.[index]) return;

  router.push({
    name: 'side-menu-purchase-return-edit',
    params: { ulid: purchaseReturnLists.value.data[index].ulid },
  });
};

const deleteSelected = (index: number) => {
  if (!purchaseReturnLists.value?.data?.[index]) return;

  deleteUlid.value = purchaseReturnLists.value.data[index].ulid;
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
  const result = await purchaseReturnService.delete(deleteUlid.value);
  emits('loading-state', false);
  deleteModalShow.value = false;

  if (result.success) {
    showAlertPlaceholder('hidden', '', null);
    emits('update-profile');
    showNotification(t('views.purchase_return.alert.delete.title'), t('views.purchase_return.alert.delete.message'));
    await getPurchaseReturns(
      searchText.value,
      true,
      purchaseReturnLists.value?.meta.current_page ?? 1,
      purchaseReturnLists.value?.meta.per_page ?? 10,
    );
  } else {
    showAlertPlaceholder('danger', '', result.errors as Record<string, Array<string>>);
  }
};

const formatCurrencyRounded = (value: number | string | null | undefined, precision = 2) =>
  formatCurrency(Number(Number(value ?? 0).toFixed(precision)));

const formatQuantityValue = (value: number | string | null | undefined, precision = 4) =>
  new Intl.NumberFormat('id-ID', {
    minimumFractionDigits: 0,
    maximumFractionDigits: precision,
  }).format(Number(value ?? 0));
</script>

<template>
  <div class="mt-5 grid grid-cols-12 gap-6">
    <div class="col-span-12">
      <div class="mb-5 grid grid-cols-12 gap-4">
        <div class="col-span-12 md:col-span-6 lg:col-span-3">
          <FormLabel>{{ t('views.purchase_return.fields.start_date') }}</FormLabel>
          <FormInputDateTime v-model="startDate" @change="reloadList" />
        </div>
        <div class="col-span-12 md:col-span-6 lg:col-span-3">
          <FormLabel>{{ t('views.purchase_return.fields.end_date') }}</FormLabel>
          <FormInputDateTime v-model="endDate" @change="reloadList" />
        </div>
        <div class="col-span-12 md:col-span-6 lg:col-span-3">
          <FormLabel>{{ t('views.purchase_return.fields.supplier_id') }}</FormLabel>
          <FormSelectSearch
            v-model="selectedSupplierId"
            v-model:search="supplierSearch"
            :options="supplierOptions"
            :placeholder="t('components.dropdown.placeholder')"
            @change="
              async () => {
                await loadPurchaseInvoiceDDL();
                await reloadList();
              }
            "
            @search="loadSupplierDDL"
            @clear="clearSupplierFilter"
          />
        </div>
        <div class="col-span-12 md:col-span-6 lg:col-span-3">
          <FormLabel>{{ t('views.purchase_return.fields.purchase_invoice_id') }}</FormLabel>
          <FormSelectSearch
            v-model="selectedPurchaseInvoiceId"
            v-model:search="purchaseInvoiceSearch"
            :options="purchaseInvoiceOptions"
            :placeholder="t('components.dropdown.placeholder')"
            @change="reloadList"
            @search="loadPurchaseInvoiceDDL"
            @clear="clearPurchaseInvoiceFilter"
          />
        </div>
        <div class="col-span-12 md:col-span-6 lg:col-span-3">
          <FormLabel>{{ t('views.purchase_return.fields.warehouse_id') }}</FormLabel>
          <FormSelectSearch
            v-model="selectedWarehouseId"
            v-model:search="warehouseSearch"
            :options="warehouseOptions"
            :placeholder="t('components.dropdown.placeholder')"
            @change="reloadList"
            @search="loadWarehouseDDL"
            @clear="clearWarehouseFilter"
          />
        </div>
        <div class="col-span-12 md:col-span-6 lg:col-span-3">
          <FormLabel>{{ t('views.purchase_return.filters.is_settled') }}</FormLabel>
          <FormSelectSearch
            v-model="selectedIsSettled"
            :options="booleanOptions"
            :placeholder="t('components.dropdown.placeholder')"
            @change="reloadList"
            @clear="
              () => {
                selectedIsSettled = null;
                reloadList();
              }
            "
          />
        </div>
      </div>

      <DataListFlex
        :data="purchaseReturnLists"
        :title="t('views.purchase_return.table.title')"
        :enable-search="true"
        :can-print="true"
        :can-export="true"
        :rows="purchaseReturnLists?.data ?? []"
        row-class="bg-white dark:bg-darkmode-600"
        :pagination="purchaseReturnLists ? purchaseReturnLists.meta : null"
        @dataListChanged="handleDataListChange"
      >
        <template #row="{ item, index }">
          <div class="col-span-12 self-start lg:col-span-4">
            <div class="space-y-2">
              <div class="text-xs font-semibold uppercase tracking-wide text-primary">
                {{ t('views.purchase_return.page_title') }}
              </div>
              <div class="grid grid-cols-12 items-center gap-x-3 gap-y-2 text-xs">
                <div class="col-span-4 text-slate-500">{{ t('views.purchase_return.fields.code') }}</div>
                <div class="col-span-8 break-words text-slate-700 dark:text-slate-200">
                  {{ (item as PurchaseReturn).code ?? '-' }}
                </div>
                <div class="col-span-4 text-slate-500">{{ t('views.purchase_return.fields.date') }}</div>
                <div class="col-span-8 break-words text-slate-700 dark:text-slate-200">
                  {{
                    (item as PurchaseReturn).date
                      ? formatDate((item as PurchaseReturn).date, 'DD-MMM-YYYY HH:mm:ss')
                      : '-'
                  }}
                </div>
                <div class="col-span-4 text-slate-500">{{ t('views.purchase_return.fields.supplier_id') }}</div>
                <div class="col-span-8 break-words text-slate-700 dark:text-slate-200">
                  {{ (item as PurchaseReturn).supplier?.name ?? '-' }}
                </div>
                <div class="col-span-4 text-slate-500">{{ t('views.purchase_return.fields.purchase_invoice_id') }}</div>
                <div class="col-span-8 break-words text-slate-700 dark:text-slate-200">
                  {{ (item as PurchaseReturn).purchase_invoice?.code ?? '-' }}
                </div>
                <div class="col-span-4 text-slate-500">{{ t('views.purchase_return.fields.warehouse_id') }}</div>
                <div class="col-span-8 break-words text-slate-700 dark:text-slate-200">
                  {{ (item as PurchaseReturn).warehouse?.name ?? '-' }}
                </div>
              </div>
            </div>
          </div>

          <div class="col-span-12 self-start lg:col-span-4">
            <div class="space-y-2">
              <div class="text-xs font-semibold uppercase tracking-wide text-primary">
                {{ t('views.purchase_return.field_groups.summary') }}
              </div>
              <div class="grid grid-cols-12 items-center gap-x-3 gap-y-2 text-xs">
                <div class="col-span-7 text-slate-500">{{ t('views.purchase_return.fields.amount_payable') }}</div>
                <div class="col-span-5 text-right text-slate-700 dark:text-slate-200">
                  {{ formatCurrencyRounded((item as PurchaseReturn).amount_payable) }}
                </div>
                <div class="col-span-7 text-slate-500">
                  {{ t('views.purchase_return.fields.amount_allocated_to_invoice') }}
                </div>
                <div class="col-span-5 text-right text-slate-700 dark:text-slate-200">
                  {{ formatCurrencyRounded((item as PurchaseReturn).amount_allocated_to_invoice) }}
                </div>
                <div class="col-span-7 text-slate-500">
                  {{ t('views.purchase_return.fields.amount_received_total') }}
                </div>
                <div class="col-span-5 text-right text-slate-700 dark:text-slate-200">
                  {{ formatCurrencyRounded((item as PurchaseReturn).amount_received_total) }}
                </div>
                <div class="col-span-7 text-slate-500">
                  {{ t('views.purchase_return.fields.amount_settled_total') }}
                </div>
                <div class="col-span-5 text-right text-slate-700 dark:text-slate-200">
                  {{ formatCurrencyRounded((item as PurchaseReturn).amount_settled_total) }}
                </div>
                <div class="col-span-7 text-slate-500">{{ t('views.purchase_return.fields.amount_available') }}</div>
                <div class="col-span-5 text-right text-slate-700 dark:text-slate-200">
                  {{ formatCurrencyRounded((item as PurchaseReturn).amount_available) }}
                </div>
              </div>
            </div>
          </div>

          <div class="col-span-12 self-start lg:col-span-3">
            <div class="space-y-2">
              <div class="text-xs font-semibold uppercase tracking-wide text-primary">
                {{ t('views.purchase_return.field_groups.status') }}
              </div>
              <div class="grid grid-cols-12 items-center gap-x-3 gap-y-2 text-xs">
                <div class="col-span-7 text-slate-500">{{ t('views.purchase_return.fields.is_posted') }}</div>
                <div class="col-span-5 text-right text-slate-700 dark:text-slate-200">
                  {{ (item as PurchaseReturn).is_posted ? t('components.buttons.yes') : t('components.buttons.no') }}
                </div>
                <div class="col-span-7 text-slate-500">{{ t('views.purchase_return.fields.is_settled') }}</div>
                <div class="col-span-5 text-right text-slate-700 dark:text-slate-200">
                  {{ (item as PurchaseReturn).is_settled ? t('components.buttons.yes') : t('components.buttons.no') }}
                </div>
                <div class="col-span-7 text-slate-500">{{ t('views.purchase_return.fields.item_count') }}</div>
                <div class="col-span-5 text-right text-slate-700 dark:text-slate-200">
                  {{ (item as PurchaseReturn).items?.length ?? 0 }}
                </div>
                <div class="col-span-7 text-slate-500">{{ t('views.purchase_return.fields.refunds') }}</div>
                <div class="col-span-5 text-right text-slate-700 dark:text-slate-200">
                  {{ (item as PurchaseReturn).refunds?.length ?? 0 }}
                </div>
              </div>
            </div>
          </div>

          <div
            class="col-span-12 flex items-center justify-end gap-2 self-center pt-2 md:col-span-1 md:flex-col lg:col-span-1 lg:flex-col"
          >
            <Button size="sm" variant="outline-secondary" class="flex items-center gap-1" @click="viewSelected(index)">
              <Lucide icon="Info" class="h-4 w-4" />
            </Button>
            <Button size="sm" variant="outline-secondary" class="flex items-center gap-1" @click="editSelected(index)">
              <Lucide icon="Pen" class="h-4 w-4" />
            </Button>
            <Button
              size="sm"
              variant="outline-secondary"
              class="flex items-center gap-1"
              @click="deleteSelected(index)"
            >
              <Lucide icon="Trash2" class="h-4 w-4 text-danger" />
            </Button>
          </div>

          <div
            v-if="expandDetail === index"
            class="col-span-12 mt-2 border-t border-slate-200 pt-3 dark:border-darkmode-400"
          >
            <div class="space-y-4">
              <div class="space-y-3">
                <div class="text-xs font-semibold uppercase tracking-wide text-primary">
                  {{ t('views.purchase_return.field_groups.items') }}
                </div>
                <div v-if="!(item as PurchaseReturn).items?.length" class="text-xs text-slate-500">
                  {{ t('views.purchase_return.fields.items_empty') }}
                </div>
                <div
                  v-for="(returnItem, itemIndex) in (item as PurchaseReturn).items ?? []"
                  :key="returnItem.ulid ?? `${(item as PurchaseReturn).ulid}-item-${itemIndex}`"
                  class="rounded-md border border-slate-200/70 p-3 dark:border-darkmode-400"
                >
                  <div class="grid grid-cols-12 gap-x-3 gap-y-2 text-xs">
                    <div class="col-span-12 font-medium text-slate-700 dark:text-slate-200">
                      {{ returnItem.product_unit?.product?.name ?? returnItem.product_unit?.code ?? '-' }}
                    </div>
                    <div class="col-span-4 text-slate-500">{{ t('views.purchase_return.fields.qty') }}</div>
                    <div class="col-span-8 text-slate-700 dark:text-slate-200">
                      {{ formatQuantityValue(returnItem.qty) }} {{ returnItem.product_unit?.unit?.name ?? '' }}
                    </div>
                    <div class="col-span-4 text-slate-500">
                      {{ t('views.purchase_return.fields.purchase_order_receipt_item_id') }}
                    </div>
                    <div class="col-span-8 text-slate-700 dark:text-slate-200">
                      {{ returnItem.purchase_order_receipt_item?.purchase_order_receipt?.code ?? '-' }}
                    </div>
                    <div class="col-span-4 text-slate-500">{{ t('views.purchase_return.fields.vat') }}</div>
                    <div class="col-span-8 text-slate-700 dark:text-slate-200">
                      {{ formatCurrencyRounded(returnItem.vat) }}
                    </div>
                    <div class="col-span-4 text-slate-500">
                      {{ t('views.purchase_return.fields.item_amount_payable') }}
                    </div>
                    <div class="col-span-8 text-slate-700 dark:text-slate-200">
                      {{ formatCurrencyRounded(returnItem.amount_payable) }}
                    </div>
                  </div>
                </div>
              </div>

              <div class="space-y-3">
                <div class="text-xs font-semibold uppercase tracking-wide text-primary">
                  {{ t('views.purchase_return.field_groups.refunds') }}
                </div>
                <div v-if="!(item as PurchaseReturn).refunds?.length" class="text-xs text-slate-500">
                  {{ t('views.purchase_return.fields.refunds_empty') }}
                </div>
                <div
                  v-for="(refund, refundIndex) in (item as PurchaseReturn).refunds ?? []"
                  :key="refund.ulid ?? `${(item as PurchaseReturn).ulid}-refund-${refundIndex}`"
                  class="rounded-md border border-slate-200/70 p-3 dark:border-darkmode-400"
                >
                  <div class="grid grid-cols-12 gap-x-3 gap-y-2 text-xs">
                    <div class="col-span-4 text-slate-500">{{ t('views.purchase_return.fields.code') }}</div>
                    <div class="col-span-8 text-slate-700 dark:text-slate-200">{{ refund.code ?? '-' }}</div>
                    <div class="col-span-4 text-slate-500">{{ t('views.purchase_return.fields.date') }}</div>
                    <div class="col-span-8 text-slate-700 dark:text-slate-200">
                      {{ refund.date ? formatDate(refund.date, 'DD-MMM-YYYY HH:mm:ss') : '-' }}
                    </div>
                    <div class="col-span-4 text-slate-500">{{ t('views.purchase_return.fields.cash_account_id') }}</div>
                    <div class="col-span-8 text-slate-700 dark:text-slate-200">
                      {{ refund.cash_account?.name ?? '-' }}
                    </div>
                    <div class="col-span-4 text-slate-500">{{ t('views.purchase_return.fields.amount') }}</div>
                    <div class="col-span-8 text-slate-700 dark:text-slate-200">
                      {{ formatCurrencyRounded(refund.amount) }}
                    </div>
                  </div>
                </div>
              </div>

              <div class="space-y-2">
                <div class="text-xs font-semibold uppercase tracking-wide text-primary">
                  {{ t('views.purchase_return.field_groups.remarks') }}
                </div>
                <div
                  class="rounded-md border border-slate-200/70 p-3 text-xs text-slate-700 dark:border-darkmode-400 dark:text-slate-200"
                >
                  {{ (item as PurchaseReturn).remarks?.trim() || '-' }}
                </div>
              </div>
            </div>
          </div>
        </template>
      </DataListFlex>
    </div>
  </div>

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
        <Lucide icon="XCircle" class="mx-auto mt-3 h-16 w-16 text-danger" />
        <div class="mt-5 text-3xl">{{ t('components.delete-modal.title') }}</div>
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
