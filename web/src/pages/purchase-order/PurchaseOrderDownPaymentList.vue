<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import { FormInputDateTime, FormLabel, FormSelectSearch } from '@/components/Base/Form';
import { DataListFlex } from '@/components/DataList';
import Button from '@/components/Base/Button';
import Lucide from '@/components/Base/Lucide';
import PurchaseOrderDownPaymentService from '@/services/PurchaseOrderDownPaymentService';
import SupplierService from '@/services/SupplierService';
import CashAccountService from '@/services/CashAccountService';
import { useSelectedUserLocationStore } from '@/stores/selected-user-location';
import { ErrorCode } from '@/types/enums/ErrorCode';
import { ViewMode } from '@/types/enums/ViewMode';
import type { PurchaseOrderDownPayment } from '@/types/models/PurchaseOrderDownPayment';
import type { DropDownOption } from '@/types/models/DropDownOption';
import type { Collection } from '@/types/resources/Collection';
import type { DataListEmittedData } from '@/components/DataList/DataList.vue';
import type { PurchaseOrderDownPaymentReadAnyPaginateRequest } from '@/types/services/purchase-order-down-payment/PurchaseOrderDownPaymentRequest';
import type { ServiceResponse } from '@/types/services/ServiceResponse';
import { formatCurrency, formatDate } from '@/utils/helper';

const { t } = useI18n();
const router = useRouter();
const purchaseOrderDownPaymentService = new PurchaseOrderDownPaymentService();
const supplierService = new SupplierService();
const cashAccountService = new CashAccountService();
const selectedUserLocationStore = useSelectedUserLocationStore();

const emits = defineEmits(['mode-state', 'loading-state', 'show-alertplaceholder', 'show-notification']);

const expandDetail = ref<number | null>(null);
const startDate = ref<string | null>(null);
const endDate = ref<string | null>(null);
const searchText = ref('');
const selectedSupplierId = ref<string | null>(null);
const selectedCashAccountId = ref<string | null>(null);
const supplierSearch = ref('');
const cashAccountSearch = ref('');

const lists = ref<Collection<Array<PurchaseOrderDownPayment>> | null>({
  data: [],
  meta: { current_page: 1, from: null, last_page: 0, path: '', per_page: 10, to: null, total: 0 },
  links: { first: '', last: '', prev: null, next: null },
});

const isUserLocationSelected = computed(() => selectedUserLocationStore.isUserLocationSelected);
const selectedUserLocation = computed(() => selectedUserLocationStore.selectedUserLocation);

const supplierDDL = ref<Array<DropDownOption> | null>(null);
const cashAccountDDL = ref<Array<DropDownOption> | null>(null);
const supplierOptions = computed(() => (supplierDDL.value ?? []).map((item) => ({ value: item.code, label: item.name })));
const cashAccountOptions = computed(() => (cashAccountDDL.value ?? []).map((item) => ({ value: item.code, label: item.name })));

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

  await Promise.all([loadSupplierDDL(), loadCashAccountDDL()]);
  await getLists('', true, 1, 10);
});

const getLists = async (search: string, refresh: boolean, page: number, perPage: number) => {
  emits('loading-state', true);
  searchText.value = search;

  const request: PurchaseOrderDownPaymentReadAnyPaginateRequest = {
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    branch_id: selectedUserLocation.value.branch.id,
    search,
    start_date: startDate.value || undefined,
    end_date: endDate.value || undefined,
    supplier_id: selectedSupplierId.value,
    cash_account_id: selectedCashAccountId.value,
    refresh,
    page,
    per_page: perPage,
  };

  const result = (await purchaseOrderDownPaymentService.readAnyPaginate(
    request,
  )) as ServiceResponse<Collection<Array<PurchaseOrderDownPayment>> | null>;

  if (result.success && result.data) {
    lists.value = result.data;
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
    supplierDDL.value = result.data.data.map((item: any) => ({ code: item.id, name: item.name }));
  }
};

const loadCashAccountDDL = async (search = '') => {
  if (!selectedUserLocation.value) return;

  const result = await cashAccountService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    branch_id: selectedUserLocation.value.branch.id,
    search,
    refresh: false,
    limit: 100,
  });

  if (result.success && result.data) {
    cashAccountDDL.value = result.data.data.map((item: any) => ({ code: item.id, name: item.name }));
  }
};

const handleDataListChange = async (emittedData: DataListEmittedData) => {
  await getLists(emittedData.search.text, true, emittedData.pagination.page, emittedData.pagination.per_page);
};

