<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import { debounce } from 'lodash';
import { convertErrorTypeToAlertListType, formatCurrency } from '@/utils/helper';
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
import LiabilityService from '@/services/LiabilityService';
import LiabilityCategoryService from '@/services/LiabilityCategoryService';
import LiabilityCreditorService from '@/services/LiabilityCreditorService';
import SupplierService from '@/services/SupplierService';
import CashAccountService from '@/services/CashAccountService';
import CacheService from '@/services/CacheService';
import Button from '@/components/Base/Button';
import Lucide from '@/components/Base/Lucide';
import { ErrorCode } from '@/types/enums/ErrorCode';
import type { DropDownOption } from '@/types/models/DropDownOption';
import type { AlertPlaceholderProps } from '@/components/AlertPlaceholder/AlertPlaceholder.vue';
import type { LiabilityCategory } from '@/types/models/LiabilityCategory';
import type { LiabilityCreditor } from '@/types/models/LiabilityCreditor';
import type { Supplier } from '@/types/models/Supplier';
import type { CashAccount } from '@/types/models/CashAccount';

type LiabilityPaymentFormItem = {
  id?: string | null;
  code: string;
  date: string;
  cash_account_id: string;
  amount: number;
  remarks: string;
};

type PartyType = 'creditor' | 'supplier';

const { t } = useI18n();
const router = useRouter();
const selectedUserLocationStore = useSelectedUserLocationStore();

const emits = defineEmits(['mode-state', 'loading-state', 'update-profile', 'show-alertplaceholder']);

const isUserLocationSelected = computed(() => selectedUserLocationStore.isUserLocationSelected);
const selectedUserLocation = computed(() => selectedUserLocationStore.selectedUserLocation);

const liabilityService = new LiabilityService();
const liabilityCategoryService = new LiabilityCategoryService();
const liabilityCreditorService = new LiabilityCreditorService();
const supplierService = new SupplierService();
const cashAccountService = new CashAccountService();
const cacheService = new CacheService();

const cards = ref<Array<TwoColumnsLayoutCards>>([
  { title: 'views.liability.field_groups.company_info', state: CardState.Expanded },
  { title: 'views.liability.field_groups.liability_data', state: CardState.Expanded },
  { title: 'views.liability.field_groups.payments', state: CardState.Expanded },
  { title: '', state: CardState.Hidden, id: 'button' },
]);

const liabilityForm = liabilityService.useLiabilityCreateForm();
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

const liabilityPaymentsForm = computed<LiabilityPaymentFormItem[]>(
  () => liabilityForm.payments as LiabilityPaymentFormItem[],
);
const paymentCashAccountSearch = ref<string>('');
const selectedPartyField = computed(() => (partyType.value === 'creditor' ? 'creditor_id' : 'supplier_id'));
const selectedPartyOptions = computed(() =>
  partyType.value === 'creditor' ? creditorOptions.value : supplierOptions.value,
);
const selectedPartyLabel = computed(() =>
  partyType.value === 'creditor'
    ? t('views.liability.fields.creditor')
    : t('views.liability.fields.supplier'),
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
  () => Number(liabilityForm.amount_received ?? 0) + Number(liabilityForm.amount_payable ?? 0),
);
const amountPaidByCashAccountPreview = computed(() =>
  liabilityPaymentsForm.value.reduce((total, payment) => total + Number(payment.amount ?? 0), 0),
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
const invalidLiabilityField = (field: string) => liabilityForm.invalid(field as any);
const validateLiabilityField = (field: string) => liabilityForm.validate(field as any);
const normalizeErrorMessages = (messages: string | string[] | undefined): string | undefined =>
  Array.isArray(messages) ? messages.join(' ') : messages;
const getLiabilityFieldErrors = (field: string) =>
  normalizeErrorMessages(
    (liabilityForm.errors as Record<string, string | string[] | undefined>)[field],
  );

onMounted(async () => {
  emits('mode-state', ViewMode.FORM_CREATE);

  if (!isUserLocationSelected.value) {
    router.push({
      name: 'side-menu-error-code',
      params: { code: ErrorCode.USERLOCATION_REQUIRED },
    });
    return;
  }

  loadFromCache();
  syncPartyTypeFromForm();
  setLocationData();

  await Promise.all([
    loadCategoryDDL(),
    loadCreditorDDL(),
    loadSupplierDDL(),
    loadCashAccountDDL(),
  ]);
});

const setLocationData = () => {
  liabilityForm.setData({
    company_id: selectedUserLocation.value.company.id,
    branch_id: selectedUserLocation.value.branch.id,
  });
};

const loadFromCache = () => {
  const data = cacheService.getLastEntity('LIABILITY_CREATE') as Record<string, unknown> | null;
  if (!data) return;

  liabilityForm.setData(data);
};

const syncPartyTypeFromForm = () => {
  if (liabilityForm.supplier_id && !liabilityForm.creditor_id) {
    partyType.value = 'supplier';
    return;
  }

  partyType.value = 'creditor';
};

const loadCategoryDDL = async (search = '') => {
  if (!selectedUserLocation.value) return;

  const result = await liabilityCategoryService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    search,
    include_id: liabilityForm.category_id || undefined,
    refresh: false,
    limit: 20,
  });

  if (result.success && result.data) {
    categoryDDL.value = result.data.data.map((item: LiabilityCategory) => ({
      code: item.id,
      name: item.name,
    }));
  }
};

