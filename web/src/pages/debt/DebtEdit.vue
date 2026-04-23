<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRoute, useRouter } from 'vue-router';
import { convertErrorTypeToAlertListType, formatCurrency, formatDate } from '@/utils/helper';
import { TwoColumnsLayout } from '@/components/Base/Form/FormLayout';
import { TwoColumnsLayoutCards } from '@/components/Base/Form/FormLayout/TwoColumnsLayout.vue';
import { CardState } from '@/types/enums/CardState';
import { ViewMode } from '@/types/enums/ViewMode';
import { useSelectedUserLocationStore } from '@/stores/selected-user-location';
import {
  FormErrorMessages,
  FormInput,
  FormInputCode,
  FormInputCurrency,
  FormInputDateTimeAuto,
  FormLabel,
  FormSelectSearch,
  FormSwitch,
  FormTextarea,
} from '@/components/Base/Form';
import DebtService from '@/services/DebtService';
import DebtCategoryService from '@/services/DebtCategoryService';
import DebtCreditorService from '@/services/DebtCreditorService';
import SupplierService from '@/services/SupplierService';
import CashAccountService from '@/services/CashAccountService';
import Button from '@/components/Base/Button';
import Lucide from '@/components/Base/Lucide';
import { ErrorCode } from '@/types/enums/ErrorCode';
import type { DropDownOption } from '@/types/models/DropDownOption';
import type { AlertPlaceholderProps } from '@/components/AlertPlaceholder/AlertPlaceholder.vue';
import type { Debt } from '@/types/models/Debt';
import type { DebtCategory } from '@/types/models/DebtCategory';
import type { DebtCreditor } from '@/types/models/DebtCreditor';
import type { Supplier } from '@/types/models/Supplier';
import type { CashAccount } from '@/types/models/CashAccount';
import type { DebtPayment } from '@/types/models/DebtPayment';

type DebtPaymentFormItem = {
  id?: string | null;
  code: string;
  date: string;
  cash_account_id: string;
  amount: number;
  remarks: string;
};

type PartyType = 'creditor' | 'supplier';

const { t } = useI18n();
const route = useRoute();
const router = useRouter();
const selectedUserLocationStore = useSelectedUserLocationStore();

const emits = defineEmits(['mode-state', 'loading-state', 'update-profile', 'show-alertplaceholder']);

const isUserLocationSelected = computed(() => selectedUserLocationStore.isUserLocationSelected);
const selectedUserLocation = computed(() => selectedUserLocationStore.selectedUserLocation);

const debtService = new DebtService();
const debtCategoryService = new DebtCategoryService();
const debtCreditorService = new DebtCreditorService();
const supplierService = new SupplierService();
const cashAccountService = new CashAccountService();

const cards = ref<Array<TwoColumnsLayoutCards>>([
  { title: 'views.debt.field_groups.company_info', state: CardState.Expanded },
  { title: 'views.debt.field_groups.debt_data', state: CardState.Expanded },
  { title: 'views.debt.field_groups.payments', state: CardState.Expanded },
  { title: '', state: CardState.Hidden, id: 'button' },
]);

const debtForm = debtService.useDebtEditForm(route.params.ulid.toString());
const debtData = ref<Debt | null>(null);
const partyType = ref<PartyType>('creditor');

const categoryDDL = ref<Array<DropDownOption> | null>(null);
const categorySearch = ref<string>('');
const categoryOptions = computed(() =>
  (categoryDDL.value ?? []).map((item) => ({
    value: item.code,
    label: item.name,
  })),
);

const creditorDDL = ref<Array<DropDownOption> | null>(null);
const creditorSearch = ref<string>('');
const creditorOptions = computed(() =>
  (creditorDDL.value ?? []).map((item) => ({
    value: item.code,
    label: item.name,
  })),
);

