<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRoute, useRouter } from 'vue-router';
import { debounce } from 'lodash';
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
import InvestorService from '@/services/InvestorService';
import CashAccountService from '@/services/CashAccountService';
import CapitalTransactionService from '@/services/CapitalTransactionService';
import CacheService from '@/services/CacheService';
import Button from '@/components/Base/Button';
import Lucide from '@/components/Base/Lucide';
import { ErrorCode } from '@/types/enums/ErrorCode';
import { type DropDownOption } from '@/types/models/DropDownOption';
import { type AlertPlaceholderProps } from '@/components/AlertPlaceholder/AlertPlaceholder.vue';
import { convertErrorTypeToAlertListType } from '@/utils/helper';
import { type ServiceResponse } from '@/types/services/ServiceResponse';
import { type CapitalTransaction } from '@/types/models/CapitalTransaction';

const { t } = useI18n();
const router = useRouter();
const route = useRoute();
const selectedUserLocationStore = useSelectedUserLocationStore();

const emits = defineEmits(['mode-state', 'loading-state', 'update-profile', 'show-alertplaceholder']);

const isUserLocationSelected = computed(() => selectedUserLocationStore.isUserLocationSelected);
const selectedUserLocation = computed(() => selectedUserLocationStore.selectedUserLocation);

const capitalTransactionService = new CapitalTransactionService();
const investorService = new InvestorService();
const cashAccountService = new CashAccountService();
const cacheServices = new CacheService();

const cards = ref<Array<TwoColumnsLayoutCards>>([
  {
    title: 'views.capital_transaction.field_groups.company_info',
    state: CardState.Expanded,
  },
  {
    title: 'views.capital_transaction.field_groups.capital_transaction_data',
    state: CardState.Expanded,
  },
  { title: '', state: CardState.Hidden, id: 'button' },
]);

const capitalTransactionForm = capitalTransactionService.useCapitalTransactionEditForm(route.params.ulid as string);
const capitalTransactionData = ref<CapitalTransaction | null>(null);

