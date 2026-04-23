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
import IncomePaymentService from '@/services/IncomePaymentService';
import IncomeService from '@/services/IncomeService';
import CashAccountService from '@/services/CashAccountService';
import CacheService from '@/services/CacheService';
import Button from '@/components/Base/Button';
import Lucide from '@/components/Base/Lucide';
import { ErrorCode } from '@/types/enums/ErrorCode';
import type { DropDownOption } from '@/types/models/DropDownOption';
import type { AlertPlaceholderProps } from '@/components/AlertPlaceholder/AlertPlaceholder.vue';
import type { Income } from '@/types/models/Income';
import type { CashAccount } from '@/types/models/CashAccount';

const { t } = useI18n();
const router = useRouter();
const selectedUserLocationStore = useSelectedUserLocationStore();

const emits = defineEmits(['mode-state', 'loading-state', 'update-profile', 'show-alertplaceholder']);

const isUserLocationSelected = computed(() => selectedUserLocationStore.isUserLocationSelected);
const selectedUserLocation = computed(() => selectedUserLocationStore.selectedUserLocation);

const incomePaymentService = new IncomePaymentService();
const incomeService = new IncomeService();
const cashAccountService = new CashAccountService();
const cacheServices = new CacheService();

const cards = ref<Array<TwoColumnsLayoutCards>>([
  {
    title: 'views.income_payment.field_groups.company_info',
    state: CardState.Expanded,
  },
  {
    title: 'views.income_payment.field_groups.income_payment_data',
    state: CardState.Expanded,
  },
  { title: '', state: CardState.Hidden, id: 'button' },
]);

const incomePaymentForm = incomePaymentService.useIncomePaymentCreateForm();

