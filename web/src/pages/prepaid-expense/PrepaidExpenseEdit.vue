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
import PrepaidExpenseService from '@/services/PrepaidExpenseService';
import ExpenseCategoryService from '@/services/ExpenseCategoryService';
import CashAccountService from '@/services/CashAccountService';
import PrepaidExpenseImagesField from '@/components/PrepaidExpense/PrepaidExpenseImagesField.vue';
import Button from '@/components/Base/Button';
import Lucide from '@/components/Base/Lucide';
import { ErrorCode } from '@/types/enums/ErrorCode';
import type { DropDownOption } from '@/types/models/DropDownOption';
import type { AlertPlaceholderProps } from '@/components/AlertPlaceholder/AlertPlaceholder.vue';
import type { PrepaidExpense } from '@/types/models/PrepaidExpense';
import type { ExpenseCategory } from '@/types/models/ExpenseCategory';
import type { CashAccount } from '@/types/models/CashAccount';
import type { PrepaidExpenseImage } from '@/types/models/PrepaidExpenseImage';
import type { PrepaidExpensePayment } from '@/types/models/PrepaidExpensePayment';

type PrepaidExpensePaymentFormItem = {
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

const prepaidExpenseService = new PrepaidExpenseService();
const expenseCategoryService = new ExpenseCategoryService();
const cashAccountService = new CashAccountService();

const cards = ref<Array<TwoColumnsLayoutCards>>([
  {
    title: 'views.prepaid_expense.field_groups.company_info',
    state: CardState.Expanded,
  },
  {
    title: 'views.prepaid_expense.field_groups.prepaid_expense_data',
    state: CardState.Expanded,
  },
  {
    title: 'views.prepaid_expense.field_groups.payments',
    state: CardState.Expanded,
  },
  {
    title: 'views.prepaid_expense.field_groups.images',
    state: CardState.Expanded,
  },
  { title: '', state: CardState.Hidden, id: 'button' },
]);

const prepaidExpenseForm = prepaidExpenseService.usePrepaidExpenseEditForm(route.params.ulid.toString());
const prepaidExpenseData = ref<PrepaidExpense | null>(null);
const uploadedImages = ref<PrepaidExpenseImage[]>([]);

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
  Number(prepaidExpenseForm.amount_paid_immediately ?? 0) + Number(prepaidExpenseForm.amount_payable ?? 0),
);
const prepaidExpensePaymentsForm = computed<PrepaidExpensePaymentFormItem[]>(
  () => prepaidExpenseForm.payments as PrepaidExpensePaymentFormItem[],
);
const paymentCashAccountSearch = ref<string>('');
const invalidPrepaidExpenseField = (field: string) => prepaidExpenseForm.invalid(field as any);
const validatePrepaidExpenseField = (field: string) => prepaidExpenseForm.validate(field as any);
const getPrepaidExpenseFieldErrors = (field: string): string | undefined => {
  const errors = (prepaidExpenseForm.errors as Record<string, string | string[] | undefined>)[field];

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
  const result = await prepaidExpenseService.read(route.params.ulid.toString());
  emits('loading-state', false);

  if (result.success && result.data) {
    prepaidExpenseData.value = result.data;

    prepaidExpenseForm.setData({
      company_id: result.data.company?.id ?? '',
      branch_id: result.data.branch?.id ?? '',
      code: result.data.code,
      date: formatDate(result.data.date, 'YYYY-MM-DD HH:mm:ss'),
      expense_category_id: result.data.category?.id ?? '',
      estimated_useful_life: result.data.estimated_useful_life,
      paid_immediately_cash_account_id: result.data.paid_immediately_cash_account?.id ?? '',
      amount_paid_immediately: result.data.amount_paid_immediately,
      amount_payable: result.data.amount_payable,
      due_days: result.data.due_days,
      remarks: result.data.remarks ?? '',
      delete_image_ids: [],
      image_hashes: [],
      delete_payment_ids: [],
      payments: (result.data.payments ?? []).map((payment: PrepaidExpensePayment) => ({
        id: payment.id,
        code: payment.code,
        date: formatDate(payment.date, 'YYYY-MM-DD HH:mm:ss'),
        cash_account_id: payment.cash_account?.id ?? '',
        amount: payment.amount,
        remarks: payment.remarks ?? '',
      })),
    } as any);

    if (result.data.prepaid_expense_images) {
      const images = result.data.prepaid_expense_images.map((img: PrepaidExpenseImage) => ({
        id: img.id,
        prepaid_expense_id: img.prepaid_expense_id,
        path: img.path,
        url: img.url,
        hash: img.hash,
        is_main: img.is_main,
      }));

      uploadedImages.value = images;
      prepaidExpenseForm.image_hashes = images.map((img: PrepaidExpenseImage) => ({
        hash: img.hash,
        is_main: img.is_main,
      }));
    } else {
      uploadedImages.value = [];
    }
  } else {
    router.push({ name: 'side-menu-prepaid-expense-list' });
  }
};