const handleFilterChange = async () => {
  await getLists(searchText.value, true, 1, lists.value?.meta.per_page ?? 10);
};

const clearSupplierFilter = async () => {
  selectedSupplierId.value = null;
  await handleFilterChange();
};

const clearCashAccountFilter = async () => {
  selectedCashAccountId.value = null;
  await handleFilterChange();
};

const viewSelected = (index: number) => {
  expandDetail.value = expandDetail.value === index ? null : index;
};

const editSelected = (index: number) => {
  const ulid = lists.value?.data?.[index]?.purchase_order?.ulid;
  if (!ulid) return;

  router.push({
    name: 'side-menu-purchase-order-edit',
    params: { ulid },
  });
};
</script>

<template>
  <div class="grid grid-cols-12 gap-6 mt-5">
    <div class="col-span-12">
      <div class="grid grid-cols-12 gap-4 mb-5">
        <div class="col-span-12 md:col-span-3">
          <FormLabel>{{ t('views.purchase_order.fields.start_date') }}</FormLabel>
          <FormInputDateTime v-model="startDate" @change="handleFilterChange" />
        </div>
        <div class="col-span-12 md:col-span-3">
          <FormLabel>{{ t('views.purchase_order.fields.end_date') }}</FormLabel>
          <FormInputDateTime v-model="endDate" @change="handleFilterChange" />
        </div>
        <div class="col-span-12 md:col-span-3">
          <FormLabel>{{ t('views.purchase_order.fields.supplier_id') }}</FormLabel>
          <FormSelectSearch
            v-model="selectedSupplierId"
            v-model:search="supplierSearch"
            :options="supplierOptions"
            :placeholder="t('components.dropdown.placeholder')"
            @change="handleFilterChange"
            @search="loadSupplierDDL"
            @clear="clearSupplierFilter"
          />
        </div>
        <div class="col-span-12 md:col-span-3">
          <FormLabel>{{ t('views.purchase_order.fields.cash_account_id') }}</FormLabel>
          <FormSelectSearch
            v-model="selectedCashAccountId"
            v-model:search="cashAccountSearch"
            :options="cashAccountOptions"
            :placeholder="t('components.dropdown.placeholder')"
            @change="handleFilterChange"
            @search="loadCashAccountDDL"
            @clear="clearCashAccountFilter"
          />
        </div>
      </div>

      <DataListFlex
        :data="lists"
        :enable-search="true"
        :can-print="true"
        :can-export="true"
        :rows="lists?.data ?? []"
        row-class="bg-white dark:bg-darkmode-600"
        :pagination="lists ? lists.meta : null"
        @dataListChanged="handleDataListChange"
      >
        <template #row="{ item, index }">
          <div class="col-span-12 md:col-span-12 lg:col-span-4 self-start">
            <div class="space-y-2">
              <div class="text-primary text-xs font-semibold uppercase tracking-wide">
                {{ t('views.purchase_order.fields.down_payment') }}
              </div>
              <div class="grid grid-cols-12 items-center gap-x-3 gap-y-2 text-xs">
                <div class="col-span-4 text-slate-500">{{ t('views.purchase_order.fields.code') }}</div>
                <div class="col-span-8 text-slate-700 dark:text-slate-200 break-words">{{ (item as PurchaseOrderDownPayment).code }}</div>
                <div class="col-span-4 text-slate-500">{{ t('views.purchase_order.fields.date') }}</div>
                <div class="col-span-8 text-slate-700 dark:text-slate-200 break-words">
                  {{ (item as PurchaseOrderDownPayment).date ? formatDate((item as PurchaseOrderDownPayment).date, 'DD-MMM-YYYY HH:mm:ss') : '-' }}
                </div>
                <div class="col-span-4 text-slate-500">{{ t('views.purchase_order.fields.supplier_id') }}</div>
                <div class="col-span-8 text-slate-700 dark:text-slate-200 break-words">
                  {{ (item as PurchaseOrderDownPayment).purchase_order?.supplier?.name ?? '-' }}
                </div>
              </div>
            </div>
          </div>

          <div class="col-span-12 md:col-span-6 lg:col-span-4 self-start md:pr-3 lg:pr-4">
            <div class="space-y-2">
              <div class="text-primary text-xs font-semibold uppercase tracking-wide">
                {{ t('views.purchase_order.field_groups.summary') }}
              </div>
              <div class="grid grid-cols-12 items-center gap-x-3 gap-y-2 text-xs">
                <div class="col-span-7 text-slate-500">{{ t('views.purchase_order.fields.amount') }}</div>
                <div class="col-span-5 text-right text-slate-700 dark:text-slate-200">{{ formatCurrency((item as PurchaseOrderDownPayment).amount ?? 0) }}</div>
                <div class="col-span-7 text-slate-500">{{ t('views.purchase_order.fields.amount_allocated') }}</div>
                <div class="col-span-5 text-right text-slate-700 dark:text-slate-200">{{ formatCurrency((item as PurchaseOrderDownPayment).amount_allocated ?? 0) }}</div>
                <div class="col-span-7 text-primary font-medium">{{ t('views.purchase_order.fields.amount_available_down_payment') }}</div>
                <div class="col-span-5 text-right text-primary font-medium">
                  {{ formatCurrency(((item as PurchaseOrderDownPayment).amount ?? 0) - ((item as PurchaseOrderDownPayment).amount_allocated ?? 0)) }}
                </div>
              </div>
            </div>
          </div>

          <div class="col-span-12 md:col-span-5 lg:col-span-3 self-start md:pl-3 lg:pl-4">
            <div class="space-y-2">
              <div class="text-primary text-xs font-semibold uppercase tracking-wide">
                {{ t('views.purchase_order.field_groups.purchase_order_data') }}
              </div>
              <div class="grid grid-cols-12 items-center gap-x-3 gap-y-2 text-xs">
                <div class="col-span-7 text-slate-500">{{ t('views.purchase_order.fields.code') }}</div>
                <div class="col-span-5 text-right text-slate-700 dark:text-slate-200 break-words">
                  {{ (item as PurchaseOrderDownPayment).purchase_order?.code ?? '-' }}
                </div>
                <div class="col-span-7 text-slate-500">{{ t('views.purchase_order.fields.cash_account_id') }}</div>
                <div class="col-span-5 text-right text-slate-700 dark:text-slate-200 break-words">
                  {{ (item as PurchaseOrderDownPayment).cash_account?.name ?? '-' }}
                </div>
              </div>
            </div>
          </div>

          <div class="col-span-12 lg:col-span-1 md:col-span-1 self-center flex justify-end items-center gap-2 pt-2 md:flex-col lg:flex-col">
            <Button size="sm" variant="outline-secondary" class="flex items-center gap-1" @click="viewSelected(index)">
              <Lucide icon="Info" class="w-4 h-4" />
            </Button>
            <Button size="sm" variant="outline-secondary" class="flex items-center gap-1" @click="editSelected(index)">
              <Lucide icon="Pen" class="w-4 h-4" />
            </Button>
          </div>

          <div v-if="expandDetail === index" class="col-span-12 border-t border-slate-200 dark:border-darkmode-400 mt-2 pt-3">
            <div class="grid grid-cols-12 gap-4">
              <div class="col-span-12 lg:col-span-6 space-y-3">
                <div class="text-primary text-xs font-semibold uppercase tracking-wide">
                  {{ t('views.purchase_order.field_groups.purchase_order_data') }}
                </div>
                <div class="grid grid-cols-12 items-center gap-x-3 gap-y-2 text-xs">
                  <div class="col-span-4 text-slate-500">{{ t('views.purchase_order.fields.company_id') }}</div>
                  <div class="col-span-8 text-slate-700 dark:text-slate-200">{{ (item as PurchaseOrderDownPayment).company?.name ?? '-' }}</div>
                  <div class="col-span-4 text-slate-500">{{ t('views.purchase_order.fields.supplier_id') }}</div>
                  <div class="col-span-8 text-slate-700 dark:text-slate-200">{{ (item as PurchaseOrderDownPayment).purchase_order?.supplier?.name ?? '-' }}</div>
                  <div class="col-span-4 text-slate-500">{{ t('views.purchase_order.fields.code') }}</div>
                  <div class="col-span-8 text-slate-700 dark:text-slate-200 break-words">{{ (item as PurchaseOrderDownPayment).purchase_order?.code ?? '-' }}</div>
                </div>
              </div>
              <div class="col-span-12 lg:col-span-6 space-y-3">
                <div class="text-primary text-xs font-semibold uppercase tracking-wide">
                  {{ t('views.purchase_order.fields.remarks') }}
                </div>
                <div class="rounded-md border border-slate-200/60 dark:border-darkmode-400 px-3 py-2 text-slate-700 dark:text-slate-200 break-words min-h-[72px]">
                  {{ (item as PurchaseOrderDownPayment).remarks?.trim() || '-' }}
                </div>
              </div>
            </div>
          </div>
        </template>
      </DataListFlex>
    </div>
  </div>
</template>