const loadCreditorDDL = async (search = '') => {
  if (!selectedUserLocation.value) return;

  const result = await liabilityCreditorService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    search,
    include_id: liabilityForm.creditor_id || undefined,
    refresh: false,
    limit: 20,
  });

  if (result.success && result.data) {
    creditorDDL.value = result.data.data.map((item: LiabilityCreditor) => ({
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
    include_id: liabilityForm.supplier_id || undefined,
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
    include_id: liabilityForm.cash_account_id || undefined,
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
    liabilityForm.setData({ supplier_id: '' });
    liabilityForm.forgetError('supplier_id');
    supplierSearch.value = '';
    return;
  }

  liabilityForm.setData({ creditor_id: '' });
  liabilityForm.forgetError('creditor_id');
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
  liabilityForm.forgetError('code');
  liabilityForm.setData({
    code: liabilityForm.code === '_AUTO_' ? '' : '_AUTO_',
  });
};

const clearCategory = () => {
  liabilityForm.setData({ category_id: '' });
  liabilityForm.forgetError('category_id');
};

const clearCashAccount = () => {
  liabilityForm.setData({ cash_account_id: '' });
  liabilityForm.forgetError('cash_account_id');
};

const clearParty = () => {
  liabilityForm.forgetError(selectedPartyField.value as any);

  if (partyType.value === 'creditor') {
    liabilityForm.setData({ creditor_id: '' });
    return;
  }

  liabilityForm.setData({ supplier_id: '' });
};

const handlePartySearch = async (search: string) => {
  if (partyType.value === 'creditor') {
    await loadCreditorDDL(search);
    return;
  }

  await loadSupplierDDL(search);
};

const setPaymentCode = (index: number) => {
  liabilityForm.forgetError(`payments.${index}.code` as any);
  liabilityPaymentsForm.value[index].code =
    liabilityPaymentsForm.value[index].code === '_AUTO_' ? '' : '_AUTO_';
};

const clearPaymentCashAccount = (index: number) => {
  const payment = liabilityPaymentsForm.value[index];
  if (!payment) return;

  payment.cash_account_id = '';
  validateLiabilityField(`payments.${index}.cash_account_id`);
};

const addPayment = () => {
  liabilityPaymentsForm.value.push({
    code: '_AUTO_',
    date: '_AUTO_',
    cash_account_id: '',
    amount: 0,
    remarks: '',
  });

  Object.keys(liabilityForm.errors).forEach((key) => {
    if (key === 'payments' || key.startsWith('payments.')) {
      liabilityForm.forgetError(key as any);
    }
  });
};

const removePayment = (index: number) => {
  liabilityPaymentsForm.value.splice(index, 1);

  Object.keys(liabilityForm.errors).forEach((key) => {
    if (key === 'payments' || key.startsWith('payments.')) {
      liabilityForm.forgetError(key as any);
    }
  });
};

const onSubmit = async () => {
  if (liabilityForm.hasErrors) {
    const firstErrorKey = Object.keys(liabilityForm.errors)[0];
    if (firstErrorKey) {
      scrollToError(firstErrorKey);
    }
    return;
  }

  emits('loading-state', true);

  await liabilityForm
    .submit()
    .then(() => {
      cacheService.removeLastEntity('LIABILITY_CREATE');
      resetForm();
      showAlertPlaceholder('hidden', '', null);
      emits('update-profile');
      router.push({ name: 'side-menu-liability-list' });
    })
    .catch((error) => {
      showAlertPlaceholder('danger', '', convertErrorTypeToAlertListType(error));
    })
    .finally(() => {
      emits('loading-state', false);
    });
};

const resetForm = () => {
  liabilityForm.reset();
  liabilityForm.setErrors({});
  liabilityForm.setData({
    code: '_AUTO_',
    date: '_AUTO_',
    creditor_id: '',
    supplier_id: '',
    cash_account_id: '',
    amount_received: 0,
    amount_payable: 0,
    due_days: 0,
    remarks: '',
    payments: [],
  });
  partyType.value = 'creditor';
  categorySearch.value = '';
  creditorSearch.value = '';
  supplierSearch.value = '';
  cashAccountSearch.value = '';
  paymentCashAccountSearch.value = '';
  setLocationData();
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
  liabilityForm,
  debounce((newValue): void => {
    cacheService.setLastEntity('LIABILITY_CREATE', newValue.data());
  }, 500),
  { deep: true },
);
</script>

<template>
  <form id="liabilityForm" @submit.prevent="onSubmit">
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
              <FormInput type="hidden" v-model="liabilityForm.company_id" />
            </div>
            <div class="col-span-12 md:col-span-6">
              <FormLabel>
                {{ selectedUserLocation.branch.code }}
                <br />
                {{ selectedUserLocation.branch.name }}
              </FormLabel>
              <FormInput type="hidden" v-model="liabilityForm.branch_id" />
            </div>
          </div>
        </div>
      </template>

      <template #card-items-1>
        <div class="p-5">
          <div class="grid grid-cols-12 gap-4 gap-y-3">
            <div class="col-span-12 lg:col-span-3">
              <FormLabel :class="{ 'text-danger': liabilityForm.invalid('code') }">
                {{ t('views.liability.fields.code') }}
              </FormLabel>
              <FormInputCode
                id="code"
                v-model="liabilityForm.code"
                :class="{ 'border-danger': liabilityForm.invalid('code') }"
                :placeholder="t('views.liability.fields.code')"
                @set-auto="setCode"
                @change="liabilityForm.validate('code')"
              />
              <FormErrorMessages :messages="liabilityForm.errors.code" />
            </div>

            <div class="col-span-12 lg:col-span-3">
              <FormLabel :class="{ 'text-danger': liabilityForm.invalid('date') }">
                {{ t('views.liability.fields.date') }}
              </FormLabel>
              <FormInputDateTimeAuto
                id="date"
                v-model="liabilityForm.date"
                :class="{ 'border-danger': liabilityForm.invalid('date') }"
                :placeholder="t('views.liability.fields.date')"
                @change="liabilityForm.validate('date')"
              />
              <FormErrorMessages :messages="liabilityForm.errors.date" />
            </div>

            <div class="col-span-12 lg:col-span-6">
              <FormLabel :class="{ 'text-danger': liabilityForm.invalid('category_id') }">
                {{ t('views.liability.fields.category') }}
              </FormLabel>
              <FormSelectSearch
                v-model="liabilityForm.category_id"
                v-model:search="categorySearch"
                :options="categoryOptions"
                :placeholder="t('components.dropdown.placeholder')"
                :class="{ 'border-danger': liabilityForm.invalid('category_id') }"
                @change="liabilityForm.validate('category_id')"
                @search="loadCategoryDDL"
                @clear="clearCategory"
              />
              <FormErrorMessages :messages="liabilityForm.errors.category_id" />
            </div>

            <div class="col-span-12 lg:col-span-4">
              <FormLabel>
                {{ t('views.liability.fields.party_type') }}
              </FormLabel>
              <div class="flex items-center gap-3 rounded-md border border-slate-200 px-3 py-2 dark:border-darkmode-400">
                <span
                  class="text-sm"
                  :class="partyType === 'creditor' ? 'font-medium text-primary' : 'text-slate-500'"
                >
                  {{ t('views.liability.party_types.creditor') }}
                </span>
                <FormSwitch>
                  <FormSwitch.Input v-model="isSupplierParty" type="checkbox" />
                </FormSwitch>
                <span
                  class="text-sm"
                  :class="partyType === 'supplier' ? 'font-medium text-primary' : 'text-slate-500'"
                >
                  {{ t('views.liability.party_types.supplier') }}
                </span>
              </div>
            </div>

            <div class="col-span-12 lg:col-span-8">
              <FormLabel :class="{ 'text-danger': invalidLiabilityField(selectedPartyField) }">
                {{ selectedPartyLabel }}
              </FormLabel>
              <FormSelectSearch
                v-model="liabilityForm[selectedPartyField]"
                v-model:search="partySearch"
                :options="selectedPartyOptions"
                :placeholder="t('components.dropdown.placeholder')"
                :class="{ 'border-danger': invalidLiabilityField(selectedPartyField) }"
                @change="validateLiabilityField(selectedPartyField)"
                @search="handlePartySearch"
                @clear="clearParty"
              />
              <FormErrorMessages :messages="getLiabilityFieldErrors(selectedPartyField)" />
            </div>

            <div class="col-span-12 lg:col-span-4">
              <FormLabel :class="{ 'text-danger': liabilityForm.invalid('cash_account_id') }">
                {{ t('views.liability.fields.cash_account') }}
              </FormLabel>
              <FormSelectSearch
                v-model="liabilityForm.cash_account_id"
                v-model:search="cashAccountSearch"
                :options="cashAccountOptions"
                :placeholder="t('components.dropdown.placeholder')"
                :class="{ 'border-danger': liabilityForm.invalid('cash_account_id') }"
                @change="liabilityForm.validate('cash_account_id')"
                @search="loadCashAccountDDL"
                @clear="clearCashAccount"
              />
              <FormErrorMessages :messages="liabilityForm.errors.cash_account_id" />
            </div>

            <div class="col-span-12 lg:col-span-2">
              <FormLabel :class="{ 'text-danger': liabilityForm.invalid('amount_received') }">
                {{ t('views.liability.fields.amount_received') }}
              </FormLabel>
              <FormInputCurrency
                id="amount_received"
                v-model="liabilityForm.amount_received"
                :allow-negative="false"
                :class="{ 'border-danger': liabilityForm.invalid('amount_received') }"
                :placeholder="t('views.liability.fields.amount_received')"
                @change="liabilityForm.validate('amount_received')"
              />
              <FormErrorMessages :messages="liabilityForm.errors.amount_received" />
            </div>

            <div class="col-span-12 lg:col-span-2">
              <FormLabel :class="{ 'text-danger': liabilityForm.invalid('amount_payable') }">
                {{ t('views.liability.fields.amount_payable') }}
              </FormLabel>
              <FormInputCurrency
                id="amount_payable"
                v-model="liabilityForm.amount_payable"
                :allow-negative="false"
                :class="{ 'border-danger': liabilityForm.invalid('amount_payable') }"
                :placeholder="t('views.liability.fields.amount_payable')"
                @change="liabilityForm.validate('amount_payable')"
              />
              <FormErrorMessages :messages="liabilityForm.errors.amount_payable" />
            </div>

            <div class="col-span-12 lg:col-span-2">
              <FormLabel :class="{ 'text-danger': liabilityForm.invalid('due_days') }">
                {{ t('views.liability.fields.due_days') }}
              </FormLabel>
              <FormInput
                id="due_days"
                v-model="liabilityForm.due_days"
                type="number"
                min="0"
                :class="{ 'border-danger': liabilityForm.invalid('due_days') }"
                :placeholder="t('views.liability.fields.due_days')"
                @change="liabilityForm.validate('due_days')"
              />
              <FormErrorMessages :messages="liabilityForm.errors.due_days" />
            </div>

            <div class="col-span-12 lg:col-span-2">
              <FormLabel>{{ t('views.liability.fields.amount_total') }}</FormLabel>
              <FormInputCurrency :model-value="amountTotalPreview" readonly :allow-negative="false" />
            </div>

            <div class="col-span-12 lg:col-span-2">
              <FormLabel>{{ t('views.liability.fields.amount_due') }}</FormLabel>
              <FormInputCurrency :model-value="amountDuePreview" readonly :allow-negative="false" />
            </div>

            <div class="col-span-12 lg:col-span-4">
              <FormLabel>{{ t('views.liability.fields.amount_paid_by_cash_account') }}</FormLabel>
              <FormInputCurrency :model-value="amountPaidByCashAccountPreview" readonly :allow-negative="false" />
            </div>

            <div class="col-span-12 lg:col-span-4">
              <FormLabel>{{ t('views.liability.fields.amount_paid_by_stock_adjustment') }}</FormLabel>
              <FormInputCurrency
                :model-value="amountPaidByStockAdjustmentPreview"
                readonly
                :allow-negative="false"
              />
            </div>

            <div class="col-span-12">
              <FormLabel :class="{ 'text-danger': liabilityForm.invalid('remarks') }">
                {{ t('views.liability.fields.remarks') }}
              </FormLabel>
              <FormTextarea
                id="remarks"
                v-model="liabilityForm.remarks"
                rows="3"
                :class="{ 'border-danger': liabilityForm.invalid('remarks') }"
                :placeholder="t('views.liability.fields.remarks')"
                @change="liabilityForm.validate('remarks')"
              />
              <FormErrorMessages :messages="liabilityForm.errors.remarks" />
            </div>
          </div>
        </div>
      </template>

      <template #card-items-2>
        <div class="p-5 space-y-4">
          <div class="grid grid-cols-12 gap-4 gap-y-3">
            <div class="col-span-12 md:col-span-4">
              <FormLabel>{{ t('views.liability.fields.amount_total') }}</FormLabel>
              <div class="rounded-md border border-slate-200 px-3 py-2 text-right text-sm font-medium dark:border-darkmode-400">
                {{ formatCurrency(amountTotalPreview) }}
              </div>
            </div>
            <div class="col-span-12 md:col-span-4">
              <FormLabel>{{ t('views.liability.fields.amount_paid_by_cash_account') }}</FormLabel>
              <div class="rounded-md border border-slate-200 px-3 py-2 text-right text-sm font-medium dark:border-darkmode-400">
                {{ formatCurrency(amountPaidByCashAccountPreview) }}
              </div>
            </div>
            <div class="col-span-12 md:col-span-4">
              <FormLabel>{{ t('views.liability.fields.amount_due') }}</FormLabel>
              <div class="rounded-md border border-slate-200 px-3 py-2 text-right text-sm font-medium dark:border-darkmode-400">
                {{ formatCurrency(amountDuePreview) }}
              </div>
            </div>
          </div>

          <FormErrorMessages :messages="(liabilityForm.errors as any).payments" />

          <div v-if="liabilityPaymentsForm.length === 0" class="text-right text-sm text-slate-500">
            {{ t('components.data-list.data_not_found') }}
          </div>

          <div v-else class="space-y-4">
            <div
              v-for="(payment, index) in liabilityPaymentsForm"
              :key="`liability-payment-${index}`"
              class="rounded-md border border-slate-200 p-4 dark:border-darkmode-400"
            >
              <div class="grid grid-cols-12 gap-4 gap-y-3">
                <div class="col-span-12 md:col-span-4 lg:col-span-2">
                  <FormLabel :class="{ 'text-danger': invalidLiabilityField(`payments.${index}.code`) }">
                    {{ t('views.liability.fields.payment_code') }}
                  </FormLabel>
                  <FormInputCode
                    :id="`payments.${index}.code`"
                    v-model="payment.code"
                    :class="{ 'border-danger': invalidLiabilityField(`payments.${index}.code`) }"
                    :placeholder="t('views.liability.fields.payment_code')"
                    @set-auto="setPaymentCode(index)"
                    @change="validateLiabilityField(`payments.${index}.code`)"
                  />
                  <FormErrorMessages :messages="getLiabilityFieldErrors(`payments.${index}.code`)" />
                </div>

                <div class="col-span-12 md:col-span-8 lg:col-span-3">
                  <FormLabel :class="{ 'text-danger': invalidLiabilityField(`payments.${index}.date`) }">
                    {{ t('views.liability.fields.payment_date') }}
                  </FormLabel>
                  <FormInputDateTimeAuto
                    :id="`payments.${index}.date`"
                    v-model="payment.date"
                    :class="{ 'border-danger': invalidLiabilityField(`payments.${index}.date`) }"
                    :placeholder="t('views.liability.fields.payment_date')"
                    @change="validateLiabilityField(`payments.${index}.date`)"
                  />
                  <FormErrorMessages :messages="getLiabilityFieldErrors(`payments.${index}.date`)" />
                </div>

                <div class="col-span-12 lg:col-span-4">
                  <FormLabel :class="{ 'text-danger': invalidLiabilityField(`payments.${index}.cash_account_id`) }">
                    {{ t('views.liability.fields.payment_cash_account') }}
                  </FormLabel>
                  <FormSelectSearch
                    :id="`payments.${index}.cash_account_id`"
                    v-model="payment.cash_account_id"
                    v-model:search="paymentCashAccountSearch"
                    :options="cashAccountOptions"
                    :placeholder="t('components.dropdown.placeholder')"
                    :class="{ 'border-danger': invalidLiabilityField(`payments.${index}.cash_account_id`) }"
                    @change="validateLiabilityField(`payments.${index}.cash_account_id`)"
                    @search="loadCashAccountDDL"
                    @clear="clearPaymentCashAccount(index)"
                  />
                  <FormErrorMessages :messages="getLiabilityFieldErrors(`payments.${index}.cash_account_id`)" />
                </div>

                <div class="col-span-12 md:col-span-8 lg:col-span-3">
                  <FormLabel :class="{ 'text-danger': invalidLiabilityField(`payments.${index}.amount`) }">
                    {{ t('views.liability.fields.payment_amount') }}
                  </FormLabel>
                  <div class="flex items-start gap-2">
                    <div class="min-w-0 flex-1">
                      <FormInputCurrency
                        :id="`payments.${index}.amount`"
                        v-model="payment.amount"
                        :allow-negative="false"
                        :class="{ 'border-danger': invalidLiabilityField(`payments.${index}.amount`) }"
                        @change="validateLiabilityField(`payments.${index}.amount`)"
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
                  <FormErrorMessages :messages="getLiabilityFieldErrors(`payments.${index}.amount`)" />
                </div>

                <div class="col-span-12">
                  <FormLabel :class="{ 'text-danger': invalidLiabilityField(`payments.${index}.remarks`) }">
                    {{ t('views.liability.fields.payment_remarks') }}
                  </FormLabel>
                  <FormTextarea
                    :id="`payments.${index}.remarks`"
                    v-model="payment.remarks"
                    rows="2"
                    :class="{ 'border-danger': invalidLiabilityField(`payments.${index}.remarks`) }"
                    :placeholder="t('views.liability.fields.payment_remarks')"
                    @change="validateLiabilityField(`payments.${index}.remarks`)"
                  />
                  <FormErrorMessages :messages="getLiabilityFieldErrors(`payments.${index}.remarks`)" />
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
            :disabled="liabilityForm.validating || liabilityForm.hasErrors"
          >
            <Lucide v-if="liabilityForm.validating" icon="Loader" class="animate-spin" />
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
