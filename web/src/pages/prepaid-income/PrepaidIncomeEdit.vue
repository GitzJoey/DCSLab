<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRoute, useRouter } from 'vue-router';
import { convertErrorTypeToAlertListType, formatDate } from '@/utils/helper';
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
import PrepaidIncomeService from '@/services/PrepaidIncomeService';
import IncomeCategoryService from '@/services/IncomeCategoryService';
import CashAccountService from '@/services/CashAccountService';
import PrepaidIncomeImagesField from '@/components/PrepaidIncome/PrepaidIncomeImagesField.vue';
import Button from '@/components/Base/Button';
import Lucide from '@/components/Base/Lucide';
import { ErrorCode } from '@/types/enums/ErrorCode';
import type { DropDownOption } from '@/types/models/DropDownOption';
import type { AlertPlaceholderProps } from '@/components/AlertPlaceholder/AlertPlaceholder.vue';
import type { PrepaidIncome } from '@/types/models/PrepaidIncome';
import type { IncomeCategory } from '@/types/models/IncomeCategory';
import type { CashAccount } from '@/types/models/CashAccount';
import type { PrepaidIncomeImage } from '@/types/models/PrepaidIncomeImage';
import type { PrepaidIncomePayment } from '@/types/models/PrepaidIncomePayment';

type PrepaidIncomePaymentFormItem = {
  id?: string | null;
  code: string;
  date: string;
  cash_account_id: string;
  amount: number;
  remarks: string;
};

const { t } = useI18n();
const route = useRoute();
const router = useRouter();
const selectedUserLocationStore = useSelectedUserLocationStore();

const emits = defineEmits(['mode-state', 'loading-state', 'update-profile', 'show-alertplaceholder']);

const isUserLocationSelected = computed(() => selectedUserLocationStore.isUserLocationSelected);
const selectedUserLocation = computed(() => selectedUserLocationStore.selectedUserLocation);

const prepaidIncomeService = new PrepaidIncomeService();
const incomeCategoryService = new IncomeCategoryService();
const cashAccountService = new CashAccountService();

const cards = ref<Array<TwoColumnsLayoutCards>>([
  {
    title: 'views.prepaid_income.field_groups.company_info',
    state: CardState.Expanded,
  },
  {
    title: 'views.prepaid_income.field_groups.prepaid_income_data',
    state: CardState.Expanded,
  },
  {
    title: 'views.prepaid_income.field_groups.payments',
    state: CardState.Expanded,
  },
  {
    title: 'views.prepaid_income.field_groups.images',
    state: CardState.Expanded,
  },
  { title: '', state: CardState.Hidden, id: 'button' },
]);

const prepaidIncomeForm = prepaidIncomeService.usePrepaidIncomeEditForm(route.params.ulid.toString());
const prepaidIncomeData = ref<PrepaidIncome | null>(null);
const uploadedImages = ref<PrepaidIncomeImage[]>([]);

const categoryDDL = ref<Array<DropDownOption> | null>(null);
const categorySearch = ref<string>('');
const categoryOptions = computed(() =>
  (categoryDDL.value ?? []).map((item) => ({
    value: item.code,
    label: item.name,
  })),
);

const paidImmediatelyCashAccountDDL = ref<Array<DropDownOption> | null>(null);
const paidImmediatelyCashAccountSearch = ref<string>('');
const paidImmediatelyCashAccountOptions = computed(() =>
  (paidImmediatelyCashAccountDDL.value ?? []).map((item) => ({
    value: item.code,
    label: item.name,
  })),
);

const amountTotalPreview = computed(() =>
  Number(prepaidIncomeForm.amount_paid_immediately ?? 0) + Number(prepaidIncomeForm.amount_receivable ?? 0),
);
const prepaidIncomePaymentsForm = computed<PrepaidIncomePaymentFormItem[]>(
  () => prepaidIncomeForm.payments as PrepaidIncomePaymentFormItem[],
);
const paymentCashAccountSearch = ref<string>('');
const invalidPrepaidIncomeField = (field: string) => prepaidIncomeForm.invalid(field as any);
const validatePrepaidIncomeField = (field: string) => prepaidIncomeForm.validate(field as any);
const getPrepaidIncomeFieldErrors = (field: string): string | undefined => {
  const errors = (prepaidIncomeForm.errors as Record<string, string | string[] | undefined>)[field];

  if (Array.isArray(errors)) {
    return errors.join(' ');
  }

  return errors;
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
  await Promise.all([loadCategoryDDL(), loadPaidImmediatelyCashAccountDDL()]);
});

