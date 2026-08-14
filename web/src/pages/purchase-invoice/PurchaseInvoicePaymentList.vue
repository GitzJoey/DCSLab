<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import { FormInputDateTime, FormLabel, FormSelectSearch } from '@/components/Base/Form';
import { DataListFlex } from '@/components/DataList';
import CashAccountService from '@/services/CashAccountService';
import PurchaseInvoicePaymentService from '@/services/PurchaseInvoicePaymentService';
import PurchaseInvoiceService from '@/services/PurchaseInvoiceService';
import { useSelectedUserLocationStore } from '@/stores/selected-user-location';
import { ErrorCode } from '@/types/enums/ErrorCode';
import { ViewMode } from '@/types/enums/ViewMode';
import type { DataListEmittedData } from '@/components/DataList/DataList.vue';
import type { AlertPlaceholderProps } from '@/components/AlertPlaceholder/AlertPlaceholder.vue';
import type { DropDownOption } from '@/types/models/DropDownOption';
import type { PurchaseInvoicePayment, PurchaseInvoicePaymentType } from '@/types/models/PurchaseInvoicePayment';
import type { Collection } from '@/types/resources/Collection';
import type { ServiceResponse } from '@/types/services/ServiceResponse';
import type { PurchaseInvoicePaymentReadAnyPaginateRequest } from '@/types/services/purchase-invoice-payment/PurchaseInvoicePaymentRequest';
import { formatCurrency, formatDate } from '@/utils/helper';

const { t } = useI18n();
const router = useRouter();

const purchaseInvoicePaymentService = new PurchaseInvoicePaymentService();
const purchaseInvoiceService = new PurchaseInvoiceService();
const cashAccountService = new CashAccountService();
const selectedUserLocationStore = useSelectedUserLocationStore();

const emits = defineEmits(['mode-state', 'loading-state', 'show-alertplaceholder', 'show-notification']);

const startDate = ref<string | null>(null);
const endDate = ref<string | null>(null);
const searchText = ref<string>('');
const selectedPurchaseInvoiceId = ref<string | null>(null);
const selectedPaymentType = ref<string | null>(null);
const selectedCashAccountId = ref<string | null>(null);