const supplierDDL = ref<Array<DropDownOption> | null>(null);
const supplierSearch = ref<string>('');
const supplierOptions = computed(() =>
  (supplierDDL.value ?? []).map((item) => ({
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

const debtPaymentsForm = computed<DebtPaymentFormItem[]>(
  () => debtForm.payments as DebtPaymentFormItem[],
);
const paymentCashAccountSearch = ref<string>('');
const selectedPartyField = computed(() => (partyType.value === 'creditor' ? 'creditor_id' : 'supplier_id'));
const selectedPartyOptions = computed(() =>
  partyType.value === 'creditor' ? creditorOptions.value : supplierOptions.value,
);
const selectedPartyLabel = computed(() =>
  partyType.value === 'creditor'
    ? t('views.debt.fields.creditor')
    : t('views.debt.fields.supplier'),
);
const partySearch = computed({
  get: () => (partyType.value === 'creditor' ? creditorSearch.value : supplierSearch.value),
  set: (value: string) => {
    if (partyType.value === 'creditor') {
      creditorSearch.value = value;
      return;
    }

    supplierSearch.value = value;
  },
});
const isSupplierParty = computed({
  get: () => partyType.value === 'supplier',
  set: (value: boolean) => {
    setPartyType(value ? 'supplier' : 'creditor');
  },
});

const amountTotalPreview = computed(
  () => Number(debtForm.direct_amount_received ?? 0) + Number(debtForm.opening_amount_due ?? 0),
);
const amountPaidByCashAccountPreview = computed(() =>
  debtPaymentsForm.value.reduce((total, payment) => total + Number(payment.amount ?? 0), 0),
);
const amountPaidByStockAdjustmentPreview = computed(() => 0);
const amountDuePreview = computed(() =>
  Math.max(
    0,
    amountTotalPreview.value
      - amountPaidByCashAccountPreview.value
      - amountPaidByStockAdjustmentPreview.value,
  ),
);
const invalidDebtField = (field: string) => debtForm.invalid(field as any);
const validateDebtField = (field: string) => debtForm.validate(field as any);
const normalizeErrorMessages = (messages: string | string[] | undefined): string | undefined =>
  Array.isArray(messages) ? messages.join(' ') : messages;
const getDebtFieldErrors = (field: string) =>
  normalizeErrorMessages(
    (debtForm.errors as Record<string, string | string[] | undefined>)[field],
  );
const clearTopAlertPlaceholder = () => {
  showAlertPlaceholder('hidden', '', null);
};
const clearStaleValidationErrors = (fields: string[]) => {
  fields.forEach((field) => debtForm.forgetError(field as any));
  clearTopAlertPlaceholder();
};

onMounted(async () => {
  emits('mode-state', ViewMode.FORM_EDIT);

  if (!isUserLocationSelected.value) {
    router.push({
      name: 'side-menu-error-code',
      params: { code: ErrorCode.USERLOCATION_REQUIRED },
    });
    return;
  }

  await loadData();
  await Promise.all([
    loadCategoryDDL(),
    loadCreditorDDL(),
    loadSupplierDDL(),
    loadCashAccountDDL(),
  ]);
});

const loadData = async () => {
  emits('loading-state', true);
  const result = await debtService.read(route.params.ulid.toString());
  emits('loading-state', false);

  if (!result.success || !result.data) {
    router.push({ name: 'side-menu-debt-list' });
    return;
  }

  debtData.value = result.data;

  debtForm.setData({
    company_id: result.data.company?.id ?? '',
    branch_id: result.data.branch?.id ?? '',
    code: result.data.code,
    date: formatDate(result.data.date, 'YYYY-MM-DD HH:mm:ss'),
    category_id: result.data.category?.id ?? '',
    creditor_id: result.data.creditor?.id ?? '',
    supplier_id: result.data.supplier?.id ?? '',
    cash_account_id: result.data.cash_account?.id ?? '',
    direct_amount_received: result.data.direct_amount_received,
    opening_amount_due: result.data.opening_amount_due,
    due_days: result.data.due_days,
    remarks: result.data.remarks ?? '',
    delete_payment_ids: [],
    payments: (result.data.payments ?? []).map((payment: DebtPayment) => ({
      id: payment.id,
      code: payment.code,
      date: formatDate(payment.date, 'YYYY-MM-DD HH:mm:ss'),
      cash_account_id: payment.cash_account?.id ?? '',
      amount: payment.amount,
      remarks: payment.remarks ?? '',
    })),
  } as any);

  syncPartyTypeFromForm();
};

const syncPartyTypeFromForm = () => {
  if (debtForm.supplier_id && !debtForm.creditor_id) {
    partyType.value = 'supplier';
    return;
  }

  partyType.value = 'creditor';
};

const loadCategoryDDL = async (search = '') => {
  if (!selectedUserLocation.value) return;

  const result = await debtCategoryService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    search,
    include_id: debtData.value?.category?.id,
    refresh: false,
    limit: 20,
  });

  if (result.success && result.data) {
    categoryDDL.value = result.data.data.map((item: DebtCategory) => ({
      code: item.id,
      name: item.name,
    }));
  }
};

const loadCreditorDDL = async (search = '') => {
  if (!selectedUserLocation.value) return;

  const result = await debtCreditorService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    search,
    include_id: debtData.value?.creditor?.id ?? undefined,
    refresh: false,
    limit: 20,
  });

  if (result.success && result.data) {
    creditorDDL.value = result.data.data.map((item: DebtCreditor) => ({
      code: item.id,
      name: item.code ? `${item.code} - ${item.name}` : item.name,
    }));
  }
};

