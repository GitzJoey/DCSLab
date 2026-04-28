<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import { Dialog } from '@/components/Base/Headless';
import { FormInputDateTime, FormLabel, FormSelectSearch } from '@/components/Base/Form';
import { DataListFlex } from '@/components/DataList';
import Button from '@/components/Base/Button';
import Lucide from '@/components/Base/Lucide';
import PurchaseService from '@/services/PurchaseService';
import PurchaseOrderService from '@/services/PurchaseOrderService';
import SupplierService from '@/services/SupplierService';
import { useSelectedUserLocationStore } from '@/stores/selected-user-location';
import { ErrorCode } from '@/types/enums/ErrorCode';
import { ViewMode } from '@/types/enums/ViewMode';
import type { Purchase } from '@/types/models/Purchase';
import type { DropDownOption } from '@/types/models/DropDownOption';
import type { NotificationData } from '@/types/models/NotificationData';
import type { Collection } from '@/types/resources/Collection';
import type { DataListEmittedData } from '@/components/DataList/DataList.vue';
import type { PurchaseReadAnyPaginateRequest } from '@/types/services/purchase/PurchaseRequest';
import type { ServiceResponse } from '@/types/services/ServiceResponse';
import type { AlertPlaceholderProps } from '@/components/AlertPlaceholder/AlertPlaceholder.vue';
import { formatCurrency, formatDate } from '@/utils/helper';

const { t } = useI18n();
const router = useRouter();
const purchaseService = new PurchaseService();
const purchaseOrderService = new PurchaseOrderService();
const supplierService = new SupplierService();
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
const selectedPurchaseOrderId = ref<string | null>(null);
const selectedReceiptMode = ref<string | null>(null);
const selectedProgressStatus = ref<string | null>(null);
const selectedIsPosted = ref<string | null>(null);