const loadData = async () => {
  emits('loading-state', true);
  const result = await prepaidIncomeService.read(route.params.ulid.toString());
  emits('loading-state', false);

  if (result.success && result.data) {
    prepaidIncomeData.value = result.data;

    prepaidIncomeForm.setData({
      company_id: result.data.company?.id ?? '',
      branch_id: result.data.branch?.id ?? '',
      code: result.data.code,
      date: formatDate(result.data.date, 'YYYY-MM-DD HH:mm:ss'),
      income_category_id: result.data.category?.id ?? '',
      estimated_useful_life: result.data.estimated_useful_life,
      paid_immediately_cash_account_id: result.data.paid_immediately_cash_account?.id ?? '',
      amount_paid_immediately: result.data.amount_paid_immediately,
      amount_receivable: result.data.amount_receivable,
      due_days: result.data.due_days,
      remarks: result.data.remarks ?? '',
      delete_image_ids: [],
      image_hashes: [],
      delete_payment_ids: [],
      payments: (result.data.payments ?? []).map((payment: PrepaidIncomePayment) => ({
        id: payment.id,
        code: payment.code,
        date: formatDate(payment.date, 'YYYY-MM-DD HH:mm:ss'),
        cash_account_id: payment.cash_account?.id ?? '',
        amount: payment.amount,
        remarks: payment.remarks ?? '',
      })),
    } as any);

    if (result.data.prepaid_income_images) {
      const images = result.data.prepaid_income_images.map((img: PrepaidIncomeImage) => ({
        id: img.id,
        prepaid_income_id: img.prepaid_income_id,
        path: img.path,
        url: img.url,
        hash: img.hash,
        is_main: img.is_main,
      }));

      uploadedImages.value = images;
      prepaidIncomeForm.image_hashes = images.map((img: PrepaidIncomeImage) => ({
        hash: img.hash,
        is_main: img.is_main,
      }));
    } else {
      uploadedImages.value = [];
    }
  } else {
    router.push({ name: 'side-menu-prepaid-income-list' });
  }
};

const loadCategoryDDL = async (search = '') => {
  if (!selectedUserLocation.value) return;

  const result = await incomeCategoryService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    search,
    parent_id: undefined,
    has_parent: undefined,
    has_children: false,
    include_id: prepaidIncomeData.value?.category?.id,
    refresh: false,
    limit: 20,
  });

  if (result.success && result.data) {
    categoryDDL.value = result.data.data.map((item: IncomeCategory) => ({
      code: item.id,
      name: item.display_code ? `${item.display_code} - ${item.name}` : item.name,
    }));
  }
};

const loadPaidImmediatelyCashAccountDDL = async (search = '') => {
  if (!selectedUserLocation.value) return;

  const result = await cashAccountService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    branch_id: selectedUserLocation.value.branch.id,
    is_bank: null,
    search,
    include_id: prepaidIncomeData.value?.paid_immediately_cash_account?.id,
    with_remaining_balance: null,
    refresh: false,
    limit: 20,
  });

  if (result.success && result.data) {
    paidImmediatelyCashAccountDDL.value = result.data.data.map((item: CashAccount) => ({
      code: item.id,
      name: `${item.code} - ${item.name}`,
    }));
  }
};

const handleExpandCard = (index: number) => {
  if (cards.value[index].state === CardState.Collapsed) {
    cards.value[index].state = CardState.Expanded;
  } else if (cards.value[index].state === CardState.Expanded) {
    cards.value[index].state = CardState.Collapsed;
  }
};

const scrollToError = (id: string): void => {
  const el = document.getElementById(id);
  if (!el) return;
  el.scrollIntoView({ behavior: 'smooth', block: 'center' });
};

const setCode = () => {
  prepaidIncomeForm.forgetError('code');

  if (prepaidIncomeForm.code === '_AUTO_') {
    prepaidIncomeForm.setData({ code: '' });
    return;
  }

  prepaidIncomeForm.setData({ code: '_AUTO_' });
};

const clearCategory = () => {
  prepaidIncomeForm.setData({ income_category_id: '' });
  prepaidIncomeForm.forgetError('income_category_id');
};

