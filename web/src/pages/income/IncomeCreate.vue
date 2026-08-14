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
import IncomeService from '@/services/IncomeService';
import IncomeCategoryService from '@/services/IncomeCategoryService';
import CashAccountService from '@/services/CashAccountService';
import CacheService from '@/services/CacheService';
import IncomeImagesField from '@/components/Income/IncomeImagesField.vue';
import Button from '@/components/Base/Button';
import Lucide from '@/components/Base/Lucide';
import { ErrorCode } from '@/types/enums/ErrorCode';
import type { DropDownOption } from '@/types/models/DropDownOption';
import type { AlertPlaceholderProps } from '@/components/AlertPlaceholder/AlertPlaceholder.vue';
import type { IncomeCategory } from '@/types/models/IncomeCategory';
import type { CashAccount } from '@/types/models/CashAccount';

type IncomePaymentFormItem = {
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

const incomeService = new IncomeService();
const incomeCategoryService = new IncomeCategoryService();
const cashAccountService = new CashAccountService();
const cacheServices = new CacheService();

const cards = ref<Array<TwoColumnsLayoutCards>>([
  {
    title: 'views.income.field_groups.company_info',
    state: CardState.Expanded,
  },
  {
    title: 'views.income.field_groups.income_data',
    state: CardState.Expanded,
  },
  {
    title: 'views.income.field_groups.payments',
    state: CardState.Expanded,
  },
  {
    title: 'views.income.field_groups.images',
    state: CardState.Expanded,
  },
  { title: '', state: CardState.Hidden, id: 'button' },
]);

const incomeForm = incomeService.useIncomeCreateForm();

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
  Number(incomeForm.amount_paid_immediately ?? 0) + Number(incomeForm.amount_receivable ?? 0),
);
const incomePaymentsForm = computed<IncomePaymentFormItem[]>(
  () => incomeForm.payments as IncomePaymentFormItem[],
);
const paymentCashAccountSearch = ref<string>('');
const invalidIncomeField = (field: string) => incomeForm.invalid(field as any);
const validateIncomeField = (field: string) => incomeForm.validate(field as any);
const getIncomeFieldErrors = (field: string): string | undefined => {
  const errors = (incomeForm.errors as Record<string, string | string[] | undefined>)[field];

  if (Array.isArray(errors)) {
    return errors.join(' ');
  }

  return errors;
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

  await Promise.all([loadCategoryDDL(), loadPaidImmediatelyCashAccountDDL()]);
});

const setLocationData = () => {
  incomeForm.setData({
    company_id: selectedUserLocation.value.company.id,
    branch_id: selectedUserLocation.value.branch.id,
  });
};

const loadFromCache = () => {
  const data = cacheServices.getLastEntity('INCOME_CREATE') as Record<string, unknown>;

  if (!data) return;

  incomeForm.setData(data);
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
    include_id: incomeForm.income_category_id || undefined,
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
    include_id: incomeForm.paid_immediately_cash_account_id || undefined,
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
  incomeForm.forgetError('code');

  if (incomeForm.code === '_AUTO_') {
    incomeForm.setData({ code: '' });
    return;
  }

  incomeForm.setData({ code: '_AUTO_' });
};

const clearCategory = () => {
  incomeForm.setData({ income_category_id: '' });
  incomeForm.forgetError('income_category_id');
};

const clearPaidImmediatelyCashAccount = () => {
  incomeForm.setData({ paid_immediately_cash_account_id: '' });
  incomeForm.forgetError('paid_immediately_cash_account_id');
};

const clearAmountTotalError = () => {
  const { amount_total, ...restErrors } = incomeForm.errors as Record<string, string | string[]>;

  if (amount_total) {
    incomeForm.setErrors(restErrors);
  }
};

const handleAmountChange = (field: 'amount_paid_immediately' | 'amount_receivable') => {
  clearAmountTotalError();
  incomeForm.validate(field);
};

const setPaymentCode = (index: number) => {
  incomeForm.forgetError(`payments.${index}.code` as any);
  incomePaymentsForm.value[index].code = incomePaymentsForm.value[index].code === '_AUTO_' ? '' : '_AUTO_';
};

const clearPaymentCashAccount = (index: number) => {
  const payment = incomePaymentsForm.value[index];
  if (!payment) return;

  payment.cash_account_id = '';
  validateIncomeField(`payments.${index}.cash_account_id`);
};