const investorDDL = ref<Array<DropDownOption> | null>(null);
const investorSearch = ref<string>('');
const investorOptions = computed(() =>
  (investorDDL.value ?? []).map((item) => ({
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

const typeDDL = ref<Array<DropDownOption> | null>(null);
const typeOptions = computed(() =>
  (typeDDL.value ?? []).map((item) => ({
    value: item.code,
    label: t(item.name),
  })),
);

onMounted(async () => {
  emits('mode-state', ViewMode.FORM_EDIT);

  if (!isUserLocationSelected.value) {
    router.push({
      name: 'side-menu-error-code',
      params: { code: ErrorCode.USERLOCATION_REQUIRED },
    });
    return;
  }

  await Promise.all([loadData(route.params.ulid as string), loadTypeDDL()]);
  await Promise.all([loadInvestorDDL(), loadCashAccountDDL()]);
});

const loadData = async (ulid: string) => {
  emits('loading-state', true);

  const result: ServiceResponse<CapitalTransaction | null> = await capitalTransactionService.read(ulid);

  if (result.success && result.data) {
    capitalTransactionData.value = result.data;

    capitalTransactionForm.setData({
      company_id: result.data.company.id,
      branch_id: result.data.branch.id,
      code: result.data.code,
      date: result.data.date,
      investor_id: result.data.investor.id,
      cash_account_id: result.data.cash_account.id,
      type: result.data.type,
      amount: result.data.amount,
      remarks: result.data.remarks ?? '',
    });
  } else {
    router.push({ name: 'side-menu-finance-capital-transaction-list' });
  }

  emits('loading-state', false);
};

const loadInvestorDDL = async (search = '') => {
  if (!selectedUserLocation.value) return;

  const result = await investorService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    search,
    include_id: capitalTransactionData.value?.investor?.id,
    refresh: false,
    limit: 20,
  });

  if (result.success && result.data) {
    investorDDL.value = result.data.data.map((item) => ({
      code: item.id,
      name: item.name,
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
    include_id: capitalTransactionData.value?.cash_account?.id,
    refresh: false,
    limit: 20,
  });

  if (result.success && result.data) {
    cashAccountDDL.value = result.data.data.map((item) => ({
      code: item.id,
      name: item.name,
    }));
  }
};

const loadTypeDDL = async () => {
  const result = await capitalTransactionService.getTypes();

  if (result) {
    typeDDL.value = result;
  }
};

const handleExpandCard = (index: number) => {
  if (cards.value[index].state === CardState.Collapsed) {
    cards.value[index].state = CardState.Expanded;
  } else if (cards.value[index].state === CardState.Expanded) {
    cards.value[index].state = CardState.Collapsed;
  }
};

const scrollToError = (id: string) => {
  const el = document.getElementById(id);

  if (!el) return;

  el.scrollIntoView({ behavior: 'smooth', block: 'center' });
};

const onSubmit = async () => {
  if (capitalTransactionForm.hasErrors) {
    const firstErrorKey = Object.keys(capitalTransactionForm.errors)[0];

    if (firstErrorKey) {
      scrollToError(firstErrorKey);
    }

    return;
  }

  emits('loading-state', true);

  await capitalTransactionForm
    .submit()
    .then(() => {
      emits('update-profile');
      showAlertPlaceholder('hidden', '', null);
      router.push({ name: 'side-menu-finance-capital-transaction-list' });
    })
    .catch((error) => {
      showAlertPlaceholder('danger', '', convertErrorTypeToAlertListType(error));
    })
    .finally(() => {
      emits('loading-state', false);
    });
};

const resetForm = async () => {
  capitalTransactionForm.reset();
  capitalTransactionForm.setErrors({});
  await loadData(route.params.ulid as string);
  await Promise.all([loadInvestorDDL(), loadCashAccountDDL()]);
};

const setCode = () => {
  capitalTransactionForm.forgetError('code');

  if (capitalTransactionForm.code === '_AUTO_') {
    capitalTransactionForm.setData({ code: '' });
    return;
  }

  capitalTransactionForm.setData({ code: '_AUTO_' });
};

const clearInvestor = () => {
  capitalTransactionForm.setData({ investor_id: '' });
  capitalTransactionForm.forgetError('investor_id');
};

const clearCashAccount = () => {
  capitalTransactionForm.setData({ cash_account_id: '' });
  capitalTransactionForm.forgetError('cash_account_id');
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
  capitalTransactionForm,
  debounce((newValue): void => {
    cacheServices.setLastEntity('CAPITAL_TRANSACTION_EDIT', newValue.data());
  }, 500),
  { deep: true },
);
</script>

<template>
  <form id="capitalTransactionForm" @submit.prevent="onSubmit">
    <TwoColumnsLayout :cards="cards" :using-side-tab="false" @handle-expand-card="handleExpandCard">
      <template #card-items-0>
        <div class="p-5">
          <FormLabel>
            {{ selectedUserLocation.company.code }}
            <br />
            {{ selectedUserLocation.company.name }}
          </FormLabel>
          <FormInput type="hidden" v-model="capitalTransactionForm.company_id" />

          <FormLabel class="mt-5">
            {{ selectedUserLocation.branch.code }}
            <br />
            {{ selectedUserLocation.branch.name }}
          </FormLabel>
          <FormInput type="hidden" v-model="capitalTransactionForm.branch_id" />
        </div>
      </template>
      <template #card-items-1>
        <div class="grid grid-cols-12 gap-4 p-5">
          <div class="col-span-12 lg:col-span-6">
            <FormLabel :class="{ 'text-danger': capitalTransactionForm.invalid('code') }">
              {{ t('views.capital_transaction.fields.code') }}
            </FormLabel>
            <FormInputCode
              v-model="capitalTransactionForm.code"
              :class="{ 'border-danger': capitalTransactionForm.invalid('code') }"
              :placeholder="t('views.capital_transaction.fields.code')"
              @set-auto="setCode"
              @change="capitalTransactionForm.validate('code')"
            />
            <FormErrorMessages :messages="capitalTransactionForm.errors.code" />
          </div>
          <div class="col-span-12 lg:col-span-6">
            <FormLabel :class="{ 'text-danger': capitalTransactionForm.invalid('date') }">
              {{ t('views.capital_transaction.fields.date') }}
            </FormLabel>
            <FormInputDateTimeAuto
              v-model="capitalTransactionForm.date"
              :class="{ 'border-danger': capitalTransactionForm.invalid('date') }"
              :placeholder="t('views.capital_transaction.fields.date')"
              @change="capitalTransactionForm.validate('date')"
            />
            <FormErrorMessages :messages="capitalTransactionForm.errors.date" />
          </div>
          <div class="col-span-12 lg:col-span-6">
            <FormLabel :class="{ 'text-danger': capitalTransactionForm.invalid('investor_id') }">
              {{ t('views.capital_transaction.fields.investor') }}
            </FormLabel>
            <FormSelectSearch
              v-model="capitalTransactionForm.investor_id"
              v-model:search="investorSearch"
              :options="investorOptions"
              :placeholder="t('components.dropdown.placeholder')"
              :class="{ 'border-danger': capitalTransactionForm.invalid('investor_id') }"
              @change="capitalTransactionForm.validate('investor_id')"
              @search="loadInvestorDDL"
              @clear="clearInvestor"
            />
            <FormErrorMessages :messages="capitalTransactionForm.errors.investor_id" />
          </div>
          <div class="col-span-12 lg:col-span-6">
            <FormLabel :class="{ 'text-danger': capitalTransactionForm.invalid('cash_account_id') }">
              {{ t('views.capital_transaction.fields.cash_account') }}
            </FormLabel>
            <FormSelectSearch
              v-model="capitalTransactionForm.cash_account_id"
              v-model:search="cashAccountSearch"
              :options="cashAccountOptions"
              :placeholder="t('components.dropdown.placeholder')"
              :class="{ 'border-danger': capitalTransactionForm.invalid('cash_account_id') }"
              @change="capitalTransactionForm.validate('cash_account_id')"
              @search="loadCashAccountDDL"
              @clear="clearCashAccount"
            />
            <FormErrorMessages :messages="capitalTransactionForm.errors.cash_account_id" />
          </div>
          <div class="col-span-12 lg:col-span-6">
            <FormLabel :class="{ 'text-danger': capitalTransactionForm.invalid('type') }">
              {{ t('views.capital_transaction.fields.type') }}
            </FormLabel>
            <FormSelectSearch
              v-model="capitalTransactionForm.type"
              :options="typeOptions"
              :placeholder="t('components.dropdown.placeholder')"
              :class="{ 'border-danger': capitalTransactionForm.invalid('type') }"
              @change="capitalTransactionForm.validate('type')"
            />
            <FormErrorMessages :messages="capitalTransactionForm.errors.type" />
          </div>
          <div class="col-span-12 lg:col-span-6">
            <FormLabel :class="{ 'text-danger': capitalTransactionForm.invalid('amount') }">
              {{ t('views.capital_transaction.fields.amount') }}
            </FormLabel>
            <FormInputCurrency
              v-model="capitalTransactionForm.amount"
              :allow-negative="false"
              :class="{ 'border-danger': capitalTransactionForm.invalid('amount') }"
              :placeholder="t('views.capital_transaction.fields.amount')"
              @change="capitalTransactionForm.validate('amount')"
            />
            <FormErrorMessages :messages="capitalTransactionForm.errors.amount" />
          </div>
          <div class="col-span-12">
            <FormLabel :class="{ 'text-danger': capitalTransactionForm.invalid('remarks') }">
              {{ t('views.capital_transaction.fields.remarks') }}
            </FormLabel>
            <FormTextarea
              v-model="capitalTransactionForm.remarks"
              rows="3"
              :class="{ 'border-danger': capitalTransactionForm.invalid('remarks') }"
              :placeholder="t('views.capital_transaction.fields.remarks')"
              @change="capitalTransactionForm.validate('remarks')"
            />
            <FormErrorMessages :messages="capitalTransactionForm.errors.remarks" />
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
            :disabled="capitalTransactionForm.validating || capitalTransactionForm.hasErrors"
          >
            <Lucide v-if="capitalTransactionForm.validating" icon="Loader" class="animate-spin" />
            <template v-else>
              {{ t('components.buttons.submit') }}
            </template>
          </Button>
          <Button
            type="button"
            href="#"
            variant="soft-secondary"
            class="w-28 shadow-md"
            @click="resetForm"
          >
            {{ t('components.buttons.reset') }}
          </Button>
        </div>
      </template>
    </TwoColumnsLayout>
  </form>
</template>
