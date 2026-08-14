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
import ExpensePaymentService from '@/services/ExpensePaymentService';
import ExpenseService from '@/services/ExpenseService';
import CashAccountService from '@/services/CashAccountService';
import CacheService from '@/services/CacheService';
import Button from '@/components/Base/Button';
import Lucide from '@/components/Base/Lucide';
import { ErrorCode } from '@/types/enums/ErrorCode';
import type { DropDownOption } from '@/types/models/DropDownOption';
import type { AlertPlaceholderProps } from '@/components/AlertPlaceholder/AlertPlaceholder.vue';
import type { Expense } from '@/types/models/Expense';
import type { CashAccount } from '@/types/models/CashAccount';

const { t } = useI18n();
const router = useRouter();
const selectedUserLocationStore = useSelectedUserLocationStore();

const emits = defineEmits(['mode-state', 'loading-state', 'update-profile', 'show-alertplaceholder']);

const isUserLocationSelected = computed(() => selectedUserLocationStore.isUserLocationSelected);
const selectedUserLocation = computed(() => selectedUserLocationStore.selectedUserLocation);

const expensePaymentService = new ExpensePaymentService();
const expenseService = new ExpenseService();
const cashAccountService = new CashAccountService();
const cacheServices = new CacheService();

const cards = ref<Array<TwoColumnsLayoutCards>>([
  {
    title: 'views.expense_payment.field_groups.company_info',
    state: CardState.Expanded,
  },
  {
    title: 'views.expense_payment.field_groups.expense_payment_data',
    state: CardState.Expanded,
  },
  { title: '', state: CardState.Hidden, id: 'button' },
]);

const expensePaymentForm = expensePaymentService.useExpensePaymentCreateForm();