const purchaseLists = ref<Collection<Array<Purchase>> | null>({
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

const purchaseOrderDDL = ref<Array<DropDownOption> | null>(null);
const purchaseOrderSearch = ref<string>('');
const purchaseOrderOptions = computed(() =>
  (purchaseOrderDDL.value ?? []).map((item) => ({
    value: item.code,
    label: item.name,
  })),
);

const receiptModeOptions = computed(() => [
  { value: 'direct', label: 'Direct' },
  { value: 'manual', label: 'Manual' },
]);

const progressStatusOptions = computed(() => [
  { value: 'unlinked', label: 'Unlinked' },
  { value: 'unmatched', label: 'Unmatched' },
  { value: 'matched', label: 'Matched' },
]);

const postedOptions = computed(() => [
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

  await Promise.all([loadSupplierDDL(), loadPurchaseOrderDDL()]);
  await getPurchases('', true, 1, 10);
});

const getPurchases = async (search: string, refresh: boolean, page: number, perPage: number) => {
  emits('loading-state', true);
  searchText.value = search;

  const request: PurchaseReadAnyPaginateRequest = {
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    branch_id: selectedUserLocation.value.branch.id,
    search,
    start_date: startDate.value,
    end_date: endDate.value ?? undefined,
    supplier_id: selectedSupplierId.value,
    purchase_order_id: selectedPurchaseOrderId.value,
    receipt_mode: selectedReceiptMode.value,
    is_posted: selectedIsPosted.value === null ? null : selectedIsPosted.value === 'true',
    progress_status: selectedProgressStatus.value,
    refresh,
    page,
    per_page: perPage,
  };

  const result = (await purchaseService.readAnyPaginate(request)) as ServiceResponse<Collection<Array<Purchase>> | null>;

  if (result.success && result.data) {
    purchaseLists.value = result.data;
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

const loadPurchaseOrderDDL = async (search = '') => {
  if (!selectedUserLocation.value) return;

  const result = await purchaseOrderService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    branch_id: selectedUserLocation.value.branch.id,
    search,
    start_date: null,
    end_date: null,
    supplier_id: selectedSupplierId.value,
    refresh: false,
    limit: 100,
  });

  if (result.success && result.data) {
    purchaseOrderDDL.value = result.data.data.map((item: any) => ({
      code: item.id,
      name: item.code,
    }));
  }
};

const clearSupplierFilter = async () => {
  selectedSupplierId.value = null;
  selectedPurchaseOrderId.value = null;
  await loadPurchaseOrderDDL();
  await getPurchases(searchText.value, true, 1, purchaseLists.value?.meta.per_page ?? 10);
};

const handleSupplierFilterChange = async () => {
  selectedPurchaseOrderId.value = null;
  await loadPurchaseOrderDDL();
  await getPurchases(searchText.value, true, 1, purchaseLists.value?.meta.per_page ?? 10);
};

const clearPurchaseOrderFilter = async () => {
  selectedPurchaseOrderId.value = null;
  await getPurchases(searchText.value, true, 1, purchaseLists.value?.meta.per_page ?? 10);
};

const handleDataListChange = async (emittedData: DataListEmittedData) => {
  await getPurchases(
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
  if (!purchaseLists.value?.data?.[index]) return;

  const selectedPurchase = purchaseLists.value.data[index];
  const editRouteName = selectedPurchase.receipt_mode === 'direct'
    ? 'side-menu-purchase-edit-direct'
    : 'side-menu-purchase-edit-manual';

  router.push({
    name: editRouteName,
    params: { ulid: selectedPurchase.ulid },
  });
};

const deleteSelected = (index: number) => {
  if (!purchaseLists.value?.data?.[index]) return;

  deleteUlid.value = purchaseLists.value.data[index].ulid;
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
  const result = await purchaseService.delete(deleteUlid.value);
  emits('loading-state', false);
  deleteModalShow.value = false;

  if (result.success) {
    showAlertPlaceholder('hidden', '', null);
    emits('update-profile');
    showNotification(t('views.purchase.alert.delete.title'), t('views.purchase.alert.delete.message'));
    await getPurchases(searchText.value, true, purchaseLists.value?.meta.current_page ?? 1, purchaseLists.value?.meta.per_page ?? 10);
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
          <FormLabel>{{ t('views.purchase.fields.start_date') }}</FormLabel>
          <FormInputDateTime
            v-model="startDate"
            @change="getPurchases(searchText, true, 1, purchaseLists?.meta.per_page ?? 10)"
          />
        </div>
        <div class="col-span-12 md:col-span-3">
          <FormLabel>{{ t('views.purchase.fields.end_date') }}</FormLabel>
          <FormInputDateTime
            v-model="endDate"
            @change="getPurchases(searchText, true, 1, purchaseLists?.meta.per_page ?? 10)"
          />
        </div>
        <div class="col-span-12 md:col-span-3">
          <FormLabel>{{ t('views.purchase.fields.supplier_id') }}</FormLabel>
          <FormSelectSearch
            v-model="selectedSupplierId"
            v-model:search="supplierSearch"
            :options="supplierOptions"
            :placeholder="t('components.dropdown.placeholder')"
            @change="handleSupplierFilterChange"
            @search="loadSupplierDDL"
            @clear="clearSupplierFilter"
          />
        </div>
        <div class="col-span-12 md:col-span-3">
          <FormLabel>{{ t('views.purchase.fields.purchase_order_id') }}</FormLabel>
          <FormSelectSearch
            v-model="selectedPurchaseOrderId"
            v-model:search="purchaseOrderSearch"
            :options="purchaseOrderOptions"
            :placeholder="t('components.dropdown.placeholder')"
            @change="getPurchases(searchText, true, 1, purchaseLists?.meta.per_page ?? 10)"
            @search="loadPurchaseOrderDDL"
            @clear="clearPurchaseOrderFilter"
          />
        </div>
        <div class="col-span-12 md:col-span-4">
          <FormLabel>{{ t('views.purchase.fields.receipt_mode') }}</FormLabel>
          <FormSelectSearch
            v-model="selectedReceiptMode"
            :options="receiptModeOptions"
            :placeholder="t('components.dropdown.placeholder')"
            @change="getPurchases(searchText, true, 1, purchaseLists?.meta.per_page ?? 10)"
            @clear="getPurchases(searchText, true, 1, purchaseLists?.meta.per_page ?? 10)"
          />
        </div>
        <div class="col-span-12 md:col-span-4">
          <FormLabel>{{ t('views.purchase.fields.progress_status') }}</FormLabel>
          <FormSelectSearch
            v-model="selectedProgressStatus"
            :options="progressStatusOptions"
            :placeholder="t('components.dropdown.placeholder')"
            @change="getPurchases(searchText, true, 1, purchaseLists?.meta.per_page ?? 10)"
            @clear="getPurchases(searchText, true, 1, purchaseLists?.meta.per_page ?? 10)"
          />
        </div>
        <div class="col-span-12 md:col-span-4">
          <FormLabel>{{ t('views.purchase.fields.is_posted') }}</FormLabel>
          <FormSelectSearch
            v-model="selectedIsPosted"
            :options="postedOptions"
            :placeholder="t('components.dropdown.placeholder')"
            @change="getPurchases(searchText, true, 1, purchaseLists?.meta.per_page ?? 10)"
            @clear="getPurchases(searchText, true, 1, purchaseLists?.meta.per_page ?? 10)"
          />
        </div>
      </div>

      <DataListFlex
        :data="purchaseLists"
        :title="t('views.purchase.table.title')"
        :enable-search="true"
        :can-print="true"
        :can-export="true"
        :rows="purchaseLists?.data ?? []"
        row-class="bg-white dark:bg-darkmode-600"
        :pagination="purchaseLists ? purchaseLists.meta : null"
        @dataListChanged="handleDataListChange"
      >
        <template #row="{ item, index }">
          <div class="col-span-12 self-start lg:col-span-4">
            <div class="space-y-2">
              <div class="text-xs font-semibold uppercase tracking-wide text-primary">
                {{ t('views.purchase.page_title') }}
              </div>
              <div class="grid grid-cols-12 items-center gap-x-3 gap-y-2 text-xs">
                <div class="col-span-4 text-slate-500">{{ t('views.purchase.fields.code') }}</div>
                <div class="col-span-8 break-words text-slate-700 dark:text-slate-200">{{ (item as Purchase).code ?? '-' }}</div>
                <div class="col-span-4 text-slate-500">{{ t('views.purchase.fields.date') }}</div>
                <div class="col-span-8 break-words text-slate-700 dark:text-slate-200">
                  {{ (item as Purchase).date ? formatDate((item as Purchase).date, 'DD-MMM-YYYY HH:mm:ss') : '-' }}
                </div>
                <div class="col-span-4 text-slate-500">{{ t('views.purchase.fields.supplier_id') }}</div>
                <div class="col-span-8 break-words text-slate-700 dark:text-slate-200">{{ (item as Purchase).supplier?.name ?? '-' }}</div>
                <div class="col-span-4 text-slate-500">{{ t('views.purchase.fields.purchase_order_id') }}</div>
                <div class="col-span-8 break-words text-slate-700 dark:text-slate-200">{{ (item as Purchase).purchase_order?.code ?? '-' }}</div>
                <div class="col-span-4 text-slate-500">{{ t('views.purchase.fields.receipt_mode') }}</div>
                <div class="col-span-8 break-words text-slate-700 dark:text-slate-200">{{ (item as Purchase).receipt_mode ?? '-' }}</div>
                <div class="col-span-4 text-slate-500">{{ t('views.purchase.fields.due_days') }}</div>
                <div class="col-span-8 text-slate-700 dark:text-slate-200">{{ (item as Purchase).due_days ?? 0 }}</div>
                <div class="col-span-4 text-slate-500">{{ t('views.purchase.fields.remarks') }}</div>
                <div class="col-span-8 break-words text-slate-700 dark:text-slate-200">{{ (item as Purchase).remarks?.trim() || '-' }}</div>
                <div class="col-span-4 text-slate-500">{{ t('views.purchase.fields.is_posted') }}</div>
                <div class="col-span-8 break-words text-slate-700 dark:text-slate-200">
                  {{ (item as Purchase).is_posted ? t('components.buttons.yes') : t('components.buttons.no') }}
                </div>
              </div>
            </div>
          </div>

          <div class="col-span-12 self-start md:pr-3 lg:col-span-4 lg:pr-4">
            <div class="space-y-2">
              <div class="text-xs font-semibold uppercase tracking-wide text-primary">
                {{ t('views.purchase.field_groups.summary') }}
              </div>
              <div class="grid grid-cols-12 items-center gap-x-3 gap-y-2 text-xs">
                <div class="col-span-7 text-slate-500">{{ t('views.purchase.fields.item_total_before_global_discount') }}</div>
                <div class="col-span-5 text-right text-slate-700 dark:text-slate-200">{{ formatCurrency(Number((item as Purchase).item_total_before_global_discount ?? 0)) }}</div>
                <div class="col-span-7 text-slate-500">{{ t('views.purchase.fields.global_discount') }}</div>
                <div class="col-span-5 text-right text-slate-700 dark:text-slate-200">{{ formatCurrency(Number((item as Purchase).global_discount ?? 0)) }}</div>
                <div class="col-span-7 text-slate-500">{{ t('views.purchase.fields.item_total_after_global_discount') }}</div>
                <div class="col-span-5 text-right text-slate-700 dark:text-slate-200">{{ formatCurrency(Number((item as Purchase).item_total_after_global_discount ?? 0)) }}</div>
                <div class="col-span-7 text-slate-500">{{ t('views.purchase.fields.vat_base') }}</div>
                <div class="col-span-5 text-right text-slate-700 dark:text-slate-200">{{ formatCurrency(Number((item as Purchase).vat_base ?? 0)) }}</div>
                <div class="col-span-7 text-slate-500">{{ t('views.purchase.fields.vat') }}</div>
                <div class="col-span-5 text-right text-slate-700 dark:text-slate-200">{{ formatCurrency(Number((item as Purchase).vat ?? 0)) }}</div>
                <div class="col-span-7 text-slate-500">{{ t('views.purchase.fields.additional_cost') }}</div>
                <div class="col-span-5 text-right text-slate-700 dark:text-slate-200">{{ formatCurrency(Number((item as Purchase).additional_cost ?? 0)) }}</div>
                <div class="col-span-7 text-slate-500">{{ t('views.purchase.fields.rounding') }}</div>
                <div class="col-span-5 text-right text-slate-700 dark:text-slate-200">{{ formatCurrency(Number((item as Purchase).rounding ?? 0)) }}</div>
                <div class="col-span-4 font-medium text-primary">{{ t('views.purchase.fields.amount_payable') }}</div>
                <div class="col-span-8 text-right font-medium text-primary">{{ formatCurrency(Number((item as Purchase).amount_payable ?? 0)) }}</div>
              </div>
            </div>
          </div>

          <div class="col-span-12 self-start md:pl-3 lg:col-span-3 lg:pl-4">
            <div class="space-y-2">
              <div class="text-xs font-semibold uppercase tracking-wide text-primary">
                {{ t('views.purchase.field_groups.status') }}
              </div>
              <div class="grid grid-cols-12 items-center gap-x-3 gap-y-2 text-xs">
                <div class="col-span-7 text-slate-500">{{ t('views.purchase.fields.amount_paid_total') }}</div>
                <div class="col-span-5 text-right text-slate-700 dark:text-slate-200">{{ formatCurrency(Number((item as Purchase).amount_paid_total ?? 0)) }}</div>
                <div class="col-span-7 text-slate-500">{{ t('views.purchase.fields.amount_due') }}</div>
                <div class="col-span-5 text-right text-slate-700 dark:text-slate-200">{{ formatCurrency(Number((item as Purchase).amount_due ?? 0)) }}</div>
                <div class="col-span-7 text-slate-500">{{ t('views.purchase.fields.is_paid_off') }}</div>
                <div class="col-span-5 text-right text-slate-700 dark:text-slate-200">
                  {{ (item as Purchase).is_paid_off ? t('components.buttons.yes') : t('components.buttons.no') }}
                </div>
                <div class="col-span-7 text-slate-500">{{ t('views.purchase.fields.progress_status') }}</div>
                <div class="col-span-5 text-right text-slate-700 capitalize dark:text-slate-200">{{ (item as Purchase).progress_status ?? '-' }}</div>
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
              <div class="col-span-12 space-y-3">
                <div class="text-xs font-semibold uppercase tracking-wide text-primary">
                  {{ t('views.purchase.field_groups.status_breakdown') }}
                </div>
                <div class="rounded-md border border-slate-200/70 p-3 dark:border-darkmode-400">
                  <div class="flex flex-wrap gap-2 text-xs">
                    <div class="flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 dark:border-darkmode-300 dark:bg-darkmode-600">
                      <span class="text-slate-500">{{ t('views.purchase.fields.item_total_count') }}</span>
                      <span class="font-medium text-slate-700 dark:text-slate-100">{{ (item as Purchase).item_total_count ?? 0 }}</span>
                    </div>
                    <div class="flex items-center gap-2 rounded-full border border-success/30 bg-success/10 px-3 py-1.5 dark:border-success/20 dark:bg-success/10">
                      <span class="text-slate-500 dark:text-slate-300">{{ t('views.purchase.fields.item_matched_count') }}</span>
                      <span class="font-medium text-success">{{ (item as Purchase).item_matched_count ?? 0 }}</span>
                    </div>
                    <div class="flex items-center gap-2 rounded-full border border-warning/30 bg-warning/10 px-3 py-1.5 dark:border-warning/20 dark:bg-warning/10">
                      <span class="text-slate-500 dark:text-slate-300">{{ t('views.purchase.fields.item_less_count') }}</span>
                      <span class="font-medium text-warning">{{ (item as Purchase).item_less_count ?? 0 }}</span>
                    </div>
                    <div class="flex items-center gap-2 rounded-full border border-primary/30 bg-primary/10 px-3 py-1.5 dark:border-primary/20 dark:bg-primary/10">
                      <span class="text-slate-500 dark:text-slate-300">{{ t('views.purchase.fields.item_more_count') }}</span>
                      <span class="font-medium text-primary">{{ (item as Purchase).item_more_count ?? 0 }}</span>
                    </div>
                    <div class="flex items-center gap-2 rounded-full border border-pending/30 bg-pending/10 px-3 py-1.5 dark:border-pending/20 dark:bg-pending/10">
                      <span class="text-slate-500 dark:text-slate-300">{{ t('views.purchase.fields.item_unlinked_count') }}</span>
                      <span class="font-medium text-pending">{{ (item as Purchase).item_unlinked_count ?? 0 }}</span>
                    </div>
                    <div class="flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 dark:border-darkmode-300 dark:bg-darkmode-600">
                      <span class="text-slate-500">{{ t('views.purchase.fields.manual_receipt_count') }}</span>
                      <span class="font-medium text-slate-700 dark:text-slate-100">{{ (item as Purchase).manual_receipts?.length ?? 0 }}</span>
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-span-12 space-y-3">
                <div class="text-xs font-semibold uppercase tracking-wide text-primary">
                  {{ t('views.purchase.field_groups.items') }}
                </div>
                <div v-if="!(item as Purchase).items?.length" class="text-xs text-slate-500">
                  {{ t('views.purchase.fields.items_empty') }}
                </div>
                <div v-else class="space-y-2">
                  <div
                    v-for="(purchaseItem, itemIndex) in (item as Purchase).items ?? []"
                    :key="purchaseItem.ulid ?? `${(item as Purchase).ulid}-item-${itemIndex}`"
                    class="flex flex-wrap items-stretch gap-2 rounded-xl border border-slate-200/70 bg-slate-50/60 px-3 py-3 text-xs dark:border-darkmode-400 dark:bg-darkmode-700/40"
                  >
                    <div class="flex min-w-0 basis-full items-center gap-3 lg:flex-1 lg:basis-auto">
                      <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full border border-primary/20 bg-primary/10 text-[11px] font-semibold text-primary">
                        {{ itemIndex + 1 }}
                      </div>
                      <div class="min-w-0 flex-1">
                        <div class="truncate text-sm font-semibold text-slate-800 dark:text-slate-100">
                          {{ purchaseItem.product_unit?.product?.name ?? purchaseItem.product_unit?.code ?? '-' }}
                        </div>
                        <div class="truncate text-[11px] text-slate-500 dark:text-slate-400">
                          {{ purchaseItem.product_unit?.code ?? '-' }}
                        </div>
                      </div>
                    </div>

                    <div class="flex min-h-[56px] min-w-[104px] flex-col justify-between rounded-lg border border-slate-200/80 bg-white px-3 py-2 dark:border-darkmode-500 dark:bg-darkmode-600/70 sm:min-w-[116px]">
                      <div class="truncate text-[11px] leading-4 text-slate-500">{{ t('views.purchase.fields.qty') }}</div>
                      <div class="text-sm font-semibold leading-5 text-slate-700 dark:text-slate-100">{{ purchaseItem.qty ?? 0 }}</div>
                    </div>

                    <div class="flex min-h-[56px] min-w-[104px] flex-col justify-between rounded-lg border border-slate-200/80 bg-white px-3 py-2 dark:border-darkmode-500 dark:bg-darkmode-600/70 sm:min-w-[116px]">
                      <div class="truncate text-[11px] leading-4 text-slate-500">{{ t('views.purchase.fields.product_unit_price') }}</div>
                      <div class="text-sm font-semibold leading-5 text-slate-700 dark:text-slate-100">
                        {{ formatCurrency(Number(purchaseItem.product_unit_price ?? 0)) }}
                      </div>
                    </div>

                    <div class="flex min-h-[56px] min-w-[104px] flex-col justify-between rounded-lg border border-success/20 bg-success/10 px-3 py-2 sm:min-w-[116px]">
                      <div class="truncate text-[11px] leading-4 text-slate-500 dark:text-slate-300">{{ t('views.purchase.fields.subtotal') }}</div>
                      <div class="text-sm font-semibold leading-5 text-success">
                        {{ formatCurrency(Number(purchaseItem.subtotal_after_vat ?? 0)) }}
                      </div>
                    </div>

                    <div class="flex min-h-[56px] min-w-[104px] flex-col justify-between rounded-lg border border-slate-200/80 bg-white px-3 py-2 dark:border-darkmode-500 dark:bg-darkmode-600/70 sm:min-w-[116px]">
                      <div class="truncate text-[11px] leading-4 text-slate-500">{{ t('views.purchase.fields.product_unit_qty_base') }}</div>
                      <div class="text-sm font-semibold leading-5 text-slate-700 dark:text-slate-100">{{ purchaseItem.product_unit_qty_base ?? 0 }}</div>
                    </div>

                    <div class="flex min-h-[56px] min-w-[104px] flex-col justify-between rounded-lg border border-slate-200/80 bg-white px-3 py-2 dark:border-darkmode-500 dark:bg-darkmode-600/70 sm:min-w-[116px]">
                      <div class="truncate text-[11px] leading-4 text-slate-500">{{ t('views.purchase.fields.qty_received_base') }}</div>
                      <div class="text-sm font-semibold leading-5 text-slate-700 dark:text-slate-100">{{ purchaseItem.qty_received_base ?? 0 }}</div>
                    </div>

                    <div class="flex min-h-[56px] min-w-[104px] flex-col justify-between rounded-lg border border-slate-200/80 bg-white px-3 py-2 dark:border-darkmode-500 dark:bg-darkmode-600/70 sm:min-w-[116px]">
                      <div class="truncate text-[11px] leading-4 text-slate-500">{{ t('views.purchase.fields.qty_outstanding_base') }}</div>
                      <div class="text-sm font-semibold leading-5 text-slate-700 dark:text-slate-100">{{ purchaseItem.qty_outstanding_base ?? 0 }}</div>
                    </div>

                    <div class="flex min-h-[56px] min-w-[104px] flex-col justify-between rounded-lg border border-slate-200/80 bg-white px-3 py-2 dark:border-darkmode-500 dark:bg-darkmode-600/70 sm:min-w-[116px]">
                      <div class="truncate text-[11px] leading-4 text-slate-500">{{ t('views.purchase.fields.qty_excess_base') }}</div>
                      <div class="text-sm font-semibold leading-5 text-slate-700 dark:text-slate-100">{{ purchaseItem.qty_excess_base ?? 0 }}</div>
                    </div>

                    <div
                      v-if="purchaseItem.remarks?.trim()"
                      class="min-w-0 basis-full text-[11px] text-slate-600 dark:text-slate-300 xl:basis-auto xl:flex-1"
                    >
                      <span class="text-slate-500">{{ t('views.purchase.fields.remarks') }}:</span>
                      {{ purchaseItem.remarks?.trim() }}
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
        <Lucide icon="XCircle" class="mx-auto mt-3 h-16 w-16 text-danger" />
        <div class="mt-5 text-3xl">Are you sure?</div>
        <div class="mt-2 text-slate-500">This action cannot be undone.</div>
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