const loadSupplierDDL = async (search = '') => {
  if (!selectedUserLocation.value) return;

  const result = await supplierService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    search,
    status: undefined,
    include_id: debtData.value?.supplier?.id ?? undefined,
    refresh: false,
    limit: 20,
  });

  if (result.success && result.data) {
    supplierDDL.value = result.data.data.map((item: Supplier) => ({
      code: item.id,
      name: item.code ? `${item.code} - ${item.name}` : item.name,
    }));
  }
};

const loadCashAccountDDL = async (search = '') => {
  if (!selectedUserLocation.value) return;

  const result = await cashAccountService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    branch_id: selectedUserLocation.value.branch.id,
    is_bank: null,
    search,
    include_id: debtData.value?.cash_account?.id ?? undefined,
    with_remaining_balance: null,
    refresh: false,
    limit: 20,
  });

  if (result.success && result.data) {
    cashAccountDDL.value = result.data.data.map((item: CashAccount) => ({
      code: item.id,
      name: item.code ? `${item.code} - ${item.name}` : item.name,
    }));
  }
};

const setPartyType = (value: PartyType) => {
  if (partyType.value === value) return;

  partyType.value = value;

  if (value === 'creditor') {
    debtForm.setData({ supplier_id: '' });
    clearStaleValidationErrors(['supplier_id', 'form']);
    supplierSearch.value = '';
    return;
  }

  debtForm.setData({ creditor_id: '' });
  clearStaleValidationErrors(['creditor_id', 'form']);
  creditorSearch.value = '';
};

const handleExpandCard = (index: number) => {
  cards.value[index].state =
    cards.value[index].state === CardState.Collapsed ? CardState.Expanded : CardState.Collapsed;
};

const scrollToError = (id: string): void => {
  const el = document.getElementById(id);
  if (!el) return;
  el.scrollIntoView({ behavior: 'smooth', block: 'center' });
};

const setCode = () => {
  debtForm.forgetError('code');
  debtForm.setData({
    code: debtForm.code === '_AUTO_' ? '' : '_AUTO_',
  });
};

const clearCategory = () => {
  debtForm.setData({ category_id: '' });
  clearStaleValidationErrors(['category_id', 'form']);
};

const clearCashAccount = () => {
  debtForm.setData({ cash_account_id: '' });
  debtForm.forgetError('cash_account_id');
};

const clearParty = () => {
  clearStaleValidationErrors([selectedPartyField.value, 'form']);

  if (partyType.value === 'creditor') {
    debtForm.setData({ creditor_id: '' });
    return;
  }

  debtForm.setData({ supplier_id: '' });
};

const handlePartySearch = async (search: string) => {
  if (partyType.value === 'creditor') {
    await loadCreditorDDL(search);
    return;
  }

  await loadSupplierDDL(search);
};

const setPaymentCode = (index: number) => {
  debtForm.forgetError(`payments.${index}.code` as any);
  debtPaymentsForm.value[index].code =
    debtPaymentsForm.value[index].code === '_AUTO_' ? '' : '_AUTO_';
};

const clearPaymentCashAccount = (index: number) => {
  const payment = debtPaymentsForm.value[index];
  if (!payment) return;

  payment.cash_account_id = '';
  validateDebtField(`payments.${index}.cash_account_id`);
};

const addPayment = () => {
  debtPaymentsForm.value.push({
    id: null,
    code: '_AUTO_',
    date: '_AUTO_',
    cash_account_id: '',
    amount: 0,
    remarks: '',
  });

  Object.keys(debtForm.errors).forEach((key) => {
    if (key === 'payments' || key.startsWith('payments.')) {
      debtForm.forgetError(key as any);
    }
  });
};