const clearPaidImmediatelyCashAccount = () => {
  prepaidIncomeForm.setData({ paid_immediately_cash_account_id: '' });
  prepaidIncomeForm.forgetError('paid_immediately_cash_account_id');
};

const clearAmountTotalError = () => {
  const { amount_total, ...restErrors } = prepaidIncomeForm.errors as Record<string, string | string[]>;

  if (amount_total) {
    prepaidIncomeForm.setErrors(restErrors);
  }
};

const handleAmountChange = (field: 'amount_paid_immediately' | 'amount_receivable') => {
  clearAmountTotalError();
  prepaidIncomeForm.validate(field);
};

const setPaymentCode = (index: number) => {
  prepaidIncomeForm.forgetError(`payments.${index}.code` as any);
  prepaidIncomePaymentsForm.value[index].code =
    prepaidIncomePaymentsForm.value[index].code === '_AUTO_' ? '' : '_AUTO_';
};

const clearPaymentCashAccount = (index: number) => {
  const payment = prepaidIncomePaymentsForm.value[index];
  if (!payment) return;

  payment.cash_account_id = '';
  validatePrepaidIncomeField(`payments.${index}.cash_account_id`);
};

const addPayment = () => {
  prepaidIncomePaymentsForm.value.push({
    id: null,
    code: '_AUTO_',
    date: '_AUTO_',
    cash_account_id: '',
    amount: 0,
    remarks: '',
  });
};

const removePayment = (index: number) => {
  const payment = prepaidIncomePaymentsForm.value[index];

  if (payment?.id && !prepaidIncomeForm.delete_payment_ids.includes(payment.id)) {
    prepaidIncomeForm.delete_payment_ids.push(payment.id);
  }

  prepaidIncomePaymentsForm.value.splice(index, 1);

  Object.keys(prepaidIncomeForm.errors).forEach((key) => {
    if (key === 'payments' || key.startsWith('payments.')) {
      prepaidIncomeForm.forgetError(key as any);
    }
  });
};

const onSubmit = async () => {
  if (prepaidIncomeForm.hasErrors) {
    const firstErrorKey = Object.keys(prepaidIncomeForm.errors)[0];

    if (firstErrorKey) {
      scrollToError(firstErrorKey);
    }

    return;
  }

  emits('loading-state', true);

  await prepaidIncomeForm
    .submit()
    .then(() => {
      showAlertPlaceholder('hidden', '', null);
      emits('update-profile');
      router.push({ name: 'side-menu-prepaid-income-list' });
    })
    .catch((error) => {
      showAlertPlaceholder('danger', '', convertErrorTypeToAlertListType(error));
    })
    .finally(() => {
      emits('loading-state', false);
    });
};