const addPayment = () => {
  incomePaymentsForm.value.push({
    code: '_AUTO_',
    date: '_AUTO_',
    cash_account_id: '',
    amount: 0,
    remarks: '',
  });
};

const removePayment = (index: number) => {
  incomePaymentsForm.value.splice(index, 1);

  Object.keys(incomeForm.errors).forEach((key) => {
    if (key === 'payments' || key.startsWith('payments.')) {
      incomeForm.forgetError(key as any);
    }
  });
};

const onSubmit = async () => {
  if (incomeForm.hasErrors) {
    const firstErrorKey = Object.keys(incomeForm.errors)[0];

    if (firstErrorKey) {
      scrollToError(firstErrorKey);
    }

    return;
  }

  emits('loading-state', true);

  await incomeForm
    .submit()
    .then(() => {
      cacheServices.removeLastEntity('INCOME_CREATE');
      resetForm();
      showAlertPlaceholder('hidden', '', null);
      emits('update-profile');
      router.push({ name: 'side-menu-income-list' });
    })
    .catch((error) => {
      showAlertPlaceholder('danger', '', convertErrorTypeToAlertListType(error));
    })
    .finally(() => {
      emits('loading-state', false);
    });
};

const resetForm = () => {
  incomeForm.reset();
  incomeForm.setErrors({});
  setLocationData();
  categorySearch.value = '';
  paidImmediatelyCashAccountSearch.value = '';
  paymentCashAccountSearch.value = '';
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
  incomeForm,
  debounce((newValue): void => {
    cacheServices.setLastEntity('INCOME_CREATE', newValue.data());
  }, 500),
  { deep: true },
);
</script>

