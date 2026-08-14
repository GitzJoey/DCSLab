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
  FormTextarea,
} from '@/components/Base/Form';
import ReceivableService from '@/services/ReceivableService';
import ReceivableCategoryService from '@/services/ReceivableCategoryService';
import CashAccountService from '@/services/CashAccountService';
import CacheService from '@/services/CacheService';
import CustomerService from '@/services/CustomerService';
import Button from '@/components/Base/Button';
import Lucide from '@/components/Base/Lucide';
import { ErrorCode } from '@/types/enums/ErrorCode';
import type { DropDownOption } from '@/types/models/DropDownOption';
import type { AlertPlaceholderProps } from '@/components/AlertPlaceholder/AlertPlaceholder.vue';
import type { ReceivableCategory } from '@/types/models/ReceivableCategory';
import type { CashAccount } from '@/types/models/CashAccount';
import type { Customer } from '@/types/models/Customer';

type ReceivablePaymentFormItem = {
  id?: string | null;
  code: string;
  date: string;
  cash_account_id: string;
  amount: number;
  remarks: string;
};

const { t } = useI18n();
const router = useRouter();
const selectedUserLocationStore = useSelectedUserLocationStore();

const emits = defineEmits(['mode-state', 'loading-state', 'update-profile', 'show-alertplaceholder']);

const isUserLocationSelected = computed(() => selectedUserLocationStore.isUserLocationSelected);
const selectedUserLocation = computed(() => selectedUserLocationStore.selectedUserLocation);

const receivableService = new ReceivableService();
const receivableCategoryService = new ReceivableCategoryService();
const cashAccountService = new CashAccountService();
const cacheService = new CacheService();
const customerService = new CustomerService();

const cards = ref<Array<TwoColumnsLayoutCards>>([
  { title: 'views.receivable.field_groups.company_info', state: CardState.Expanded },
  { title: 'views.receivable.field_groups.receivable_data', state: CardState.Expanded },
  { title: 'views.receivable.field_groups.payments', state: CardState.Expanded },
  { title: '', state: CardState.Hidden, id: 'button' },
]);

const receivableForm = receivableService.useReceivableCreateForm();

const categoryDDL = ref<Array<DropDownOption> | null>(null);
const categorySearch = ref<string>('');
const categoryOptions = computed(() =>
  (categoryDDL.value ?? []).map((item) => ({
    value: item.code,
    label: item.name,
  })),
);