const resetForm = async () => {
  prepaidIncomeForm.reset();
  prepaidIncomeForm.setErrors({});
  categorySearch.value = '';
  paidImmediatelyCashAccountSearch.value = '';
  paymentCashAccountSearch.value = '';
  await loadData();
  await Promise.all([loadCategoryDDL(), loadPaidImmediatelyCashAccountDDL()]);
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
  <form id="prepaidIncomeForm" @submit.prevent="onSubmit">
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
              <FormInput type="hidden" v-model="prepaidIncomeForm.company_id" />
            </div>
            <div class="col-span-12 md:col-span-6">
              <FormLabel>
                {{ selectedUserLocation.branch.code }}
                <br />
                {{ selectedUserLocation.branch.name }}
              </FormLabel>
              <FormInput type="hidden" v-model="prepaidIncomeForm.branch_id" />
            </div>
          </div>
        </div>
      </template>

      <template #card-items-1>
        <div class="p-5">
          <div class="grid grid-cols-12 gap-4 gap-y-3">
            <div class="col-span-12 lg:col-span-3">
              <FormLabel :class="{ 'text-danger': prepaidIncomeForm.invalid('code') }">
                {{ t('views.prepaid_income.fields.code') }}
              </FormLabel>
              <FormInputCode
                v-model="prepaidIncomeForm.code"
                :class="{ 'border-danger': prepaidIncomeForm.invalid('code') }"
                :placeholder="t('views.prepaid_income.fields.code')"
                @set-auto="setCode"
                @change="prepaidIncomeForm.validate('code')"
              />
              <FormErrorMessages :messages="prepaidIncomeForm.errors.code" />
            </div>

            <div class="col-span-12 lg:col-span-3">
              <FormLabel :class="{ 'text-danger': prepaidIncomeForm.invalid('date') }">
                {{ t('views.prepaid_income.fields.date') }}
              </FormLabel>
              <FormInputDateTimeAuto
                v-model="prepaidIncomeForm.date"
                :class="{ 'border-danger': prepaidIncomeForm.invalid('date') }"
                :placeholder="t('views.prepaid_income.fields.date')"
                @change="prepaidIncomeForm.validate('date')"
              />
              <FormErrorMessages :messages="prepaidIncomeForm.errors.date" />
            </div>

            <div class="col-span-12 lg:col-span-6">
              <FormLabel :class="{ 'text-danger': prepaidIncomeForm.invalid('income_category_id') }">
                {{ t('views.prepaid_income.fields.category') }}
              </FormLabel>
              <FormSelectSearch
                v-model="prepaidIncomeForm.income_category_id"
                v-model:search="categorySearch"
                :options="categoryOptions"
                :placeholder="t('components.dropdown.placeholder')"
                :class="{ 'border-danger': prepaidIncomeForm.invalid('income_category_id') }"
                @change="prepaidIncomeForm.validate('income_category_id')"
                @search="loadCategoryDDL"
                @clear="clearCategory"
              />
              <FormErrorMessages :messages="prepaidIncomeForm.errors.income_category_id" />
            </div>

            <div class="col-span-12 lg:col-span-4">
              <FormLabel :class="{ 'text-danger': prepaidIncomeForm.invalid('estimated_useful_life') }">
                {{ t('views.prepaid_income.fields.estimated_useful_life') }}
              </FormLabel>
              <FormInputCurrency
                id="estimated_useful_life"
                v-model="prepaidIncomeForm.estimated_useful_life"
                :class="{ 'border-danger': prepaidIncomeForm.invalid('estimated_useful_life') }"
                :placeholder="t('views.prepaid_income.fields.estimated_useful_life')"
                @change="prepaidIncomeForm.validate('estimated_useful_life')"
              />
              <div class="mt-1 text-xs text-slate-500">
                {{ t('views.prepaid_income.hints.estimated_useful_life') }}
              </div>
              <FormErrorMessages :messages="prepaidIncomeForm.errors.estimated_useful_life" />
            </div>

            <div class="col-span-12 lg:col-span-4">
              <FormLabel :class="{ 'text-danger': prepaidIncomeForm.invalid('paid_immediately_cash_account_id') }">
                {{ t('views.prepaid_income.fields.paid_immediately_cash_account') }}
              </FormLabel>
              <FormSelectSearch
                v-model="prepaidIncomeForm.paid_immediately_cash_account_id"
                v-model:search="paidImmediatelyCashAccountSearch"
                :options="paidImmediatelyCashAccountOptions"
                :placeholder="t('components.dropdown.placeholder')"
                :class="{ 'border-danger': prepaidIncomeForm.invalid('paid_immediately_cash_account_id') }"
                @change="prepaidIncomeForm.validate('paid_immediately_cash_account_id')"
                @search="loadPaidImmediatelyCashAccountDDL"
                @clear="clearPaidImmediatelyCashAccount"
              />
              <FormErrorMessages :messages="prepaidIncomeForm.errors.paid_immediately_cash_account_id" />
            </div>

            <div class="col-span-12 lg:col-span-2">
              <FormLabel :class="{ 'text-danger': prepaidIncomeForm.invalid('amount_paid_immediately') }">
                {{ t('views.prepaid_income.fields.amount_paid_immediately') }}
              </FormLabel>
              <FormInputCurrency
                v-model="prepaidIncomeForm.amount_paid_immediately"
                :allow-negative="false"
                :class="{ 'border-danger': prepaidIncomeForm.invalid('amount_paid_immediately') }"
                :placeholder="t('views.prepaid_income.fields.amount_paid_immediately')"
                @change="handleAmountChange('amount_paid_immediately')"
              />
              <FormErrorMessages :messages="prepaidIncomeForm.errors.amount_paid_immediately" />
            </div>

            <div class="col-span-12 lg:col-span-2">
              <FormLabel :class="{ 'text-danger': prepaidIncomeForm.invalid('amount_receivable') }">
                {{ t('views.prepaid_income.fields.amount_receivable') }}
              </FormLabel>
              <FormInputCurrency
                v-model="prepaidIncomeForm.amount_receivable"
                :allow-negative="false"
                :class="{ 'border-danger': prepaidIncomeForm.invalid('amount_receivable') }"
                :placeholder="t('views.prepaid_income.fields.amount_receivable')"
                @change="handleAmountChange('amount_receivable')"
              />
              <FormErrorMessages :messages="prepaidIncomeForm.errors.amount_receivable" />
            </div>

            <div class="col-span-12 lg:col-span-2">
              <FormLabel :class="{ 'text-danger': prepaidIncomeForm.invalid('due_days') }">
                {{ t('views.prepaid_income.fields.due_days') }}
              </FormLabel>
              <FormInput
                id="due_days"
                v-model="prepaidIncomeForm.due_days"
                type="number"
                min="0"
                :class="{ 'border-danger': prepaidIncomeForm.invalid('due_days') }"
                :placeholder="t('views.prepaid_income.fields.due_days')"
                @change="prepaidIncomeForm.validate('due_days')"
              />
              <FormErrorMessages :messages="prepaidIncomeForm.errors.due_days" />
            </div>

            <div class="col-span-12 lg:col-span-2">
              <FormLabel>
                {{ t('views.prepaid_income.fields.amount_total') }}
              </FormLabel>
              <FormInputCurrency
                id="amount_total"
                :model-value="amountTotalPreview"
                readonly
                :allow-negative="false"
              />
            </div>

            <div class="col-span-12">
              <FormLabel :class="{ 'text-danger': prepaidIncomeForm.invalid('remarks') }">
                {{ t('views.prepaid_income.fields.remarks') }}
              </FormLabel>
              <FormTextarea
                v-model="prepaidIncomeForm.remarks"
                rows="3"
                :class="{ 'border-danger': prepaidIncomeForm.invalid('remarks') }"
                :placeholder="t('views.prepaid_income.fields.remarks')"
                @change="prepaidIncomeForm.validate('remarks')"
              />
              <FormErrorMessages :messages="prepaidIncomeForm.errors.remarks" />
            </div>
          </div>
        </div>
      </template>

      <template #card-items-2>
        <div class="p-5 space-y-4">
          <FormErrorMessages :messages="(prepaidIncomeForm.errors as any).payments" />
          <FormErrorMessages :messages="(prepaidIncomeForm.errors as any).delete_payment_ids" />

          <div v-if="prepaidIncomePaymentsForm.length === 0" class="text-right text-slate-500 text-sm">
            {{ t('components.data-list.data_not_found') }}
          </div>

          <div v-else class="space-y-4">
            <div v-for="(payment, index) in prepaidIncomePaymentsForm" :key="payment.id ?? `prepaid-income-payment-${index}`" class="space-y-3">
              <div class="grid grid-cols-12 gap-4 gap-y-3">
                <div class="col-span-12 md:col-span-4 lg:col-span-2">
                  <FormLabel :class="{ 'text-danger': invalidPrepaidIncomeField(`payments.${index}.code`) }">
                    {{ t('views.prepaid_income.fields.code') }}
                  </FormLabel>
                  <FormInputCode
                    :id="`payments.${index}.code`"
                    v-model="payment.code"
                    :class="{ 'border-danger': invalidPrepaidIncomeField(`payments.${index}.code`) }"
                    :placeholder="t('views.prepaid_income.fields.code')"
                    @set-auto="setPaymentCode(index)"
                    @change="validatePrepaidIncomeField(`payments.${index}.code`)"
                  />
                  <FormErrorMessages :messages="getPrepaidIncomeFieldErrors(`payments.${index}.code`)" />
                </div>

                <div class="col-span-12 md:col-span-8 lg:col-span-3">
                  <FormLabel :class="{ 'text-danger': invalidPrepaidIncomeField(`payments.${index}.date`) }">
                    {{ t('views.prepaid_income.fields.date') }}
                  </FormLabel>
                  <FormInputDateTimeAuto
                    :id="`payments.${index}.date`"
                    v-model="payment.date"
                    :class="{ 'border-danger': invalidPrepaidIncomeField(`payments.${index}.date`) }"
                    :placeholder="t('views.prepaid_income.fields.date')"
                    @change="validatePrepaidIncomeField(`payments.${index}.date`)"
                  />
                  <FormErrorMessages :messages="getPrepaidIncomeFieldErrors(`payments.${index}.date`)" />
                </div>

                <div class="col-span-12 lg:col-span-4">
                  <FormLabel :class="{ 'text-danger': invalidPrepaidIncomeField(`payments.${index}.cash_account_id`) }">
                    {{ t('views.income_payment.fields.cash_account') }}
                  </FormLabel>
                  <FormSelectSearch
                    :id="`payments.${index}.cash_account_id`"
                    v-model="payment.cash_account_id"
                    v-model:search="paymentCashAccountSearch"
                    :options="paidImmediatelyCashAccountOptions"
                    :placeholder="t('components.dropdown.placeholder')"
                    :class="{ 'border-danger': invalidPrepaidIncomeField(`payments.${index}.cash_account_id`) }"
                    @change="validatePrepaidIncomeField(`payments.${index}.cash_account_id`)"
                    @search="loadPaidImmediatelyCashAccountDDL"
                    @clear="clearPaymentCashAccount(index)"
                  />
                  <FormErrorMessages :messages="getPrepaidIncomeFieldErrors(`payments.${index}.cash_account_id`)" />
                </div>

                <div class="col-span-12 md:col-span-8 lg:col-span-3">
                  <FormLabel :class="{ 'text-danger': invalidPrepaidIncomeField(`payments.${index}.amount`) }">
                    {{ t('views.income_payment.fields.amount') }}
                  </FormLabel>
                  <div class="flex items-start gap-2">
                    <div class="flex-1 min-w-0">
                      <FormInputCurrency
                        :id="`payments.${index}.amount`"
                        v-model="payment.amount"
                        :allow-negative="false"
                        :class="{ 'border-danger': invalidPrepaidIncomeField(`payments.${index}.amount`) }"
                        @change="validatePrepaidIncomeField(`payments.${index}.amount`)"
                      />
                    </div>
                    <div class="shrink-0">
                      <Button
                        type="button"
                        variant="outline-secondary"
                        class="h-[38px] w-[38px] min-w-0 flex items-center justify-center"
                        @click="removePayment(index)"
                      >
                        <Lucide icon="Trash2" class="w-4 h-4 text-danger" />
                      </Button>
                    </div>
                  </div>
                  <FormErrorMessages :messages="getPrepaidIncomeFieldErrors(`payments.${index}.amount`)" />
                </div>
              </div>

              <div class="grid grid-cols-12 gap-4 gap-y-3">
                <div class="col-span-12">
                  <FormLabel :class="{ 'text-danger': invalidPrepaidIncomeField(`payments.${index}.remarks`) }">
                    {{ t('views.prepaid_income.fields.remarks') }}
                  </FormLabel>
                  <FormTextarea
                    :id="`payments.${index}.remarks`"
                    v-model="payment.remarks"
                    rows="2"
                    :class="{ 'border-danger': invalidPrepaidIncomeField(`payments.${index}.remarks`) }"
                    :placeholder="t('views.prepaid_income.fields.remarks')"
                    @change="validatePrepaidIncomeField(`payments.${index}.remarks`)"
                  />
                  <FormErrorMessages :messages="getPrepaidIncomeFieldErrors(`payments.${index}.remarks`)" />
                </div>
              </div>
            </div>
          </div>

          <div class="flex justify-end">
            <Button type="button" variant="outline-primary" @click="addPayment">
              <Lucide icon="Plus" class="w-4 h-4 mr-1" />
              {{ t('components.buttons.create_new') }}
            </Button>
          </div>
        </div>
      </template>

      <template #card-items-3>
        <div class="p-5">
          <FormLabel>
            {{ t('views.prepaid_income.fields.images') }}
          </FormLabel>
          <PrepaidIncomeImagesField
            v-model="prepaidIncomeForm.image_hashes"
            v-model:existing-images="uploadedImages"
            v-model:delete-image-ids="prepaidIncomeForm.delete_image_ids"
          />
          <FormErrorMessages :messages="(prepaidIncomeForm.errors as any).image_hashes" />
          <FormErrorMessages :messages="(prepaidIncomeForm.errors as any).delete_image_ids" />
        </div>
      </template>

      <template #card-items-button>
        <div class="flex gap-4">
          <Button
            type="submit"
            href="#"
            variant="primary"
            class="w-28 shadow-md"
            :disabled="prepaidIncomeForm.validating || prepaidIncomeForm.hasErrors"
          >
            <Lucide v-if="prepaidIncomeForm.validating" icon="Loader" class="animate-spin" />
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