<template>
  <form id="incomeForm" @submit.prevent="onSubmit">
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
              <FormInputCurrency type="hidden" v-model="incomeForm.company_id" />
            </div>
            <div class="col-span-12 md:col-span-6">
              <FormLabel>
                {{ selectedUserLocation.branch.code }}
                <br />
                {{ selectedUserLocation.branch.name }}
              </FormLabel>
              <FormInput type="hidden" v-model="incomeForm.branch_id" />
            </div>
          </div>
        </div>
      </template>

      <template #card-items-1>
        <div class="p-5">
          <div class="grid grid-cols-12 gap-4 gap-y-3">
            <div class="col-span-12 lg:col-span-3">
              <FormLabel :class="{ 'text-danger': incomeForm.invalid('code') }">
                {{ t('views.income.fields.code') }}
              </FormLabel>
              <FormInputCode
                v-model="incomeForm.code"
                :class="{ 'border-danger': incomeForm.invalid('code') }"
                :placeholder="t('views.income.fields.code')"
                @set-auto="setCode"
                @change="incomeForm.validate('code')"
              />
              <FormErrorMessages :messages="incomeForm.errors.code" />
            </div>

            <div class="col-span-12 lg:col-span-3">
              <FormLabel :class="{ 'text-danger': incomeForm.invalid('date') }">
                {{ t('views.income.fields.date') }}
              </FormLabel>
              <FormInputDateTimeAuto
                v-model="incomeForm.date"
                :class="{ 'border-danger': incomeForm.invalid('date') }"
                :placeholder="t('views.income.fields.date')"
                @change="incomeForm.validate('date')"
              />
              <FormErrorMessages :messages="incomeForm.errors.date" />
            </div>

            <div class="col-span-12 lg:col-span-6">
              <FormLabel :class="{ 'text-danger': incomeForm.invalid('income_category_id') }">
                {{ t('views.income.fields.category') }}
              </FormLabel>
              <FormSelectSearch
                v-model="incomeForm.income_category_id"
                v-model:search="categorySearch"
                :options="categoryOptions"
                :placeholder="t('components.dropdown.placeholder')"
                :class="{ 'border-danger': incomeForm.invalid('income_category_id') }"
                @change="incomeForm.validate('income_category_id')"
                @search="loadCategoryDDL"
                @clear="clearCategory"
              />
              <FormErrorMessages :messages="incomeForm.errors.income_category_id" />
            </div>

            <div class="col-span-12 lg:col-span-4">
              <FormLabel :class="{ 'text-danger': incomeForm.invalid('paid_immediately_cash_account_id') }">
                {{ t('views.income.fields.paid_immediately_cash_account') }}
              </FormLabel>
              <FormSelectSearch
                v-model="incomeForm.paid_immediately_cash_account_id"
                v-model:search="paidImmediatelyCashAccountSearch"
                :options="paidImmediatelyCashAccountOptions"
                :placeholder="t('components.dropdown.placeholder')"
                :class="{ 'border-danger': incomeForm.invalid('paid_immediately_cash_account_id') }"
                @change="incomeForm.validate('paid_immediately_cash_account_id')"
                @search="loadPaidImmediatelyCashAccountDDL"
                @clear="clearPaidImmediatelyCashAccount"
              />
              <FormErrorMessages :messages="incomeForm.errors.paid_immediately_cash_account_id" />
            </div>

            <div class="col-span-12 lg:col-span-2">
              <FormLabel :class="{ 'text-danger': incomeForm.invalid('amount_paid_immediately') }">
                {{ t('views.income.fields.amount_paid_immediately') }}
              </FormLabel>
              <FormInputCurrency
                v-model="incomeForm.amount_paid_immediately"
                :allow-negative="false"
                :class="{ 'border-danger': incomeForm.invalid('amount_paid_immediately') }"
                :placeholder="t('views.income.fields.amount_paid_immediately')"
                @change="handleAmountChange('amount_paid_immediately')"
              />
              <FormErrorMessages :messages="incomeForm.errors.amount_paid_immediately" />
            </div>

            <div class="col-span-12 lg:col-span-2">
              <FormLabel :class="{ 'text-danger': incomeForm.invalid('amount_receivable') }">
                {{ t('views.income.fields.amount_receivable') }}
              </FormLabel>
              <FormInputCurrency
                v-model="incomeForm.amount_receivable"
                :allow-negative="false"
                :class="{ 'border-danger': incomeForm.invalid('amount_receivable') }"
                :placeholder="t('views.income.fields.amount_receivable')"
                @change="handleAmountChange('amount_receivable')"
              />
              <FormErrorMessages :messages="incomeForm.errors.amount_receivable" />
            </div>

            <div class="col-span-12 lg:col-span-2">
              <FormLabel :class="{ 'text-danger': incomeForm.invalid('due_days') }">
                {{ t('views.income.fields.due_days') }}
              </FormLabel>
              <FormInputCurrency
                id="due_days"
                v-model="incomeForm.due_days"
                :allow-negative="false"
                :class="{ 'border-danger': incomeForm.invalid('due_days') }"
                :placeholder="t('views.income.fields.due_days')"
                @change="incomeForm.validate('due_days')"
              />
              <FormErrorMessages :messages="incomeForm.errors.due_days" />
            </div>

            <div class="col-span-12 lg:col-span-2">
              <FormLabel>
                {{ t('views.income.fields.amount_total') }}
              </FormLabel>
              <FormInputCurrency
                id="amount_total"
                :model-value="amountTotalPreview"
                readonly
                :allow-negative="false"
              />
            </div>

            <div class="col-span-12">
              <FormLabel :class="{ 'text-danger': incomeForm.invalid('remarks') }">
                {{ t('views.income.fields.remarks') }}
              </FormLabel>
              <FormTextarea
                v-model="incomeForm.remarks"
                rows="3"
                :class="{ 'border-danger': incomeForm.invalid('remarks') }"
                :placeholder="t('views.income.fields.remarks')"
                @change="incomeForm.validate('remarks')"
              />
              <FormErrorMessages :messages="incomeForm.errors.remarks" />
            </div>
          </div>
        </div>
      </template>

      <template #card-items-2>
        <div class="p-5 space-y-4">
          <FormErrorMessages :messages="(incomeForm.errors as any).payments" />

          <div v-if="incomePaymentsForm.length === 0" class="text-right text-slate-500 text-sm">
            {{ t('components.data-list.data_not_found') }}
          </div>

          <div v-else class="space-y-4">
            <div v-for="(payment, index) in incomePaymentsForm" :key="`income-payment-${index}`" class="space-y-3">
              <div class="grid grid-cols-12 gap-4 gap-y-3">
                <div class="col-span-12 md:col-span-4 lg:col-span-2">
                  <FormLabel :class="{ 'text-danger': invalidIncomeField(`payments.${index}.code`) }">
                    {{ t('views.income.fields.code') }}
                  </FormLabel>
                  <FormInputCode
                    :id="`payments.${index}.code`"
                    v-model="payment.code"
                    :class="{ 'border-danger': invalidIncomeField(`payments.${index}.code`) }"
                    :placeholder="t('views.income.fields.code')"
                    @set-auto="setPaymentCode(index)"
                    @change="validateIncomeField(`payments.${index}.code`)"
                  />
                  <FormErrorMessages :messages="getIncomeFieldErrors(`payments.${index}.code`)" />
                </div>

                <div class="col-span-12 md:col-span-8 lg:col-span-3">
                  <FormLabel :class="{ 'text-danger': invalidIncomeField(`payments.${index}.date`) }">
                    {{ t('views.income.fields.date') }}
                  </FormLabel>
                  <FormInputDateTimeAuto
                    :id="`payments.${index}.date`"
                    v-model="payment.date"
                    :class="{ 'border-danger': invalidIncomeField(`payments.${index}.date`) }"
                    :placeholder="t('views.income.fields.date')"
                    @change="validateIncomeField(`payments.${index}.date`)"
                  />
                  <FormErrorMessages :messages="getIncomeFieldErrors(`payments.${index}.date`)" />
                </div>

                <div class="col-span-12 lg:col-span-4">
                  <FormLabel :class="{ 'text-danger': invalidIncomeField(`payments.${index}.cash_account_id`) }">
                    {{ t('views.income_payment.fields.cash_account') }}
                  </FormLabel>
                  <FormSelectSearch
                    :id="`payments.${index}.cash_account_id`"
                    v-model="payment.cash_account_id"
                    v-model:search="paymentCashAccountSearch"
                    :options="paidImmediatelyCashAccountOptions"
                    :placeholder="t('components.dropdown.placeholder')"
                    :class="{ 'border-danger': invalidIncomeField(`payments.${index}.cash_account_id`) }"
                    @change="validateIncomeField(`payments.${index}.cash_account_id`)"
                    @search="loadPaidImmediatelyCashAccountDDL"
                    @clear="clearPaymentCashAccount(index)"
                  />
                  <FormErrorMessages :messages="getIncomeFieldErrors(`payments.${index}.cash_account_id`)" />
                </div>

                <div class="col-span-12 md:col-span-8 lg:col-span-3">
                  <FormLabel :class="{ 'text-danger': invalidIncomeField(`payments.${index}.amount`) }">
                    {{ t('views.income_payment.fields.amount') }}
                  </FormLabel>
                  <div class="flex items-start gap-2">
                    <div class="flex-1 min-w-0">
                      <FormInputCurrency
                        :id="`payments.${index}.amount`"
                        v-model="payment.amount"
                        :allow-negative="false"
                        :class="{ 'border-danger': invalidIncomeField(`payments.${index}.amount`) }"
                        @change="validateIncomeField(`payments.${index}.amount`)"
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
                  <FormErrorMessages :messages="getIncomeFieldErrors(`payments.${index}.amount`)" />
                </div>
              </div>

              <div class="grid grid-cols-12 gap-4 gap-y-3">
                <div class="col-span-12">
                  <FormLabel :class="{ 'text-danger': invalidIncomeField(`payments.${index}.remarks`) }">
                    {{ t('views.income.fields.remarks') }}
                  </FormLabel>
                  <FormTextarea
                    :id="`payments.${index}.remarks`"
                    v-model="payment.remarks"
                    rows="2"
                    :class="{ 'border-danger': invalidIncomeField(`payments.${index}.remarks`) }"
                    :placeholder="t('views.income.fields.remarks')"
                    @change="validateIncomeField(`payments.${index}.remarks`)"
                  />
                  <FormErrorMessages :messages="getIncomeFieldErrors(`payments.${index}.remarks`)" />
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
            {{ t('views.income.fields.images') }}
          </FormLabel>
          <IncomeImagesField v-model="incomeForm.image_hashes" />
          <FormErrorMessages :messages="(incomeForm.errors as any).image_hashes" />
        </div>
      </template>

      <template #card-items-button>
        <div class="flex gap-4">
          <Button
            type="submit"
            href="#"
            variant="primary"
            class="w-28 shadow-md"
            :disabled="incomeForm.validating || incomeForm.hasErrors"
          >
            <Lucide v-if="incomeForm.validating" icon="Loader" class="animate-spin" />
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