const loadCategoryDDL = async (search = '') => {
  if (!selectedUserLocation.value) return;

  const result = await expenseCategoryService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    search,
    parent_id: undefined,
    has_parent: undefined,
    has_children: false,
    include_id: prepaidExpenseData.value?.category?.id,
    refresh: false,
    limit: 20,
  });

  if (result.success && result.data) {
    categoryDDL.value = result.data.data.map((item: ExpenseCategory) => ({
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
    include_id: prepaidExpenseData.value?.paid_immediately_cash_account?.id,
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
  prepaidExpenseForm.forgetError('code');

  if (prepaidExpenseForm.code === '_AUTO_') {
    prepaidExpenseForm.setData({ code: '' });
    return;
  }

  prepaidExpenseForm.setData({ code: '_AUTO_' });
};

const clearCategory = () => {
  prepaidExpenseForm.setData({ expense_category_id: '' });
  prepaidExpenseForm.forgetError('expense_category_id');
};

const clearPaidImmediatelyCashAccount = () => {
  prepaidExpenseForm.setData({ paid_immediately_cash_account_id: '' });
  prepaidExpenseForm.forgetError('paid_immediately_cash_account_id');
};

const clearAmountTotalError = () => {
  const { amount_total, ...restErrors } = prepaidExpenseForm.errors as Record<string, string | string[]>;

  if (amount_total) {
    prepaidExpenseForm.setErrors(restErrors);
  }
};

const handleAmountChange = (field: 'amount_paid_immediately' | 'amount_payable') => {
  clearAmountTotalError();
  prepaidExpenseForm.validate(field);
};

const setPaymentCode = (index: number) => {
  prepaidExpenseForm.forgetError(`payments.${index}.code` as any);
  prepaidExpensePaymentsForm.value[index].code =
    prepaidExpensePaymentsForm.value[index].code === '_AUTO_' ? '' : '_AUTO_';
};

const clearPaymentCashAccount = (index: number) => {
  const payment = prepaidExpensePaymentsForm.value[index];
  if (!payment) return;

  payment.cash_account_id = '';
  validatePrepaidExpenseField(`payments.${index}.cash_account_id`);
};

const addPayment = () => {
  prepaidExpensePaymentsForm.value.push({
    id: null,
    code: '_AUTO_',
    date: '_AUTO_',
    cash_account_id: '',
    amount: 0,
    remarks: '',
  });
};

const removePayment = (index: number) => {
  const payment = prepaidExpensePaymentsForm.value[index];

  if (payment?.id && !prepaidExpenseForm.delete_payment_ids.includes(payment.id)) {
    prepaidExpenseForm.delete_payment_ids.push(payment.id);
  }

  prepaidExpensePaymentsForm.value.splice(index, 1);

  Object.keys(prepaidExpenseForm.errors).forEach((key) => {
    if (key === 'payments' || key.startsWith('payments.')) {
      prepaidExpenseForm.forgetError(key as any);
    }
  });
};

const onSubmit = async () => {
  if (prepaidExpenseForm.hasErrors) {
    const firstErrorKey = Object.keys(prepaidExpenseForm.errors)[0];

    if (firstErrorKey) {
      scrollToError(firstErrorKey);
    }

    return;
  }

  emits('loading-state', true);

  await prepaidExpenseForm
    .submit()
    .then(() => {
      showAlertPlaceholder('hidden', '', null);
      emits('update-profile');
      router.push({ name: 'side-menu-prepaid-expense-list' });
    })
    .catch((error) => {
      showAlertPlaceholder('danger', '', convertErrorTypeToAlertListType(error));
    })
    .finally(() => {
      emits('loading-state', false);
    });
};

const resetForm = async () => {
  prepaidExpenseForm.reset();
  prepaidExpenseForm.setErrors({});
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
  <form id="prepaidExpenseForm" @submit.prevent="onSubmit">
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
              <FormInputCurrency type="hidden" v-model="prepaidExpenseForm.company_id" />
            </div>
            <div class="col-span-12 md:col-span-6">
              <FormLabel>
                {{ selectedUserLocation.branch.code }}
                <br />
                {{ selectedUserLocation.branch.name }}
              </FormLabel>
              <FormInput type="hidden" v-model="prepaidExpenseForm.branch_id" />
            </div>
          </div>
        </div>
      </template>

      <template #card-items-1>
        <div class="p-5">
          <div class="grid grid-cols-12 gap-4 gap-y-3">
            <div class="col-span-12 lg:col-span-3">
              <FormLabel :class="{ 'text-danger': prepaidExpenseForm.invalid('code') }">
                {{ t('views.prepaid_expense.fields.code') }}
              </FormLabel>
              <FormInputCode
                v-model="prepaidExpenseForm.code"
                :class="{ 'border-danger': prepaidExpenseForm.invalid('code') }"
                :placeholder="t('views.prepaid_expense.fields.code')"
                @set-auto="setCode"
                @change="prepaidExpenseForm.validate('code')"
              />
              <FormErrorMessages :messages="prepaidExpenseForm.errors.code" />
            </div>

            <div class="col-span-12 lg:col-span-3">
              <FormLabel :class="{ 'text-danger': prepaidExpenseForm.invalid('date') }">
                {{ t('views.prepaid_expense.fields.date') }}
              </FormLabel>
              <FormInputDateTimeAuto
                v-model="prepaidExpenseForm.date"
                :class="{ 'border-danger': prepaidExpenseForm.invalid('date') }"
                :placeholder="t('views.prepaid_expense.fields.date')"
                @change="prepaidExpenseForm.validate('date')"
              />
              <FormErrorMessages :messages="prepaidExpenseForm.errors.date" />
            </div>

            <div class="col-span-12 lg:col-span-6">
              <FormLabel :class="{ 'text-danger': prepaidExpenseForm.invalid('expense_category_id') }">
                {{ t('views.prepaid_expense.fields.category') }}
              </FormLabel>
              <FormSelectSearch
                v-model="prepaidExpenseForm.expense_category_id"
                v-model:search="categorySearch"
                :options="categoryOptions"
                :placeholder="t('components.dropdown.placeholder')"
                :class="{ 'border-danger': prepaidExpenseForm.invalid('expense_category_id') }"
                @change="prepaidExpenseForm.validate('expense_category_id')"
                @search="loadCategoryDDL"
                @clear="clearCategory"
              />
              <FormErrorMessages :messages="prepaidExpenseForm.errors.expense_category_id" />
            </div>

            <div class="col-span-12 lg:col-span-4">
              <FormLabel :class="{ 'text-danger': prepaidExpenseForm.invalid('estimated_useful_life') }">
                {{ t('views.prepaid_expense.fields.estimated_useful_life') }}
              </FormLabel>
              <FormInputCurrency
                id="estimated_useful_life"
                v-model="prepaidExpenseForm.estimated_useful_life"
                :class="{ 'border-danger': prepaidExpenseForm.invalid('estimated_useful_life') }"
                :placeholder="t('views.prepaid_expense.fields.estimated_useful_life')"
                @change="prepaidExpenseForm.validate('estimated_useful_life')"
              />
              <div class="mt-1 text-xs text-slate-500">
                {{ t('views.prepaid_expense.hints.estimated_useful_life') }}
              </div>
              <FormErrorMessages :messages="prepaidExpenseForm.errors.estimated_useful_life" />
            </div>

            <div class="col-span-12 lg:col-span-4">
              <FormLabel :class="{ 'text-danger': prepaidExpenseForm.invalid('paid_immediately_cash_account_id') }">
                {{ t('views.prepaid_expense.fields.paid_immediately_cash_account') }}
              </FormLabel>
              <FormSelectSearch
                v-model="prepaidExpenseForm.paid_immediately_cash_account_id"
                v-model:search="paidImmediatelyCashAccountSearch"
                :options="paidImmediatelyCashAccountOptions"
                :placeholder="t('components.dropdown.placeholder')"
                :class="{ 'border-danger': prepaidExpenseForm.invalid('paid_immediately_cash_account_id') }"
                @change="prepaidExpenseForm.validate('paid_immediately_cash_account_id')"
                @search="loadPaidImmediatelyCashAccountDDL"
                @clear="clearPaidImmediatelyCashAccount"
              />
              <FormErrorMessages :messages="prepaidExpenseForm.errors.paid_immediately_cash_account_id" />
            </div>

            <div class="col-span-12 lg:col-span-2">
              <FormLabel :class="{ 'text-danger': prepaidExpenseForm.invalid('amount_paid_immediately') }">
                {{ t('views.prepaid_expense.fields.amount_paid_immediately') }}
              </FormLabel>
              <FormInputCurrency
                v-model="prepaidExpenseForm.amount_paid_immediately"
                :allow-negative="false"
                :class="{ 'border-danger': prepaidExpenseForm.invalid('amount_paid_immediately') }"
                :placeholder="t('views.prepaid_expense.fields.amount_paid_immediately')"
                @change="handleAmountChange('amount_paid_immediately')"
              />
              <FormErrorMessages :messages="prepaidExpenseForm.errors.amount_paid_immediately" />
            </div>

            <div class="col-span-12 lg:col-span-2">
              <FormLabel :class="{ 'text-danger': prepaidExpenseForm.invalid('amount_payable') }">
                {{ t('views.prepaid_expense.fields.amount_payable') }}
              </FormLabel>
              <FormInputCurrency
                v-model="prepaidExpenseForm.amount_payable"
                :allow-negative="false"
                :class="{ 'border-danger': prepaidExpenseForm.invalid('amount_payable') }"
                :placeholder="t('views.prepaid_expense.fields.amount_payable')"
                @change="handleAmountChange('amount_payable')"
              />
              <FormErrorMessages :messages="prepaidExpenseForm.errors.amount_payable" />
            </div>

            <div class="col-span-12 lg:col-span-2">
              <FormLabel :class="{ 'text-danger': prepaidExpenseForm.invalid('due_days') }">
                {{ t('views.prepaid_expense.fields.due_days') }}
              </FormLabel>
              <FormInputCurrency
                id="due_days"
                v-model="prepaidExpenseForm.due_days"
                :allow-negative="false"
                :class="{ 'border-danger': prepaidExpenseForm.invalid('due_days') }"
                :placeholder="t('views.prepaid_expense.fields.due_days')"
                @change="prepaidExpenseForm.validate('due_days')"
              />
              <FormErrorMessages :messages="prepaidExpenseForm.errors.due_days" />
            </div>

            <div class="col-span-12 lg:col-span-2">
              <FormLabel>
                {{ t('views.prepaid_expense.fields.amount_total') }}
              </FormLabel>
              <FormInputCurrency
                id="amount_total"
                :model-value="amountTotalPreview"
                readonly
                :allow-negative="false"
              />
            </div>

            <div class="col-span-12">
              <FormLabel :class="{ 'text-danger': prepaidExpenseForm.invalid('remarks') }">
                {{ t('views.prepaid_expense.fields.remarks') }}
              </FormLabel>
              <FormTextarea
                v-model="prepaidExpenseForm.remarks"
                rows="3"
                :class="{ 'border-danger': prepaidExpenseForm.invalid('remarks') }"
                :placeholder="t('views.prepaid_expense.fields.remarks')"
                @change="prepaidExpenseForm.validate('remarks')"
              />
              <FormErrorMessages :messages="prepaidExpenseForm.errors.remarks" />
            </div>
          </div>
        </div>
      </template>

      <template #card-items-2>
        <div class="p-5 space-y-4">
          <FormErrorMessages :messages="(prepaidExpenseForm.errors as any).payments" />
          <FormErrorMessages :messages="(prepaidExpenseForm.errors as any).delete_payment_ids" />

          <div v-if="prepaidExpensePaymentsForm.length === 0" class="text-right text-slate-500 text-sm">
            {{ t('components.data-list.data_not_found') }}
          </div>

          <div v-else class="space-y-4">
            <div v-for="(payment, index) in prepaidExpensePaymentsForm" :key="payment.id ?? `prepaid-expense-payment-${index}`" class="space-y-3">
              <div class="grid grid-cols-12 gap-4 gap-y-3">
                <div class="col-span-12 md:col-span-4 lg:col-span-2">
                  <FormLabel :class="{ 'text-danger': invalidPrepaidExpenseField(`payments.${index}.code`) }">
                    {{ t('views.prepaid_expense.fields.code') }}
                  </FormLabel>
                  <FormInputCode
                    :id="`payments.${index}.code`"
                    v-model="payment.code"
                    :class="{ 'border-danger': invalidPrepaidExpenseField(`payments.${index}.code`) }"
                    :placeholder="t('views.prepaid_expense.fields.code')"
                    @set-auto="setPaymentCode(index)"
                    @change="validatePrepaidExpenseField(`payments.${index}.code`)"
                  />
                  <FormErrorMessages :messages="getPrepaidExpenseFieldErrors(`payments.${index}.code`)" />
                </div>

                <div class="col-span-12 md:col-span-8 lg:col-span-3">
                  <FormLabel :class="{ 'text-danger': invalidPrepaidExpenseField(`payments.${index}.date`) }">
                    {{ t('views.prepaid_expense.fields.date') }}
                  </FormLabel>
                  <FormInputDateTimeAuto
                    :id="`payments.${index}.date`"
                    v-model="payment.date"
                    :class="{ 'border-danger': invalidPrepaidExpenseField(`payments.${index}.date`) }"
                    :placeholder="t('views.prepaid_expense.fields.date')"
                    @change="validatePrepaidExpenseField(`payments.${index}.date`)"
                  />
                  <FormErrorMessages :messages="getPrepaidExpenseFieldErrors(`payments.${index}.date`)" />
                </div>

                <div class="col-span-12 lg:col-span-4">
                  <FormLabel :class="{ 'text-danger': invalidPrepaidExpenseField(`payments.${index}.cash_account_id`) }">
                    {{ t('views.expense_payment.fields.cash_account') }}
                  </FormLabel>
                  <FormSelectSearch
                    :id="`payments.${index}.cash_account_id`"
                    v-model="payment.cash_account_id"
                    v-model:search="paymentCashAccountSearch"
                    :options="paidImmediatelyCashAccountOptions"
                    :placeholder="t('components.dropdown.placeholder')"
                    :class="{ 'border-danger': invalidPrepaidExpenseField(`payments.${index}.cash_account_id`) }"
                    @change="validatePrepaidExpenseField(`payments.${index}.cash_account_id`)"
                    @search="loadPaidImmediatelyCashAccountDDL"
                    @clear="clearPaymentCashAccount(index)"
                  />
                  <FormErrorMessages :messages="getPrepaidExpenseFieldErrors(`payments.${index}.cash_account_id`)" />
                </div>

                <div class="col-span-12 md:col-span-8 lg:col-span-3">
                  <FormLabel :class="{ 'text-danger': invalidPrepaidExpenseField(`payments.${index}.amount`) }">
                    {{ t('views.expense_payment.fields.amount') }}
                  </FormLabel>
                  <div class="flex items-start gap-2">
                    <div class="flex-1 min-w-0">
                      <FormInputCurrency
                        :id="`payments.${index}.amount`"
                        v-model="payment.amount"
                        :allow-negative="false"
                        :class="{ 'border-danger': invalidPrepaidExpenseField(`payments.${index}.amount`) }"
                        @change="validatePrepaidExpenseField(`payments.${index}.amount`)"
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
                  <FormErrorMessages :messages="getPrepaidExpenseFieldErrors(`payments.${index}.amount`)" />
                </div>
              </div>

              <div class="grid grid-cols-12 gap-4 gap-y-3">
                <div class="col-span-12">
                  <FormLabel :class="{ 'text-danger': invalidPrepaidExpenseField(`payments.${index}.remarks`) }">
                    {{ t('views.prepaid_expense.fields.remarks') }}
                  </FormLabel>
                  <FormTextarea
                    :id="`payments.${index}.remarks`"
                    v-model="payment.remarks"
                    rows="2"
                    :class="{ 'border-danger': invalidPrepaidExpenseField(`payments.${index}.remarks`) }"
                    :placeholder="t('views.prepaid_expense.fields.remarks')"
                    @change="validatePrepaidExpenseField(`payments.${index}.remarks`)"
                  />
                  <FormErrorMessages :messages="getPrepaidExpenseFieldErrors(`payments.${index}.remarks`)" />
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
            {{ t('views.prepaid_expense.fields.images') }}
          </FormLabel>
          <PrepaidExpenseImagesField
            v-model="prepaidExpenseForm.image_hashes"
            v-model:existing-images="uploadedImages"
            v-model:delete-image-ids="prepaidExpenseForm.delete_image_ids"
          />
          <FormErrorMessages :messages="(prepaidExpenseForm.errors as any).image_hashes" />
          <FormErrorMessages :messages="(prepaidExpenseForm.errors as any).delete_image_ids" />
        </div>
      </template>

      <template #card-items-button>
        <div class="flex gap-4">
          <Button
            type="submit"
            href="#"
            variant="primary"
            class="w-28 shadow-md"
            :disabled="prepaidExpenseForm.validating || prepaidExpenseForm.hasErrors"
          >
            <Lucide v-if="prepaidExpenseForm.validating" icon="Loader" class="animate-spin" />
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