const expenseDDL = ref<Array<DropDownOption> | null>(null);
const expenseSearch = ref<string>('');
const expenseOptions = computed(() =>
  (expenseDDL.value ?? []).map((item) => ({
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

const selectedExpense = computed(() => {
  return (expenseDDL.value ?? []).find((item) => item.code === expensePaymentForm.expense_id);
});

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

  await Promise.all([loadExpenseDDL(), loadCashAccountDDL()]);
});

const setLocationData = () => {
  expensePaymentForm.setData({
    company_id: selectedUserLocation.value.company.id,
    branch_id: selectedUserLocation.value.branch.id,
  });
};

const loadFromCache = () => {
  const data = cacheServices.getLastEntity('EXPENSE_PAYMENT_CREATE') as Record<string, unknown>;

  if (!data) return;

  expensePaymentForm.setData(data);
};

const loadExpenseDDL = async (search = '') => {
  if (!selectedUserLocation.value) return;

  const result = await expenseService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    branch_id: selectedUserLocation.value.branch.id,
    search,
    expense_category_id: null,
    is_amount_payable_paid_off: false,
    include_id: expensePaymentForm.expense_id || undefined,
    refresh: false,
    limit: 20,
  });

  if (result.success && result.data) {
    expenseDDL.value = result.data.data.map((item: Expense) => ({
      code: item.id,
      name: [
        item.code,
        item.category?.name ?? null,
        `Sisa ${formatCurrency(Number(item.amount_payable_due ?? 0))}`,
      ]
        .filter(Boolean)
        .join(' - '),
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
    include_id: expensePaymentForm.cash_account_id || undefined,
    with_remaining_balance: null,
    refresh: false,
    limit: 20,
  });

  if (result.success && result.data) {
    cashAccountDDL.value = result.data.data.map((item: CashAccount) => ({
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
  expensePaymentForm.forgetError('code');

  if (expensePaymentForm.code === '_AUTO_') {
    expensePaymentForm.setData({ code: '' });
    return;
  }

  expensePaymentForm.setData({ code: '_AUTO_' });
};

const clearExpense = () => {
  expensePaymentForm.setData({ expense_id: '' });
  expensePaymentForm.forgetError('expense_id');
};

const clearCashAccount = () => {
  expensePaymentForm.setData({ cash_account_id: '' });
  expensePaymentForm.forgetError('cash_account_id');
};

const onSubmit = async () => {
  if (expensePaymentForm.hasErrors) {
    const firstErrorKey = Object.keys(expensePaymentForm.errors)[0];

    if (firstErrorKey) {
      scrollToError(firstErrorKey);
    }

    return;
  }

  emits('loading-state', true);

  await expensePaymentForm
    .submit()
    .then(() => {
      cacheServices.removeLastEntity('EXPENSE_PAYMENT_CREATE');
      resetForm();
      showAlertPlaceholder('hidden', '', null);
      emits('update-profile');
      router.push({ name: 'side-menu-expense-payment-list' });
    })
    .catch((error) => {
      showAlertPlaceholder('danger', '', convertErrorTypeToAlertListType(error));
    })
    .finally(() => {
      emits('loading-state', false);
    });
};

const resetForm = () => {
  expensePaymentForm.reset();
  expensePaymentForm.setErrors({});
  setLocationData();
  expenseSearch.value = '';
  cashAccountSearch.value = '';
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
  expensePaymentForm,
  debounce((newValue): void => {
    cacheServices.setLastEntity('EXPENSE_PAYMENT_CREATE', newValue.data());
  }, 500),
  { deep: true },
);
</script>

<template>
  <form id="expensePaymentForm" @submit.prevent="onSubmit">
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
              <FormInput type="hidden" v-model="expensePaymentForm.company_id" />
            </div>
            <div class="col-span-12 md:col-span-6">
              <FormLabel>
                {{ selectedUserLocation.branch.code }}
                <br />
                {{ selectedUserLocation.branch.name }}
              </FormLabel>
              <FormInput type="hidden" v-model="expensePaymentForm.branch_id" />
            </div>
          </div>
        </div>
      </template>

      <template #card-items-1>
        <div class="p-5">
          <div class="grid grid-cols-12 gap-4 gap-y-3">
            <div class="col-span-12">
              <FormLabel :class="{ 'text-danger': expensePaymentForm.invalid('expense_id') }">
                {{ t('views.expense_payment.fields.expense') }}
              </FormLabel>
              <FormSelectSearch
                v-model="expensePaymentForm.expense_id"
                v-model:search="expenseSearch"
                :options="expenseOptions"
                :placeholder="t('components.dropdown.placeholder')"
                :class="{ 'border-danger': expensePaymentForm.invalid('expense_id') }"
                @change="expensePaymentForm.validate('expense_id')"
                @search="loadExpenseDDL"
                @clear="clearExpense"
              />
              <FormErrorMessages :messages="expensePaymentForm.errors.expense_id" />
            </div>

            <div v-if="selectedExpense" class="col-span-12">
              <div class="rounded-md border border-slate-200/60 bg-slate-50 p-4 text-sm dark:border-darkmode-400 dark:bg-darkmode-600/40">
                <div class="text-slate-500">{{ t('views.expense.fields.amount_payable_due') }}</div>
                <div class="mt-1 font-medium text-slate-700 dark:text-slate-200">
                  {{ selectedExpense.name }}
                </div>
              </div>
            </div>

            <div class="col-span-12 lg:col-span-6">
              <FormLabel :class="{ 'text-danger': expensePaymentForm.invalid('code') }">
                {{ t('views.expense_payment.fields.code') }}
              </FormLabel>
              <FormInputCode
                v-model="expensePaymentForm.code"
                :class="{ 'border-danger': expensePaymentForm.invalid('code') }"
                :placeholder="t('views.expense_payment.fields.code')"
                @set-auto="setCode"
                @change="expensePaymentForm.validate('code')"
              />
              <FormErrorMessages :messages="expensePaymentForm.errors.code" />
            </div>

            <div class="col-span-12 lg:col-span-6">
              <FormLabel :class="{ 'text-danger': expensePaymentForm.invalid('date') }">
                {{ t('views.expense_payment.fields.date') }}
              </FormLabel>
              <FormInputDateTimeAuto
                v-model="expensePaymentForm.date"
                :class="{ 'border-danger': expensePaymentForm.invalid('date') }"
                :placeholder="t('views.expense_payment.fields.date')"
                @change="expensePaymentForm.validate('date')"
              />
              <FormErrorMessages :messages="expensePaymentForm.errors.date" />
            </div>

            <div class="col-span-12 lg:col-span-6">
              <FormLabel :class="{ 'text-danger': expensePaymentForm.invalid('cash_account_id') }">
                {{ t('views.expense_payment.fields.cash_account') }}
              </FormLabel>
              <FormSelectSearch
                v-model="expensePaymentForm.cash_account_id"
                v-model:search="cashAccountSearch"
                :options="cashAccountOptions"
                :placeholder="t('components.dropdown.placeholder')"
                :class="{ 'border-danger': expensePaymentForm.invalid('cash_account_id') }"
                @change="expensePaymentForm.validate('cash_account_id')"
                @search="loadCashAccountDDL"
                @clear="clearCashAccount"
              />
              <FormErrorMessages :messages="expensePaymentForm.errors.cash_account_id" />
            </div>

            <div class="col-span-12 lg:col-span-6">
              <FormLabel :class="{ 'text-danger': expensePaymentForm.invalid('amount') }">
                {{ t('views.expense_payment.fields.amount') }}
              </FormLabel>
              <FormInputCurrency
                v-model="expensePaymentForm.amount"
                :allow-negative="false"
                :class="{ 'border-danger': expensePaymentForm.invalid('amount') }"
                :placeholder="t('views.expense_payment.fields.amount')"
                @change="expensePaymentForm.validate('amount')"
              />
              <FormErrorMessages :messages="expensePaymentForm.errors.amount" />
            </div>

            <div class="col-span-12">
              <FormLabel :class="{ 'text-danger': expensePaymentForm.invalid('remarks') }">
                {{ t('views.expense_payment.fields.remarks') }}
              </FormLabel>
              <FormTextarea
                v-model="expensePaymentForm.remarks"
                rows="3"
                :class="{ 'border-danger': expensePaymentForm.invalid('remarks') }"
                :placeholder="t('views.expense_payment.fields.remarks')"
                @change="expensePaymentForm.validate('remarks')"
              />
              <FormErrorMessages :messages="expensePaymentForm.errors.remarks" />
            </div>
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
            :disabled="expensePaymentForm.validating || expensePaymentForm.hasErrors"
          >
            <Lucide v-if="expensePaymentForm.validating" icon="Loader" class="animate-spin" />
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