const removePayment = (index: number) => {
  const payment = debtPaymentsForm.value[index];

  if (payment?.id && !debtForm.delete_payment_ids.includes(payment.id)) {
    debtForm.delete_payment_ids.push(payment.id);
  }

  debtPaymentsForm.value.splice(index, 1);

  Object.keys(debtForm.errors).forEach((key) => {
    if (key === 'payments' || key.startsWith('payments.')) {
      debtForm.forgetError(key as any);
    }
  });
};

const onSubmit = async () => {
  emits('loading-state', true);

  await debtForm
    .submit()
    .then(() => {
      showAlertPlaceholder('hidden', '', null);
      emits('update-profile');
      router.push({ name: 'side-menu-debt-list' });
    })
    .catch((error) => {
      showAlertPlaceholder('danger', '', convertErrorTypeToAlertListType(error));
    })
    .finally(() => {
      emits('loading-state', false);
    });
};

const resetForm = async () => {
  debtForm.reset();
  debtForm.setErrors({});
  categorySearch.value = '';
  creditorSearch.value = '';
  supplierSearch.value = '';
  cashAccountSearch.value = '';
  paymentCashAccountSearch.value = '';
  await loadData();
  await Promise.all([
    loadCategoryDDL(),
    loadCreditorDDL(),
    loadSupplierDDL(),
    loadCashAccountDDL(),
  ]);
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

watch(
  () => debtForm.category_id,
  () => {
    clearStaleValidationErrors(['category_id']);
  },
);

watch(
  () => [debtForm.direct_amount_received, debtForm.opening_amount_due],
  () => {
    clearStaleValidationErrors(['form']);
  },
);

watch(
  () => [debtForm.creditor_id, debtForm.supplier_id],
  () => {
    clearStaleValidationErrors(['form']);
  },
);
</script>

<template>
  <form id="debtForm" @submit.prevent="onSubmit">
    <TwoColumnsLayout :cards="cards" :using-side-tab="false" @handle-expand-card="handleExpandCard">
      <template #card-items-0>
        <div class="p-5">
          <div class="grid grid-cols-12 gap-4 gap-y-3">
            <div class="col-span-12 md:col-span-6">
              <FormLabel>
                {{ selectedUserLocation.company.code }}
                <br />
                {{ selectedUserLocation.company.name }}
              </FormLabel>
              <FormInputCurrency type="hidden" v-model="debtForm.company_id" />
            </div>
            <div class="col-span-12 md:col-span-6">
              <FormLabel>
                {{ selectedUserLocation.branch.code }}
                <br />
                {{ selectedUserLocation.branch.name }}
              </FormLabel>
              <FormInput type="hidden" v-model="debtForm.branch_id" />
            </div>
          </div>
        </div>
      </template>

      <template #card-items-1>
        <div class="p-5">
          <div class="grid grid-cols-12 gap-4 gap-y-3">
            <div class="col-span-12 lg:col-span-3">
              <FormLabel :class="{ 'text-danger': debtForm.invalid('code') }">
                {{ t('views.debt.fields.code') }}
              </FormLabel>
              <FormInputCode
                id="code"
                v-model="debtForm.code"
                :class="{ 'border-danger': debtForm.invalid('code') }"
                :placeholder="t('views.debt.fields.code')"
                @set-auto="setCode"
                @change="debtForm.validate('code')"
              />
              <FormErrorMessages :messages="debtForm.errors.code" />
            </div>

            <div class="col-span-12 lg:col-span-3">
              <FormLabel :class="{ 'text-danger': debtForm.invalid('date') }">
                {{ t('views.debt.fields.date') }}
              </FormLabel>
              <FormInputDateTimeAuto
                id="date"
                v-model="debtForm.date"
                :class="{ 'border-danger': debtForm.invalid('date') }"
                :placeholder="t('views.debt.fields.date')"
                @change="debtForm.validate('date')"
              />
              <FormErrorMessages :messages="debtForm.errors.date" />
            </div>

            <div class="col-span-12 lg:col-span-6">
              <FormLabel :class="{ 'text-danger': debtForm.invalid('category_id') }">
                {{ t('views.debt.fields.category') }}
              </FormLabel>
              <FormSelectSearch
                v-model="debtForm.category_id"
                v-model:search="categorySearch"
                :options="categoryOptions"
                :placeholder="t('components.dropdown.placeholder')"
                :class="{ 'border-danger': debtForm.invalid('category_id') }"
                @change="debtForm.validate('category_id')"
                @search="loadCategoryDDL"
                @clear="clearCategory"
              />
              <FormErrorMessages :messages="debtForm.errors.category_id" />
            </div>

            <div class="col-span-12 lg:col-span-4">
              <FormLabel>
                {{ t('views.debt.fields.party_type') }}
              </FormLabel>
              <div class="flex items-center gap-3 rounded-md border border-slate-200 px-3 py-2 dark:border-darkmode-400">
                <span
                  class="text-sm"
                  :class="partyType === 'creditor' ? 'font-medium text-primary' : 'text-slate-500'"
                >
                  {{ t('views.debt.party_types.creditor') }}
                </span>
                <FormSwitch>
                  <FormSwitch.Input v-model="isSupplierParty" type="checkbox" />
                </FormSwitch>
                <span
                  class="text-sm"
                  :class="partyType === 'supplier' ? 'font-medium text-primary' : 'text-slate-500'"
                >
                  {{ t('views.debt.party_types.supplier') }}
                </span>
              </div>
            </div>

            <div class="col-span-12 lg:col-span-8">
              <FormLabel :class="{ 'text-danger': invalidDebtField(selectedPartyField) }">
                {{ selectedPartyLabel }}
              </FormLabel>
              <FormSelectSearch
                v-model="debtForm[selectedPartyField]"
                v-model:search="partySearch"
                :options="selectedPartyOptions"
                :placeholder="t('components.dropdown.placeholder')"
                :class="{ 'border-danger': invalidDebtField(selectedPartyField) }"
                @change="validateDebtField(selectedPartyField)"
                @search="handlePartySearch"
                @clear="clearParty"
              />
              <FormErrorMessages :messages="getDebtFieldErrors(selectedPartyField)" />
            </div>

            <div class="col-span-12 lg:col-span-4">
              <FormLabel :class="{ 'text-danger': debtForm.invalid('cash_account_id') }">
                {{ t('views.debt.fields.cash_account') }}
              </FormLabel>
              <FormSelectSearch
                v-model="debtForm.cash_account_id"
                v-model:search="cashAccountSearch"
                :options="cashAccountOptions"
                :placeholder="t('components.dropdown.placeholder')"
                :class="{ 'border-danger': debtForm.invalid('cash_account_id') }"
                @change="debtForm.validate('cash_account_id')"
                @search="loadCashAccountDDL"
                @clear="clearCashAccount"
              />
              <FormErrorMessages :messages="debtForm.errors.cash_account_id" />
            </div>

            <div class="col-span-12 lg:col-span-2">
              <FormLabel :class="{ 'text-danger': debtForm.invalid('direct_amount_received') }">
                {{ t('views.debt.fields.direct_amount_received') }}
              </FormLabel>
              <FormInputCurrency
                id="direct_amount_received"
                v-model="debtForm.direct_amount_received"
                :allow-negative="false"
                :class="{ 'border-danger': debtForm.invalid('direct_amount_received') }"
                :placeholder="t('views.debt.fields.direct_amount_received')"
                @change="debtForm.validate('direct_amount_received')"
              />
              <FormErrorMessages :messages="debtForm.errors.direct_amount_received" />
            </div>

            <div class="col-span-12 lg:col-span-2">
              <FormLabel :class="{ 'text-danger': debtForm.invalid('opening_amount_due') }">
                {{ t('views.debt.fields.opening_amount_due') }}
              </FormLabel>
              <FormInputCurrency
                id="opening_amount_due"
                v-model="debtForm.opening_amount_due"
                :allow-negative="false"
                :class="{ 'border-danger': debtForm.invalid('opening_amount_due') }"
                :placeholder="t('views.debt.fields.opening_amount_due')"
                @change="debtForm.validate('opening_amount_due')"
              />
              <FormErrorMessages :messages="debtForm.errors.opening_amount_due" />
            </div>

            <div class="col-span-12 lg:col-span-2">
              <FormLabel :class="{ 'text-danger': debtForm.invalid('due_days') }">
                {{ t('views.debt.fields.due_days') }}
              </FormLabel>
              <FormInputCurrency
                id="due_days"
                v-model="debtForm.due_days"
                :allow-negative="false"
                :class="{ 'border-danger': debtForm.invalid('due_days') }"
                :placeholder="t('views.debt.fields.due_days')"
                @change="debtForm.validate('due_days')"
              />
              <FormErrorMessages :messages="debtForm.errors.due_days" />
            </div>

            <div class="col-span-12 lg:col-span-2">
              <FormLabel>{{ t('views.debt.fields.amount_total') }}</FormLabel>
              <FormInputCurrency :model-value="amountTotalPreview" readonly :allow-negative="false" />
            </div>

            <div class="col-span-12 lg:col-span-2">
              <FormLabel>{{ t('views.debt.fields.amount_due') }}</FormLabel>
              <FormInputCurrency :model-value="amountDuePreview" readonly :allow-negative="false" />
            </div>

            <div class="col-span-12 lg:col-span-4">
              <FormLabel>{{ t('views.debt.fields.amount_paid_by_cash_account') }}</FormLabel>
              <FormInputCurrency :model-value="amountPaidByCashAccountPreview" readonly :allow-negative="false" />
            </div>

            <div class="col-span-12 lg:col-span-4">
              <FormLabel>{{ t('views.debt.fields.amount_paid_by_stock_adjustment') }}</FormLabel>
              <FormInputCurrency
                :model-value="amountPaidByStockAdjustmentPreview"
                readonly
                :allow-negative="false"
              />
            </div>

            <div class="col-span-12">
              <FormLabel :class="{ 'text-danger': debtForm.invalid('remarks') }">
                {{ t('views.debt.fields.remarks') }}
              </FormLabel>
              <FormTextarea
                id="remarks"
                v-model="debtForm.remarks"
                rows="3"
                :class="{ 'border-danger': debtForm.invalid('remarks') }"
                :placeholder="t('views.debt.fields.remarks')"
                @change="debtForm.validate('remarks')"
              />
              <FormErrorMessages :messages="debtForm.errors.remarks" />
            </div>
          </div>
        </div>
      </template>

      <template #card-items-2>
        <div class="p-5 space-y-4">
          <div class="grid grid-cols-12 gap-4 gap-y-3">
            <div class="col-span-12 md:col-span-4">
              <FormLabel>{{ t('views.debt.fields.amount_total') }}</FormLabel>
              <div class="rounded-md border border-slate-200 px-3 py-2 text-right text-sm font-medium dark:border-darkmode-400">
                {{ formatCurrency(amountTotalPreview) }}
              </div>
            </div>
            <div class="col-span-12 md:col-span-4">
              <FormLabel>{{ t('views.debt.fields.amount_paid_by_cash_account') }}</FormLabel>
              <div class="rounded-md border border-slate-200 px-3 py-2 text-right text-sm font-medium dark:border-darkmode-400">
                {{ formatCurrency(amountPaidByCashAccountPreview) }}
              </div>
            </div>
            <div class="col-span-12 md:col-span-4">
              <FormLabel>{{ t('views.debt.fields.amount_due') }}</FormLabel>
              <div class="rounded-md border border-slate-200 px-3 py-2 text-right text-sm font-medium dark:border-darkmode-400">
                {{ formatCurrency(amountDuePreview) }}
              </div>
            </div>
          </div>

          <FormErrorMessages :messages="(debtForm.errors as any).payments" />
          <FormErrorMessages :messages="(debtForm.errors as any).delete_payment_ids" />

          <div v-if="debtPaymentsForm.length === 0" class="text-right text-sm text-slate-500">
            {{ t('components.data-list.data_not_found') }}
          </div>

          <div v-else class="space-y-4">
            <div
              v-for="(payment, index) in debtPaymentsForm"
              :key="payment.id ?? `debt-payment-${index}`"
              class="rounded-md border border-slate-200 p-4 dark:border-darkmode-400"
            >
              <div class="grid grid-cols-12 gap-4 gap-y-3">
                <div class="col-span-12 md:col-span-4 lg:col-span-2">
                  <FormLabel :class="{ 'text-danger': invalidDebtField(`payments.${index}.code`) }">
                    {{ t('views.debt.fields.payment_code') }}
                  </FormLabel>
                  <FormInputCode
                    :id="`payments.${index}.code`"
                    v-model="payment.code"
                    :class="{ 'border-danger': invalidDebtField(`payments.${index}.code`) }"
                    :placeholder="t('views.debt.fields.payment_code')"
                    @set-auto="setPaymentCode(index)"
                    @change="validateDebtField(`payments.${index}.code`)"
                  />
                  <FormErrorMessages :messages="getDebtFieldErrors(`payments.${index}.code`)" />
                </div>

                <div class="col-span-12 md:col-span-8 lg:col-span-3">
                  <FormLabel :class="{ 'text-danger': invalidDebtField(`payments.${index}.date`) }">
                    {{ t('views.debt.fields.payment_date') }}
                  </FormLabel>
                  <FormInputDateTimeAuto
                    :id="`payments.${index}.date`"
                    v-model="payment.date"
                    :class="{ 'border-danger': invalidDebtField(`payments.${index}.date`) }"
                    :placeholder="t('views.debt.fields.payment_date')"
                    @change="validateDebtField(`payments.${index}.date`)"
                  />
                  <FormErrorMessages :messages="getDebtFieldErrors(`payments.${index}.date`)" />
                </div>

                <div class="col-span-12 lg:col-span-4">
                  <FormLabel :class="{ 'text-danger': invalidDebtField(`payments.${index}.cash_account_id`) }">
                    {{ t('views.debt.fields.payment_cash_account') }}
                  </FormLabel>
                  <FormSelectSearch
                    :id="`payments.${index}.cash_account_id`"
                    v-model="payment.cash_account_id"
                    v-model:search="paymentCashAccountSearch"
                    :options="cashAccountOptions"
                    :placeholder="t('components.dropdown.placeholder')"
                    :class="{ 'border-danger': invalidDebtField(`payments.${index}.cash_account_id`) }"
                    @change="validateDebtField(`payments.${index}.cash_account_id`)"
                    @search="loadCashAccountDDL"
                    @clear="clearPaymentCashAccount(index)"
                  />
                  <FormErrorMessages :messages="getDebtFieldErrors(`payments.${index}.cash_account_id`)" />
                </div>

                <div class="col-span-12 md:col-span-8 lg:col-span-3">
                  <FormLabel :class="{ 'text-danger': invalidDebtField(`payments.${index}.amount`) }">
                    {{ t('views.debt.fields.payment_amount') }}
                  </FormLabel>
                  <div class="flex items-start gap-2">
                    <div class="min-w-0 flex-1">
                      <FormInputCurrency
                        :id="`payments.${index}.amount`"
                        v-model="payment.amount"
                        :allow-negative="false"
                        :class="{ 'border-danger': invalidDebtField(`payments.${index}.amount`) }"
                        @change="validateDebtField(`payments.${index}.amount`)"
                      />
                    </div>
                    <div class="shrink-0">
                      <Button
                        type="button"
                        variant="outline-secondary"
                        class="flex h-[38px] w-[38px] min-w-0 items-center justify-center"
                        @click="removePayment(index)"
                      >
                        <Lucide icon="Trash2" class="h-4 w-4 text-danger" />
                      </Button>
                    </div>
                  </div>
                  <FormErrorMessages :messages="getDebtFieldErrors(`payments.${index}.amount`)" />
                </div>

                <div class="col-span-12">
                  <FormLabel :class="{ 'text-danger': invalidDebtField(`payments.${index}.remarks`) }">
                    {{ t('views.debt.fields.payment_remarks') }}
                  </FormLabel>
                  <FormTextarea
                    :id="`payments.${index}.remarks`"
                    v-model="payment.remarks"
                    rows="2"
                    :class="{ 'border-danger': invalidDebtField(`payments.${index}.remarks`) }"
                    :placeholder="t('views.debt.fields.payment_remarks')"
                    @change="validateDebtField(`payments.${index}.remarks`)"
                  />
                  <FormErrorMessages :messages="getDebtFieldErrors(`payments.${index}.remarks`)" />
                </div>
              </div>
            </div>
          </div>

          <div class="flex justify-end">
            <Button type="button" variant="outline-primary" @click="addPayment">
              <Lucide icon="Plus" class="mr-1 h-4 w-4" />
              {{ t('components.buttons.create_new') }}
            </Button>
          </div>
        </div>
      </template>

      <template #card-items-button>
        <div class="flex gap-4">
          <Button
            type="submit"
            href="#"
            variant="primary"
            class="w-28 shadow-md"
            :disabled="debtForm.validating"
          >
            <Lucide v-if="debtForm.validating" icon="Loader" class="animate-spin" />
            <template v-else>
              {{ t('components.buttons.submit') }}
            </template>
          </Button>
          <Button type="button" href="#" variant="soft-secondary" class="w-28 shadow-md" @click="resetForm">
            {{ t('components.buttons.reset') }}
          </Button>
        </div>
      </template>
    </TwoColumnsLayout>
  </form>
</template>