const purchaseInvoicePaymentLists = ref<Collection<Array<PurchaseInvoicePayment>> | null>({
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

const purchaseInvoiceDDL = ref<Array<DropDownOption> | null>(null);
const purchaseInvoiceSearch = ref<string>('');
const purchaseInvoiceOptions = computed(() =>
  (purchaseInvoiceDDL.value ?? []).map((item) => ({
    value: item.code,
    label: item.name,
  })),
);

const cashAccountDDL = ref<Array<DropDownOption> | null>(null);
const cashAccountSearch = ref<string>('');
const cashAccountOptions = computed(() =>
  (cashAccountDDL.value ?? []).map((item) => ({
    value: item.code,
    label: item.name,
  })),
);

const paymentTypeOptions = computed(() => [
  { value: 'cash', label: t('views.purchase_invoice_payment.filters.payment_type_cash') },
  { value: 'down_payment', label: t('views.purchase_invoice_payment.filters.payment_type_down_payment') },
  { value: 'return', label: t('views.purchase_invoice_payment.filters.payment_type_return') },
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

  await Promise.all([loadPurchaseInvoiceDDL(), loadCashAccountDDL()]);
  await getPurchaseInvoicePayments('', true, 1, 10);
});

const getPurchaseInvoicePayments = async (search: string, refresh: boolean, page: number, perPage: number) => {
  emits('loading-state', true);
  searchText.value = search;

  const request: PurchaseInvoicePaymentReadAnyPaginateRequest = {
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    branch_id: selectedUserLocation.value.branch.id,
    search,
    start_date: startDate.value,
    end_date: endDate.value,
    purchase_invoice_id: selectedPurchaseInvoiceId.value,
    payment_type: (selectedPaymentType.value as PurchaseInvoicePaymentType | null) ?? null,
    cash_account_id: selectedCashAccountId.value,
    refresh,
    page,
    per_page: perPage,
  };

  const result = (await purchaseInvoicePaymentService.readAnyPaginate(request)) as ServiceResponse<Collection<
    Array<PurchaseInvoicePayment>
  > | null>;

  if (result.success && result.data) {
    purchaseInvoicePaymentLists.value = result.data;
    showAlertPlaceholder('hidden', '', null);
  } else {
    showAlertPlaceholder('danger', '', result.errors as Record<string, Array<string>>);
  }

  emits('loading-state', false);
};

const loadPurchaseInvoiceDDL = async (search = '') => {
  if (!selectedUserLocation.value) return;

  const result = await purchaseInvoiceService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    branch_id: selectedUserLocation.value.branch.id,
    search,
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

const loadCashAccountDDL = async (search = '') => {
  if (!selectedUserLocation.value) return;

  const result = await cashAccountService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    branch_id: selectedUserLocation.value.branch.id,
    search,
    is_bank: undefined,
    include_id: selectedCashAccountId.value ?? undefined,
    with_remaining_balance: undefined,
    refresh: false,
    limit: 100,
  });

  if (result.success && result.data) {
    cashAccountDDL.value = result.data.data.map((item: any) => ({
      code: item.id,
      name: `${item.code} - ${item.name}`,
    }));
  }
};

const reloadList = async () => {
  await getPurchaseInvoicePayments(searchText.value, true, 1, purchaseInvoicePaymentLists.value?.meta.per_page ?? 10);
};

const handleDataListChange = async (emittedData: DataListEmittedData) => {
  await getPurchaseInvoicePayments(
    emittedData.search.text,
    true,
    emittedData.pagination.page,
    emittedData.pagination.per_page,
  );
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

const formatCurrencyRounded = (value: number | string | null | undefined, precision = 2) =>
  formatCurrency(Number(Number(value ?? 0).toFixed(precision)));

const paymentTypeLabel = (paymentType: string | null | undefined) => {
  switch (paymentType) {
    case 'cash':
      return t('views.purchase_invoice_payment.filters.payment_type_cash');
    case 'down_payment':
      return t('views.purchase_invoice_payment.filters.payment_type_down_payment');
    case 'return':
      return t('views.purchase_invoice_payment.filters.payment_type_return');
    default:
      return '-';
  }
};

const paymentReferenceLabel = (payment: PurchaseInvoicePayment) => {
  switch (payment.payment_type) {
    case 'cash':
      return payment.cash_account?.name ?? '-';
    case 'down_payment':
      return payment.purchase_order_payment?.code ?? '-';
    case 'return':
      return payment.purchase_return?.code ?? '-';
    default:
      return '-';
  }
};
</script>

<template>
  <div class="mt-5 grid grid-cols-12 gap-6">
    <div class="col-span-12">
      <div class="mb-5 grid grid-cols-12 gap-4">
        <div class="col-span-12 md:col-span-6 lg:col-span-3">
          <FormLabel>{{ t('views.purchase_invoice_payment.fields.start_date') }}</FormLabel>
          <FormInputDateTime v-model="startDate" @change="reloadList" />
        </div>
        <div class="col-span-12 md:col-span-6 lg:col-span-3">
          <FormLabel>{{ t('views.purchase_invoice_payment.fields.end_date') }}</FormLabel>
          <FormInputDateTime v-model="endDate" @change="reloadList" />
        </div>
        <div class="col-span-12 md:col-span-6 lg:col-span-3">
          <FormLabel>{{ t('views.purchase_invoice_payment.fields.purchase_invoice_id') }}</FormLabel>
          <FormSelectSearch
            v-model="selectedPurchaseInvoiceId"
            v-model:search="purchaseInvoiceSearch"
            :options="purchaseInvoiceOptions"
            :placeholder="t('components.dropdown.placeholder')"
            @change="reloadList"
            @search="loadPurchaseInvoiceDDL"
            @clear="
              () => {
                selectedPurchaseInvoiceId = null;
                reloadList();
              }
            "
          />
        </div>
        <div class="col-span-12 md:col-span-6 lg:col-span-3">
          <FormLabel>{{ t('views.purchase_invoice_payment.filters.payment_type') }}</FormLabel>
          <FormSelectSearch
            v-model="selectedPaymentType"
            :options="paymentTypeOptions"
            :placeholder="t('components.dropdown.placeholder')"
            @change="reloadList"
            @clear="
              () => {
                selectedPaymentType = null;
                reloadList();
              }
            "
          />
        </div>
        <div class="col-span-12 md:col-span-6 lg:col-span-3">
          <FormLabel>{{ t('views.purchase_invoice_payment.fields.cash_account_id') }}</FormLabel>
          <FormSelectSearch
            v-model="selectedCashAccountId"
            v-model:search="cashAccountSearch"
            :options="cashAccountOptions"
            :placeholder="t('components.dropdown.placeholder')"
            @change="reloadList"
            @search="loadCashAccountDDL"
            @clear="
              () => {
                selectedCashAccountId = null;
                reloadList();
              }
            "
          />
        </div>
      </div>

      <DataListFlex
        :data="purchaseInvoicePaymentLists"
        :title="t('views.purchase_invoice_payment.table.title')"
        :enable-search="true"
        :can-print="true"
        :can-export="true"
        :rows="purchaseInvoicePaymentLists?.data ?? []"
        row-class="bg-white dark:bg-darkmode-600"
        :pagination="purchaseInvoicePaymentLists ? purchaseInvoicePaymentLists.meta : null"
        @dataListChanged="handleDataListChange"
      >
        <template #row="{ item }">
          <div class="col-span-12 self-start lg:col-span-5">
            <div class="space-y-2">
              <div class="text-xs font-semibold uppercase tracking-wide text-primary">
                {{ t('views.purchase_invoice_payment.field_groups.payment') }}
              </div>
              <div class="grid grid-cols-12 items-center gap-x-3 gap-y-2 text-xs">
                <div class="col-span-4 text-slate-500">{{ t('views.purchase_invoice_payment.fields.code') }}</div>
                <div class="col-span-8 break-words text-slate-700 dark:text-slate-200">
                  {{ (item as PurchaseInvoicePayment).code ?? '-' }}
                </div>
                <div class="col-span-4 text-slate-500">{{ t('views.purchase_invoice_payment.fields.date') }}</div>
                <div class="col-span-8 break-words text-slate-700 dark:text-slate-200">
                  {{
                    (item as PurchaseInvoicePayment).date
                      ? formatDate((item as PurchaseInvoicePayment).date, 'DD-MMM-YYYY HH:mm:ss')
                      : '-'
                  }}
                </div>
                <div class="col-span-4 text-slate-500">
                  {{ t('views.purchase_invoice_payment.fields.payment_type') }}
                </div>
                <div class="col-span-8 break-words text-slate-700 dark:text-slate-200">
                  {{ paymentTypeLabel((item as PurchaseInvoicePayment).payment_type) }}
                </div>
                <div class="col-span-4 text-slate-500">{{ t('views.purchase_invoice_payment.fields.amount') }}</div>
                <div class="col-span-8 break-words text-slate-700 dark:text-slate-200">
                  {{ formatCurrencyRounded((item as PurchaseInvoicePayment).amount) }}
                </div>
              </div>
            </div>
          </div>

          <div class="col-span-12 self-start lg:col-span-4">
            <div class="space-y-2">
              <div class="text-xs font-semibold uppercase tracking-wide text-primary">
                {{ t('views.purchase_invoice_payment.field_groups.reference') }}
              </div>
              <div class="grid grid-cols-12 items-center gap-x-3 gap-y-2 text-xs">
                <div class="col-span-5 text-slate-500">
                  {{ t('views.purchase_invoice_payment.fields.purchase_invoice_id') }}
                </div>
                <div class="col-span-7 break-words text-slate-700 dark:text-slate-200">
                  {{ (item as PurchaseInvoicePayment).purchase_invoice?.code ?? '-' }}
                </div>
                <div class="col-span-5 text-slate-500">{{ t('views.purchase_invoice_payment.fields.supplier') }}</div>
                <div class="col-span-7 break-words text-slate-700 dark:text-slate-200">
                  {{ (item as PurchaseInvoicePayment).purchase_invoice?.supplier?.name ?? '-' }}
                </div>
                <div class="col-span-5 text-slate-500">
                  {{ t('views.purchase_invoice_payment.field_groups.reference') }}
                </div>
                <div class="col-span-7 break-words text-slate-700 dark:text-slate-200">
                  {{ paymentReferenceLabel(item as PurchaseInvoicePayment) }}
                </div>
              </div>
            </div>
          </div>

          <div class="col-span-12 self-start lg:col-span-3">
            <div class="space-y-2">
              <div class="text-xs font-semibold uppercase tracking-wide text-primary">
                {{ t('views.purchase_invoice_payment.field_groups.remarks') }}
              </div>
              <div
                class="rounded-md border border-slate-200/70 p-3 text-xs text-slate-700 dark:border-darkmode-400 dark:text-slate-200"
              >
                {{ (item as PurchaseInvoicePayment).remarks?.trim() || '-' }}
              </div>
            </div>
          </div>
        </template>
      </DataListFlex>
    </div>
  </div>
</template>