const customerDDL = ref<Array<DropDownOption> | null>(null);
const customerSearch = ref<string>('');
const customerOptions = computed(() =>
  (customerDDL.value ?? []).map((item) => ({
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

const receivablePaymentsForm = computed<ReceivablePaymentFormItem[]>(
  () => receivableForm.payments as ReceivablePaymentFormItem[],
);
const paymentCashAccountSearch = ref<string>('');

const amountTotalPreview = computed(
  () => Number(receivableForm.direct_amount_received ?? 0) + Number(receivableForm.opening_amount_due ?? 0),
);
const amountPaidByCashAccountPreview = computed(() =>
  receivablePaymentsForm.value.reduce((total, payment) => total + Number(payment.amount ?? 0), 0),
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
const invalidReceivableField = (field: string) => receivableForm.invalid(field as any);
const validateReceivableField = (field: string) => receivableForm.validate(field as any);
const normalizeErrorMessages = (messages: string | string[] | undefined): string | undefined =>
  Array.isArray(messages) ? messages.join(' ') : messages;
const getReceivableFieldErrors = (field: string) =>
  normalizeErrorMessages(
    (receivableForm.errors as Record<string, string | string[] | undefined>)[field],
  );
const clearTopAlertPlaceholder = () => {
  showAlertPlaceholder('hidden', '', null);
};
const clearStaleValidationErrors = (fields: string[]) => {
  fields.forEach((field) => receivableForm.forgetError(field as any));
  clearTopAlertPlaceholder();
};

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
  setLocationData();

  await Promise.all([
    loadCategoryDDL(),
    loadCustomerDDL(),
    loadCashAccountDDL(),
  ]);
});

const setLocationData = () => {
  receivableForm.setData({
    company_id: selectedUserLocation.value.company.id,
    branch_id: selectedUserLocation.value.branch.id,
  });
};

const loadFromCache = () => {
  const data = cacheService.getLastEntity('RECEIVABLE_CREATE') as Record<string, unknown> | null;
  if (!data) return;

  receivableForm.setData(data);
};

const loadCategoryDDL = async (search = '') => {
  if (!selectedUserLocation.value) return;

  const result = await receivableCategoryService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    search,
    include_id: receivableForm.category_id || undefined,
    refresh: false,
    limit: 20,
  });

  if (result.success && result.data) {
    categoryDDL.value = result.data.data.map((item: ReceivableCategory) => ({
      code: item.id,
      name: item.name,
    }));
  }
};

const loadCustomerDDL = async (search = '') => {
  if (!selectedUserLocation.value) return;

  const result = await customerService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    search,
    include_id: receivableForm.customer_id || undefined,
    refresh: false,
    limit: 20,
  });

  if (result.success && result.data) {
    customerDDL.value = result.data.data.map((item: Customer) => ({
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
    include_id: receivableForm.cash_account_id || undefined,
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
  receivableForm.forgetError('code');
  receivableForm.setData({
    code: receivableForm.code === '_AUTO_' ? '' : '_AUTO_',
  });
};

const clearCategory = () => {
  receivableForm.setData({ category_id: '' });
  clearStaleValidationErrors(['category_id', 'form']);
};

const clearCashAccount = () => {
  receivableForm.setData({ cash_account_id: '' });
  receivableForm.forgetError('cash_account_id');
};

const clearCustomer = () => {
  receivableForm.setData({ customer_id: '' });
  clearStaleValidationErrors(['customer_id', 'form']);
};

const setPaymentCode = (index: number) => {
  receivableForm.forgetError(`payments.${index}.code` as any);
  receivablePaymentsForm.value[index].code =
    receivablePaymentsForm.value[index].code === '_AUTO_' ? '' : '_AUTO_';
};

const clearPaymentCashAccount = (index: number) => {
  const payment = receivablePaymentsForm.value[index];
  if (!payment) return;

  payment.cash_account_id = '';
  validateReceivableField(`payments.${index}.cash_account_id`);
};

const addPayment = () => {
  receivablePaymentsForm.value.push({
    code: '_AUTO_',
    date: '_AUTO_',
    cash_account_id: '',
    amount: 0,
    remarks: '',
  });

  Object.keys(receivableForm.errors).forEach((key) => {
    if (key === 'payments' || key.startsWith('payments.')) {
      receivableForm.forgetError(key as any);
    }
  });
};

const removePayment = (index: number) => {
  receivablePaymentsForm.value.splice(index, 1);

  Object.keys(receivableForm.errors).forEach((key) => {
    if (key === 'payments' || key.startsWith('payments.')) {
      receivableForm.forgetError(key as any);
    }
  });
};

const onSubmit = async () => {
  emits('loading-state', true);

  await receivableForm
    .submit()
    .then(() => {
      cacheService.removeLastEntity('RECEIVABLE_CREATE');
      resetForm();
      showAlertPlaceholder('hidden', '', null);
      emits('update-profile');
      router.push({ name: 'side-menu-receivable-list' });
    })
    .catch((error) => {
      showAlertPlaceholder('danger', '', convertErrorTypeToAlertListType(error));
    })
    .finally(() => {
      emits('loading-state', false);
    });
};

const resetForm = () => {
  receivableForm.reset();
  receivableForm.setErrors({});
  receivableForm.setData({
    code: '_AUTO_',
    date: '_AUTO_',
    customer_id: '',
    cash_account_id: '',
    direct_amount_received: 0,
    opening_amount_due: 0,
    due_days: 0,
    remarks: '',
    payments: [],
  });
  categorySearch.value = '';
  customerSearch.value = '';
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
  receivableForm,
  debounce((newValue): void => {
    cacheService.setLastEntity('RECEIVABLE_CREATE', newValue.data());
  }, 500),
  { deep: true },
);

watch(
  () => receivableForm.category_id,
  () => {
    clearStaleValidationErrors(['category_id']);
  },
);

watch(
  () => [receivableForm.direct_amount_received, receivableForm.opening_amount_due],
  () => {
    clearStaleValidationErrors(['form']);
  },
);

watch(
  () => receivableForm.customer_id,
  () => {
    clearStaleValidationErrors(['customer_id', 'form']);
  },
);
</script>

<template>
  <form id="receivableForm" @submit.prevent="onSubmit">
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
              <FormInputCurrency type="hidden" v-model="receivableForm.company_id" />
            </div>
            <div class="col-span-12 md:col-span-6">
              <FormLabel>
                {{ selectedUserLocation.branch.code }}
                <br />
                {{ selectedUserLocation.branch.name }}
              </FormLabel>
              <FormInput type="hidden" v-model="receivableForm.branch_id" />
            </div>
          </div>
        </div>
      </template>

      <template #card-items-1>
        <div class="p-5">
          <div class="grid grid-cols-12 gap-4 gap-y-3">
            <div class="col-span-12 lg:col-span-3">
              <FormLabel :class="{ 'text-danger': receivableForm.invalid('code') }">
                {{ t('views.receivable.fields.code') }}
              </FormLabel>
              <FormInputCode
                id="code"
                v-model="receivableForm.code"
                :class="{ 'border-danger': receivableForm.invalid('code') }"
                :placeholder="t('views.receivable.fields.code')"
                @set-auto="setCode"
                @change="receivableForm.validate('code')"
              />
              <FormErrorMessages :messages="receivableForm.errors.code" />
            </div>

            <div class="col-span-12 lg:col-span-3">
              <FormLabel :class="{ 'text-danger': receivableForm.invalid('date') }">
                {{ t('views.receivable.fields.date') }}
              </FormLabel>
              <FormInputDateTimeAuto
                id="date"
                v-model="receivableForm.date"
                :class="{ 'border-danger': receivableForm.invalid('date') }"
                :placeholder="t('views.receivable.fields.date')"
                @change="receivableForm.validate('date')"
              />
              <FormErrorMessages :messages="receivableForm.errors.date" />
            </div>

            <div class="col-span-12 lg:col-span-3">
              <FormLabel :class="{ 'text-danger': receivableForm.invalid('category_id') }">
                {{ t('views.receivable.fields.category') }}
              </FormLabel>
              <FormSelectSearch
                v-model="receivableForm.category_id"
                v-model:search="categorySearch"
                :options="categoryOptions"
                :placeholder="t('components.dropdown.placeholder')"
                :class="{ 'border-danger': receivableForm.invalid('category_id') }"
                @change="receivableForm.validate('category_id')"
                @search="loadCategoryDDL"
                @clear="clearCategory"
              />
              <FormErrorMessages :messages="receivableForm.errors.category_id" />
            </div>

            <div class="col-span-12 lg:col-span-3">
              <FormLabel :class="{ 'text-danger': receivableForm.invalid('customer_id') }">
                {{ t('views.receivable.fields.customer') }}
              </FormLabel>
              <FormSelectSearch
                v-model="receivableForm.customer_id"
                v-model:search="customerSearch"
                :options="customerOptions"
                :placeholder="t('components.dropdown.placeholder')"
                :class="{ 'border-danger': receivableForm.invalid('customer_id') }"
                @change="receivableForm.validate('customer_id')"
                @search="loadCustomerDDL"
                @clear="clearCustomer"
              />
              <FormErrorMessages :messages="receivableForm.errors.customer_id" />
            </div>

            <div class="col-span-12 lg:col-span-3">
              <FormLabel :class="{ 'text-danger': receivableForm.invalid('cash_account_id') }">
                {{ t('views.receivable.fields.cash_account') }}
              </FormLabel>
              <FormSelectSearch
                v-model="receivableForm.cash_account_id"
                v-model:search="cashAccountSearch"
                :options="cashAccountOptions"
                :placeholder="t('components.dropdown.placeholder')"
                :class="{ 'border-danger': receivableForm.invalid('cash_account_id') }"
                @change="receivableForm.validate('cash_account_id')"
                @search="loadCashAccountDDL"
                @clear="clearCashAccount"
              />
              <FormErrorMessages :messages="receivableForm.errors.cash_account_id" />
            </div>

            <div class="col-span-12 lg:col-span-2">
              <FormLabel :class="{ 'text-danger': receivableForm.invalid('direct_amount_received') }">
                {{ t('views.receivable.fields.direct_amount_received') }}
              </FormLabel>
              <FormInputCurrency
                id="direct_amount_received"
                v-model="receivableForm.direct_amount_received"
                :allow-negative="false"
                :class="{ 'border-danger': receivableForm.invalid('direct_amount_received') }"
                :placeholder="t('views.receivable.fields.direct_amount_received')"
                @change="receivableForm.validate('direct_amount_received')"
              />
              <FormErrorMessages :messages="receivableForm.errors.direct_amount_received" />
            </div>

            <div class="col-span-12 lg:col-span-2">
              <FormLabel :class="{ 'text-danger': receivableForm.invalid('opening_amount_due') }">
                {{ t('views.receivable.fields.opening_amount_due') }}
              </FormLabel>
              <FormInputCurrency
                id="opening_amount_due"
                v-model="receivableForm.opening_amount_due"
                :allow-negative="false"
                :class="{ 'border-danger': receivableForm.invalid('opening_amount_due') }"
                :placeholder="t('views.receivable.fields.opening_amount_due')"
                @change="receivableForm.validate('opening_amount_due')"
              />
              <FormErrorMessages :messages="receivableForm.errors.opening_amount_due" />
            </div>

            <div class="col-span-12 lg:col-span-1">
              <FormLabel :class="{ 'text-danger': receivableForm.invalid('due_days') }">
                {{ t('views.receivable.fields.due_days') }}
              </FormLabel>
              <FormInputCurrency
                id="due_days"
                v-model="receivableForm.due_days"
                :allow-negative="false"
                :class="{ 'border-danger': receivableForm.invalid('due_days') }"
                :placeholder="t('views.receivable.fields.due_days')"
                @change="receivableForm.validate('due_days')"
              />
              <FormErrorMessages :messages="receivableForm.errors.due_days" />
            </div>

            <div class="col-span-12 lg:col-span-2">
              <FormLabel>{{ t('views.receivable.fields.amount_total') }}</FormLabel>
              <FormInputCurrency :model-value="amountTotalPreview" readonly :allow-negative="false" />
            </div>

            <div class="col-span-12 lg:col-span-2">
              <FormLabel>{{ t('views.receivable.fields.amount_due') }}</FormLabel>
              <FormInputCurrency :model-value="amountDuePreview" readonly :allow-negative="false" />
            </div>

            <div class="col-span-12 lg:col-span-2">
              <FormLabel>{{ t('views.receivable.fields.amount_paid_by_cash_account') }}</FormLabel>
              <FormInputCurrency :model-value="amountPaidByCashAccountPreview" readonly :allow-negative="false" />
            </div>

            <div class="col-span-12 lg:col-span-2">
              <FormLabel>{{ t('views.receivable.fields.amount_paid_by_stock_adjustment') }}</FormLabel>
              <FormInputCurrency
                :model-value="amountPaidByStockAdjustmentPreview"
                readonly
                :allow-negative="false"
              />
            </div>

            <div class="col-span-12">
              <FormLabel :class="{ 'text-danger': receivableForm.invalid('remarks') }">
                {{ t('views.receivable.fields.remarks') }}
              </FormLabel>
              <FormTextarea
                id="remarks"
                v-model="receivableForm.remarks"
                rows="3"
                :class="{ 'border-danger': receivableForm.invalid('remarks') }"
                :placeholder="t('views.receivable.fields.remarks')"
                @change="receivableForm.validate('remarks')"
              />
              <FormErrorMessages :messages="receivableForm.errors.remarks" />
            </div>
          </div>
        </div>
      </template>

      <template #card-items-2>
        <div class="p-5 space-y-4">
          <div class="grid grid-cols-12 gap-4 gap-y-3">
            <div class="col-span-12 md:col-span-4">
              <FormLabel>{{ t('views.receivable.fields.amount_total') }}</FormLabel>
              <div class="rounded-md border border-slate-200 px-3 py-2 text-right text-sm font-medium dark:border-darkmode-400">
                {{ formatCurrency(amountTotalPreview) }}
              </div>
            </div>
            <div class="col-span-12 md:col-span-4">
              <FormLabel>{{ t('views.receivable.fields.amount_paid_by_cash_account') }}</FormLabel>
              <div class="rounded-md border border-slate-200 px-3 py-2 text-right text-sm font-medium dark:border-darkmode-400">
                {{ formatCurrency(amountPaidByCashAccountPreview) }}
              </div>
            </div>
            <div class="col-span-12 md:col-span-4">
              <FormLabel>{{ t('views.receivable.fields.amount_due') }}</FormLabel>
              <div class="rounded-md border border-slate-200 px-3 py-2 text-right text-sm font-medium dark:border-darkmode-400">
                {{ formatCurrency(amountDuePreview) }}
              </div>
            </div>
          </div>

          <FormErrorMessages :messages="(receivableForm.errors as any).payments" />

          <div v-if="receivablePaymentsForm.length === 0" class="text-right text-sm text-slate-500">
            {{ t('components.data-list.data_not_found') }}
          </div>

          <div v-else class="space-y-4">
            <div
              v-for="(payment, index) in receivablePaymentsForm"
              :key="`receivable-payment-${index}`"
              class="rounded-md border border-slate-200 p-4 dark:border-darkmode-400"
            >
              <div class="grid grid-cols-12 gap-4 gap-y-3">
                <div class="col-span-12 md:col-span-4 lg:col-span-2">
                  <FormLabel :class="{ 'text-danger': invalidReceivableField(`payments.${index}.code`) }">
                    {{ t('views.receivable.fields.payment_code') }}
                  </FormLabel>
                  <FormInputCode
                    :id="`payments.${index}.code`"
                    v-model="payment.code"
                    :class="{ 'border-danger': invalidReceivableField(`payments.${index}.code`) }"
                    :placeholder="t('views.receivable.fields.payment_code')"
                    @set-auto="setPaymentCode(index)"
                    @change="validateReceivableField(`payments.${index}.code`)"
                  />
                  <FormErrorMessages :messages="getReceivableFieldErrors(`payments.${index}.code`)" />
                </div>

                <div class="col-span-12 md:col-span-8 lg:col-span-3">
                  <FormLabel :class="{ 'text-danger': invalidReceivableField(`payments.${index}.date`) }">
                    {{ t('views.receivable.fields.payment_date') }}
                  </FormLabel>
                  <FormInputDateTimeAuto
                    :id="`payments.${index}.date`"
                    v-model="payment.date"
                    :class="{ 'border-danger': invalidReceivableField(`payments.${index}.date`) }"
                    :placeholder="t('views.receivable.fields.payment_date')"
                    @change="validateReceivableField(`payments.${index}.date`)"
                  />
                  <FormErrorMessages :messages="getReceivableFieldErrors(`payments.${index}.date`)" />
                </div>

                <div class="col-span-12 lg:col-span-4">
                  <FormLabel :class="{ 'text-danger': invalidReceivableField(`payments.${index}.cash_account_id`) }">
                    {{ t('views.receivable.fields.payment_cash_account') }}
                  </FormLabel>
                  <FormSelectSearch
                    :id="`payments.${index}.cash_account_id`"
                    v-model="payment.cash_account_id"
                    v-model:search="paymentCashAccountSearch"
                    :options="cashAccountOptions"
                    :placeholder="t('components.dropdown.placeholder')"
                    :class="{ 'border-danger': invalidReceivableField(`payments.${index}.cash_account_id`) }"
                    @change="validateReceivableField(`payments.${index}.cash_account_id`)"
                    @search="loadCashAccountDDL"
                    @clear="clearPaymentCashAccount(index)"
                  />
                  <FormErrorMessages :messages="getReceivableFieldErrors(`payments.${index}.cash_account_id`)" />
                </div>

                <div class="col-span-12 md:col-span-8 lg:col-span-3">
                  <FormLabel :class="{ 'text-danger': invalidReceivableField(`payments.${index}.amount`) }">
                    {{ t('views.receivable.fields.payment_amount') }}
                  </FormLabel>
                  <div class="flex items-start gap-2">
                    <div class="min-w-0 flex-1">
                      <FormInputCurrency
                        :id="`payments.${index}.amount`"
                        v-model="payment.amount"
                        :allow-negative="false"
                        :class="{ 'border-danger': invalidReceivableField(`payments.${index}.amount`) }"
                        @change="validateReceivableField(`payments.${index}.amount`)"
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
                  <FormErrorMessages :messages="getReceivableFieldErrors(`payments.${index}.amount`)" />
                </div>

                <div class="col-span-12">
                  <FormLabel :class="{ 'text-danger': invalidReceivableField(`payments.${index}.remarks`) }">
                    {{ t('views.receivable.fields.payment_remarks') }}
                  </FormLabel>
                  <FormTextarea
                    :id="`payments.${index}.remarks`"
                    v-model="payment.remarks"
                    rows="2"
                    :class="{ 'border-danger': invalidReceivableField(`payments.${index}.remarks`) }"
                    :placeholder="t('views.receivable.fields.payment_remarks')"
                    @change="validateReceivableField(`payments.${index}.remarks`)"
                  />
                  <FormErrorMessages :messages="getReceivableFieldErrors(`payments.${index}.remarks`)" />
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
            :disabled="receivableForm.validating"
          >
            <Lucide v-if="receivableForm.validating" icon="Loader" class="animate-spin" />
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

