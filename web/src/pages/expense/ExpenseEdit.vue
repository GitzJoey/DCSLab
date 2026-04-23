<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
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
  FormTextarea,
} from '@/components/Base/Form';
import ExpenseService from '@/services/ExpenseService';
import ExpenseCategoryService from '@/services/ExpenseCategoryService';
import CashAccountService from '@/services/CashAccountService';
import ExpenseImagesField from '@/components/Expense/ExpenseImagesField.vue';
import Button from '@/components/Base/Button';
import Lucide from '@/components/Base/Lucide';
import { ErrorCode } from '@/types/enums/ErrorCode';
import type { DropDownOption } from '@/types/models/DropDownOption';
import type { AlertPlaceholderProps } from '@/components/AlertPlaceholder/AlertPlaceholder.vue';
import type { Expense } from '@/types/models/Expense';
import type { ExpenseCategory } from '@/types/models/ExpenseCategory';
import type { CashAccount } from '@/types/models/CashAccount';
import type { ExpenseImage } from '@/types/models/ExpenseImage';
import type { ExpensePayment } from '@/types/models/ExpensePayment';

type ExpensePaymentFormItem = {
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

const expenseService = new ExpenseService();
const expenseCategoryService = new ExpenseCategoryService();
const cashAccountService = new CashAccountService();

const cards = ref<Array<TwoColumnsLayoutCards>>([
  {
    title: 'views.expense.field_groups.company_info',
    state: CardState.Expanded,
  },
  {
    title: 'views.expense.field_groups.expense_data',
    state: CardState.Expanded,
  },
  {
    title: 'views.expense.field_groups.payments',
    state: CardState.Expanded,
  },
  {
    title: 'views.expense.field_groups.images',
    state: CardState.Expanded,
  },
  { title: '', state: CardState.Hidden, id: 'button' },
]);

const expenseForm = expenseService.useExpenseEditForm(route.params.ulid.toString());
const expenseData = ref<Expense | null>(null);
const uploadedImages = ref<ExpenseImage[]>([]);

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
  Number(expenseForm.amount_paid_immediately ?? 0) + Number(expenseForm.amount_payable ?? 0),
);
const expensePaymentsForm = computed<ExpensePaymentFormItem[]>(
  () => expenseForm.payments as ExpensePaymentFormItem[],
);
const paymentCashAccountSearch = ref<string>('');
const invalidExpenseField = (field: string) => expenseForm.invalid(field as any);
const validateExpenseField = (field: string) => expenseForm.validate(field as any);
const getExpenseFieldErrors = (field: string): string | undefined => {
  const errors = (expenseForm.errors as Record<string, string | string[] | undefined>)[field];

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
  const result = await expenseService.read(route.params.ulid.toString());
  emits('loading-state', false);

  if (result.success && result.data) {
    expenseData.value = result.data;

    expenseForm.setData({
      company_id: result.data.company?.id ?? '',
      branch_id: result.data.branch?.id ?? '',
      code: result.data.code,
      date: formatDate(result.data.date, 'YYYY-MM-DD HH:mm:ss'),
      expense_category_id: result.data.category?.id ?? '',
      paid_immediately_cash_account_id: result.data.paid_immediately_cash_account?.id ?? '',
      amount_paid_immediately: result.data.amount_paid_immediately,
      amount_payable: result.data.amount_payable,
      due_days: result.data.due_days,
      remarks: result.data.remarks ?? '',
      delete_image_ids: [],
      image_hashes: [],
      delete_payment_ids: [],
      payments: (result.data.payments ?? []).map((payment: ExpensePayment) => ({
        id: payment.id,
        code: payment.code,
        date: formatDate(payment.date, 'YYYY-MM-DD HH:mm:ss'),
        cash_account_id: payment.cash_account?.id ?? '',
        amount: payment.amount,
        remarks: payment.remarks ?? '',
      })),
    } as any);

    if (result.data.expense_images) {
      const images = result.data.expense_images.map((img: ExpenseImage) => ({
        id: img.id,
        expense_id: img.expense_id,
        path: img.path,
        url: img.url,
        hash: img.hash,
        is_main: img.is_main,
      }));

      uploadedImages.value = images;
      expenseForm.image_hashes = images.map((img: ExpenseImage) => ({
        hash: img.hash,
        is_main: img.is_main,
      }));
    } else {
      uploadedImages.value = [];
    }
  } else {
    router.push({ name: 'side-menu-expense-list' });
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
    include_id: expenseData.value?.category?.id,
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
    include_id: expenseData.value?.paid_immediately_cash_account?.id,
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
  expenseForm.forgetError('code');

  if (expenseForm.code === '_AUTO_') {
    expenseForm.setData({ code: '' });
    return;
  }

  expenseForm.setData({ code: '_AUTO_' });
};

const clearCategory = () => {
  expenseForm.setData({ expense_category_id: '' });
  expenseForm.forgetError('expense_category_id');
};

const clearPaidImmediatelyCashAccount = () => {
  expenseForm.setData({ paid_immediately_cash_account_id: '' });
  expenseForm.forgetError('paid_immediately_cash_account_id');
};

const clearAmountTotalError = () => {
  const { amount_total, ...restErrors } = expenseForm.errors as Record<string, string | string[]>;

  if (amount_total) {
    expenseForm.setErrors(restErrors);
  }
};

const handleAmountChange = (field: 'amount_paid_immediately' | 'amount_payable') => {
  clearAmountTotalError();
  expenseForm.validate(field);
};

const setPaymentCode = (index: number) => {
  expenseForm.forgetError(`payments.${index}.code` as any);
  expensePaymentsForm.value[index].code = expensePaymentsForm.value[index].code === '_AUTO_' ? '' : '_AUTO_';
};

const clearPaymentCashAccount = (index: number) => {
  const payment = expensePaymentsForm.value[index];
  if (!payment) return;

  payment.cash_account_id = '';
  validateExpenseField(`payments.${index}.cash_account_id`);
};

const addPayment = () => {
  expensePaymentsForm.value.push({
    id: null,
    code: '_AUTO_',
    date: '_AUTO_',
    cash_account_id: '',
    amount: 0,
    remarks: '',
  });
};

const removePayment = (index: number) => {
  const payment = expensePaymentsForm.value[index];

  if (payment?.id && !expenseForm.delete_payment_ids.includes(payment.id)) {
    expenseForm.delete_payment_ids.push(payment.id);
  }

  expensePaymentsForm.value.splice(index, 1);

  Object.keys(expenseForm.errors).forEach((key) => {
    if (key === 'payments' || key.startsWith('payments.')) {
      expenseForm.forgetError(key as any);
    }
  });
};

const onSubmit = async () => {
  if (expenseForm.hasErrors) {
    const firstErrorKey = Object.keys(expenseForm.errors)[0];

    if (firstErrorKey) {
      scrollToError(firstErrorKey);
    }

    return;
  }

  emits('loading-state', true);

  await expenseForm
    .submit()
    .then(() => {
      showAlertPlaceholder('hidden', '', null);
      emits('update-profile');
      router.push({ name: 'side-menu-expense-list' });
    })
    .catch((error) => {
      showAlertPlaceholder('danger', '', convertErrorTypeToAlertListType(error));
    })
    .finally(() => {
      emits('loading-state', false);
    });
};

const resetForm = async () => {
  expenseForm.reset();
  expenseForm.setErrors({});
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
  <form id="expenseForm" @submit.prevent="onSubmit">
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
              <FormInput type="hidden" v-model="expenseForm.company_id" />
            </div>
            <div class="col-span-12 md:col-span-6">
              <FormLabel>
                {{ selectedUserLocation.branch.code }}
                <br />
                {{ selectedUserLocation.branch.name }}
              </FormLabel>
              <FormInput type="hidden" v-model="expenseForm.branch_id" />
            </div>
          </div>
        </div>
      </template>

      <template #card-items-1>
        <div class="p-5">
          <div class="grid grid-cols-12 gap-4 gap-y-3">
            <div class="col-span-12 lg:col-span-3">
              <FormLabel :class="{ 'text-danger': expenseForm.invalid('code') }">
                {{ t('views.expense.fields.code') }}
              </FormLabel>
              <FormInputCode
                v-model="expenseForm.code"
                :class="{ 'border-danger': expenseForm.invalid('code') }"
                :placeholder="t('views.expense.fields.code')"
                @set-auto="setCode"
                @change="expenseForm.validate('code')"
              />
              <FormErrorMessages :messages="expenseForm.errors.code" />
            </div>

            <div class="col-span-12 lg:col-span-3">
              <FormLabel :class="{ 'text-danger': expenseForm.invalid('date') }">
                {{ t('views.expense.fields.date') }}
              </FormLabel>
              <FormInputDateTimeAuto
                v-model="expenseForm.date"
                :class="{ 'border-danger': expenseForm.invalid('date') }"
                :placeholder="t('views.expense.fields.date')"
                @change="expenseForm.validate('date')"
              />
              <FormErrorMessages :messages="expenseForm.errors.date" />
            </div>

            <div class="col-span-12 lg:col-span-6">
              <FormLabel :class="{ 'text-danger': expenseForm.invalid('expense_category_id') }">
                {{ t('views.expense.fields.category') }}
              </FormLabel>
              <FormSelectSearch
                v-model="expenseForm.expense_category_id"
                v-model:search="categorySearch"
                :options="categoryOptions"
                :placeholder="t('components.dropdown.placeholder')"
                :class="{ 'border-danger': expenseForm.invalid('expense_category_id') }"
                @change="expenseForm.validate('expense_category_id')"
                @search="loadCategoryDDL"
                @clear="clearCategory"
              />
              <FormErrorMessages :messages="expenseForm.errors.expense_category_id" />
            </div>

            <div class="col-span-12 lg:col-span-4">
              <FormLabel :class="{ 'text-danger': expenseForm.invalid('paid_immediately_cash_account_id') }">
                {{ t('views.expense.fields.paid_immediately_cash_account') }}
              </FormLabel>
              <FormSelectSearch
                v-model="expenseForm.paid_immediately_cash_account_id"
                v-model:search="paidImmediatelyCashAccountSearch"
                :options="paidImmediatelyCashAccountOptions"
                :placeholder="t('components.dropdown.placeholder')"
                :class="{ 'border-danger': expenseForm.invalid('paid_immediately_cash_account_id') }"
                @change="expenseForm.validate('paid_immediately_cash_account_id')"
                @search="loadPaidImmediatelyCashAccountDDL"
                @clear="clearPaidImmediatelyCashAccount"
              />
              <FormErrorMessages :messages="expenseForm.errors.paid_immediately_cash_account_id" />
            </div>

            <div class="col-span-12 lg:col-span-2">
              <FormLabel :class="{ 'text-danger': expenseForm.invalid('amount_paid_immediately') }">
                {{ t('views.expense.fields.amount_paid_immediately') }}
              </FormLabel>
              <FormInputCurrency
                v-model="expenseForm.amount_paid_immediately"
                :allow-negative="false"
                :class="{ 'border-danger': expenseForm.invalid('amount_paid_immediately') }"
                :placeholder="t('views.expense.fields.amount_paid_immediately')"
                @change="handleAmountChange('amount_paid_immediately')"
              />
              <FormErrorMessages :messages="expenseForm.errors.amount_paid_immediately" />
            </div>

            <div class="col-span-12 lg:col-span-2">
              <FormLabel :class="{ 'text-danger': expenseForm.invalid('amount_payable') }">
                {{ t('views.expense.fields.amount_payable') }}
              </FormLabel>
              <FormInputCurrency
                v-model="expenseForm.amount_payable"
                :allow-negative="false"
                :class="{ 'border-danger': expenseForm.invalid('amount_payable') }"
                :placeholder="t('views.expense.fields.amount_payable')"
                @change="handleAmountChange('amount_payable')"
              />
              <FormErrorMessages :messages="expenseForm.errors.amount_payable" />
            </div>

            <div class="col-span-12 lg:col-span-2">
              <FormLabel :class="{ 'text-danger': expenseForm.invalid('due_days') }">
                {{ t('views.expense.fields.due_days') }}
              </FormLabel>
              <FormInput
                id="due_days"
                v-model="expenseForm.due_days"
                type="number"
                min="0"
                :class="{ 'border-danger': expenseForm.invalid('due_days') }"
                :placeholder="t('views.expense.fields.due_days')"
                @change="expenseForm.validate('due_days')"
              />
              <FormErrorMessages :messages="expenseForm.errors.due_days" />
            </div>

            <div class="col-span-12 lg:col-span-2">
              <FormLabel>
                {{ t('views.expense.fields.amount_total') }}
              </FormLabel>
              <FormInputCurrency
                id="amount_total"
                :model-value="amountTotalPreview"
                readonly
                :allow-negative="false"
              />
            </div>

            <div class="col-span-12">
              <FormLabel :class="{ 'text-danger': expenseForm.invalid('remarks') }">
                {{ t('views.expense.fields.remarks') }}
              </FormLabel>
              <FormTextarea
                v-model="expenseForm.remarks"
                rows="3"
                :class="{ 'border-danger': expenseForm.invalid('remarks') }"
                :placeholder="t('views.expense.fields.remarks')"
                @change="expenseForm.validate('remarks')"
              />
              <FormErrorMessages :messages="expenseForm.errors.remarks" />
            </div>
          </div>
        </div>
      </template>

      <template #card-items-2>
        <div class="p-5 space-y-4">
          <FormErrorMessages :messages="(expenseForm.errors as any).payments" />
          <FormErrorMessages :messages="(expenseForm.errors as any).delete_payment_ids" />

          <div v-if="expensePaymentsForm.length === 0" class="text-right text-slate-500 text-sm">
            {{ t('components.data-list.data_not_found') }}
          </div>

          <div v-else class="space-y-4">
            <div v-for="(payment, index) in expensePaymentsForm" :key="payment.id ?? `expense-payment-${index}`" class="space-y-3">
              <div class="grid grid-cols-12 gap-4 gap-y-3">
                <div class="col-span-12 md:col-span-4 lg:col-span-2">
                  <FormLabel :class="{ 'text-danger': invalidExpenseField(`payments.${index}.code`) }">
                    {{ t('views.expense.fields.code') }}
                  </FormLabel>
                  <FormInputCode
                    :id="`payments.${index}.code`"
                    v-model="payment.code"
                    :class="{ 'border-danger': invalidExpenseField(`payments.${index}.code`) }"
                    :placeholder="t('views.expense.fields.code')"
                    @set-auto="setPaymentCode(index)"
                    @change="validateExpenseField(`payments.${index}.code`)"
                  />
                  <FormErrorMessages :messages="getExpenseFieldErrors(`payments.${index}.code`)" />
                </div>

                <div class="col-span-12 md:col-span-8 lg:col-span-3">
                  <FormLabel :class="{ 'text-danger': invalidExpenseField(`payments.${index}.date`) }">
                    {{ t('views.expense.fields.date') }}
                  </FormLabel>
                  <FormInputDateTimeAuto
                    :id="`payments.${index}.date`"
                    v-model="payment.date"
                    :class="{ 'border-danger': invalidExpenseField(`payments.${index}.date`) }"
                    :placeholder="t('views.expense.fields.date')"
                    @change="validateExpenseField(`payments.${index}.date`)"
                  />
                  <FormErrorMessages :messages="getExpenseFieldErrors(`payments.${index}.date`)" />
                </div>

                <div class="col-span-12 lg:col-span-4">
                  <FormLabel :class="{ 'text-danger': invalidExpenseField(`payments.${index}.cash_account_id`) }">
                    {{ t('views.expense_payment.fields.cash_account') }}
                  </FormLabel>
                  <FormSelectSearch
                    :id="`payments.${index}.cash_account_id`"
                    v-model="payment.cash_account_id"
                    v-model:search="paymentCashAccountSearch"
                    :options="paidImmediatelyCashAccountOptions"
                    :placeholder="t('components.dropdown.placeholder')"
                    :class="{ 'border-danger': invalidExpenseField(`payments.${index}.cash_account_id`) }"
                    @change="validateExpenseField(`payments.${index}.cash_account_id`)"
                    @search="loadPaidImmediatelyCashAccountDDL"
                    @clear="clearPaymentCashAccount(index)"
                  />
                  <FormErrorMessages :messages="getExpenseFieldErrors(`payments.${index}.cash_account_id`)" />
                </div>

                <div class="col-span-12 md:col-span-8 lg:col-span-3">
                  <FormLabel :class="{ 'text-danger': invalidExpenseField(`payments.${index}.amount`) }">
                    {{ t('views.expense_payment.fields.amount') }}
                  </FormLabel>
                  <div class="flex items-start gap-2">
                    <div class="flex-1 min-w-0">
                      <FormInputCurrency
                        :id="`payments.${index}.amount`"
                        v-model="payment.amount"
                        :allow-negative="false"
                        :class="{ 'border-danger': invalidExpenseField(`payments.${index}.amount`) }"
                        @change="validateExpenseField(`payments.${index}.amount`)"
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
                  <FormErrorMessages :messages="getExpenseFieldErrors(`payments.${index}.amount`)" />
                </div>
              </div>

              <div class="grid grid-cols-12 gap-4 gap-y-3">
                <div class="col-span-12">
                  <FormLabel :class="{ 'text-danger': invalidExpenseField(`payments.${index}.remarks`) }">
                    {{ t('views.expense.fields.remarks') }}
                  </FormLabel>
                  <FormTextarea
                    :id="`payments.${index}.remarks`"
                    v-model="payment.remarks"
                    rows="2"
                    :class="{ 'border-danger': invalidExpenseField(`payments.${index}.remarks`) }"
                    :placeholder="t('views.expense.fields.remarks')"
                    @change="validateExpenseField(`payments.${index}.remarks`)"
                  />
                  <FormErrorMessages :messages="getExpenseFieldErrors(`payments.${index}.remarks`)" />
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
            {{ t('views.expense.fields.images') }}
          </FormLabel>
          <ExpenseImagesField
            v-model="expenseForm.image_hashes"
            v-model:existing-images="uploadedImages"
            v-model:delete-image-ids="expenseForm.delete_image_ids"
          />
          <FormErrorMessages :messages="(expenseForm.errors as any).image_hashes" />
          <FormErrorMessages :messages="(expenseForm.errors as any).delete_image_ids" />
        </div>
      </template>

      <template #card-items-button>
        <div class="flex gap-4">
          <Button
            type="submit"
            href="#"
            variant="primary"
            class="w-28 shadow-md"
            :disabled="expenseForm.validating || expenseForm.hasErrors"
          >
            <Lucide v-if="expenseForm.validating" icon="Loader" class="animate-spin" />
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