const incomeDDL = ref<Array<DropDownOption> | null>(null);
const incomeSearch = ref<string>('');
const incomeOptions = computed(() =>
  (incomeDDL.value ?? []).map((item) => ({
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

const selectedIncome = computed(() => {
  return (incomeDDL.value ?? []).find((item) => item.code === incomePaymentForm.income_id);
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

  await Promise.all([loadIncomeDDL(), loadCashAccountDDL()]);
});

const setLocationData = () => {
  incomePaymentForm.setData({
    company_id: selectedUserLocation.value.company.id,
    branch_id: selectedUserLocation.value.branch.id,
  });
};

const loadFromCache = () => {
  const data = cacheServices.getLastEntity('INCOME_PAYMENT_CREATE') as Record<string, unknown>;

  if (!data) return;

  incomePaymentForm.setData(data);
};

const loadIncomeDDL = async (search = '') => {
  if (!selectedUserLocation.value) return;

  const result = await incomeService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    branch_id: selectedUserLocation.value.branch.id,
    search,
    income_category_id: null,
    is_amount_receivable_paid_off: false,
    include_id: incomePaymentForm.income_id || undefined,
    refresh: false,
    limit: 20,
  });

  if (result.success && result.data) {
    incomeDDL.value = result.data.data.map((item: Income) => ({
      code: item.id,
      name: [
        item.code,
        item.category?.name ?? null,
        `Sisa ${formatCurrency(Number(item.amount_receivable_due ?? 0))}`,
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
    include_id: incomePaymentForm.cash_account_id || undefined,
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
  incomePaymentForm.forgetError('code');

  if (incomePaymentForm.code === '_AUTO_') {
    incomePaymentForm.setData({ code: '' });
    return;
  }

  incomePaymentForm.setData({ code: '_AUTO_' });
};

const clearIncome = () => {
  incomePaymentForm.setData({ income_id: '' });
  incomePaymentForm.forgetError('income_id');
};

const clearCashAccount = () => {
  incomePaymentForm.setData({ cash_account_id: '' });
  incomePaymentForm.forgetError('cash_account_id');
};

const onSubmit = async () => {
  if (incomePaymentForm.hasErrors) {
    const firstErrorKey = Object.keys(incomePaymentForm.errors)[0];

    if (firstErrorKey) {
      scrollToError(firstErrorKey);
    }

    return;
  }

  emits('loading-state', true);

  await incomePaymentForm
    .submit()
    .then(() => {
      cacheServices.removeLastEntity('INCOME_PAYMENT_CREATE');
      resetForm();
      showAlertPlaceholder('hidden', '', null);
      emits('update-profile');
      router.push({ name: 'side-menu-income-payment-list' });
    })
    .catch((error) => {
      showAlertPlaceholder('danger', '', convertErrorTypeToAlertListType(error));
    })
    .finally(() => {
      emits('loading-state', false);
    });
};

const resetForm = () => {
  incomePaymentForm.reset();
  incomePaymentForm.setErrors({});
  setLocationData();
  incomeSearch.value = '';
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
  incomePaymentForm,
  debounce((newValue): void => {
    cacheServices.setLastEntity('INCOME_PAYMENT_CREATE', newValue.data());
  }, 500),
  { deep: true },
);
</script>

<template>
  <form id="incomePaymentForm" @submit.prevent="onSubmit">
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
              <FormInput type="hidden" v-model="incomePaymentForm.company_id" />
            </div>
            <div class="col-span-12 md:col-span-6">
              <FormLabel>
                {{ selectedUserLocation.branch.code }}
                <br />
                {{ selectedUserLocation.branch.name }}
              </FormLabel>
              <FormInput type="hidden" v-model="incomePaymentForm.branch_id" />
            </div>
          </div>
        </div>
      </template>

      <template #card-items-1>
        <div class="p-5">
          <div class="grid grid-cols-12 gap-4 gap-y-3">
            <div class="col-span-12">
              <FormLabel :class="{ 'text-danger': incomePaymentForm.invalid('income_id') }">
                {{ t('views.income_payment.fields.income') }}
              </FormLabel>
              <FormSelectSearch
                v-model="incomePaymentForm.income_id"
                v-model:search="incomeSearch"
                :options="incomeOptions"
                :placeholder="t('components.dropdown.placeholder')"
                :class="{ 'border-danger': incomePaymentForm.invalid('income_id') }"
                @change="incomePaymentForm.validate('income_id')"
                @search="loadIncomeDDL"
                @clear="clearIncome"
              />
              <FormErrorMessages :messages="incomePaymentForm.errors.income_id" />
            </div>

            <div v-if="selectedIncome" class="col-span-12">
              <div class="rounded-md border border-slate-200/60 bg-slate-50 p-4 text-sm dark:border-darkmode-400 dark:bg-darkmode-600/40">
                <div class="text-slate-500">{{ t('views.income.fields.amount_receivable_due') }}</div>
                <div class="mt-1 font-medium text-slate-700 dark:text-slate-200">
                  {{ selectedIncome.name }}
                </div>
              </div>
            </div>

            <div class="col-span-12 lg:col-span-6">
              <FormLabel :class="{ 'text-danger': incomePaymentForm.invalid('code') }">
                {{ t('views.income_payment.fields.code') }}
              </FormLabel>
              <FormInputCode
                v-model="incomePaymentForm.code"
                :class="{ 'border-danger': incomePaymentForm.invalid('code') }"
                :placeholder="t('views.income_payment.fields.code')"
                @set-auto="setCode"
                @change="incomePaymentForm.validate('code')"
              />
              <FormErrorMessages :messages="incomePaymentForm.errors.code" />
            </div>

            <div class="col-span-12 lg:col-span-6">
              <FormLabel :class="{ 'text-danger': incomePaymentForm.invalid('date') }">
                {{ t('views.income_payment.fields.date') }}
              </FormLabel>
              <FormInputDateTimeAuto
                v-model="incomePaymentForm.date"
                :class="{ 'border-danger': incomePaymentForm.invalid('date') }"
                :placeholder="t('views.income_payment.fields.date')"
                @change="incomePaymentForm.validate('date')"
              />
              <FormErrorMessages :messages="incomePaymentForm.errors.date" />
            </div>

            <div class="col-span-12 lg:col-span-6">
              <FormLabel :class="{ 'text-danger': incomePaymentForm.invalid('cash_account_id') }">
                {{ t('views.income_payment.fields.cash_account') }}
              </FormLabel>
              <FormSelectSearch
                v-model="incomePaymentForm.cash_account_id"
                v-model:search="cashAccountSearch"
                :options="cashAccountOptions"
                :placeholder="t('components.dropdown.placeholder')"
                :class="{ 'border-danger': incomePaymentForm.invalid('cash_account_id') }"
                @change="incomePaymentForm.validate('cash_account_id')"
                @search="loadCashAccountDDL"
                @clear="clearCashAccount"
              />
              <FormErrorMessages :messages="incomePaymentForm.errors.cash_account_id" />
            </div>

            <div class="col-span-12 lg:col-span-6">
              <FormLabel :class="{ 'text-danger': incomePaymentForm.invalid('amount') }">
                {{ t('views.income_payment.fields.amount') }}
              </FormLabel>
              <FormInputCurrency
                v-model="incomePaymentForm.amount"
                :allow-negative="false"
                :class="{ 'border-danger': incomePaymentForm.invalid('amount') }"
                :placeholder="t('views.income_payment.fields.amount')"
                @change="incomePaymentForm.validate('amount')"
              />
              <FormErrorMessages :messages="incomePaymentForm.errors.amount" />
            </div>

            <div class="col-span-12">
              <FormLabel :class="{ 'text-danger': incomePaymentForm.invalid('remarks') }">
                {{ t('views.income_payment.fields.remarks') }}
              </FormLabel>
              <FormTextarea
                v-model="incomePaymentForm.remarks"
                rows="3"
                :class="{ 'border-danger': incomePaymentForm.invalid('remarks') }"
                :placeholder="t('views.income_payment.fields.remarks')"
                @change="incomePaymentForm.validate('remarks')"
              />
              <FormErrorMessages :messages="incomePaymentForm.errors.remarks" />
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
            :disabled="incomePaymentForm.validating || incomePaymentForm.hasErrors"
          >
            <Lucide v-if="incomePaymentForm.validating" icon="Loader" class="animate-spin" />
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
