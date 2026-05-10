<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
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
import CapitalOpeningService from '@/services/CapitalOpeningService';
import CacheService from '@/services/CacheService';
import Button from '@/components/Base/Button';
import Lucide from '@/components/Base/Lucide';
import { ErrorCode } from '@/types/enums/ErrorCode';
import { type DropDownOption } from '@/types/models/DropDownOption';
import { type AlertPlaceholderProps } from '@/components/AlertPlaceholder/AlertPlaceholder.vue';
import { convertErrorTypeToAlertListType } from '@/utils/helper';

const { t } = useI18n();
const router = useRouter();
const selectedUserLocationStore = useSelectedUserLocationStore();

const emits = defineEmits(['mode-state', 'loading-state', 'update-profile', 'show-alertplaceholder']);

const isUserLocationSelected = computed(() => selectedUserLocationStore.isUserLocationSelected);
const selectedUserLocation = computed(() => selectedUserLocationStore.selectedUserLocation);

const capitalOpeningService = new CapitalOpeningService();
const investorService = new InvestorService();
const cashAccountService = new CashAccountService();
const cacheServices = new CacheService();

const cards = ref<Array<TwoColumnsLayoutCards>>([
  {
    title: 'views.capital_opening.field_groups.company_info',
    state: CardState.Expanded,
  },
  {
    title: 'views.capital_opening.field_groups.capital_opening_data',
    state: CardState.Expanded,
  },
  { title: '', state: CardState.Hidden, id: 'button' },
]);

const capitalOpeningForm = capitalOpeningService.useCapitalOpeningCreateForm();

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

onMounted(async () => {
  emits('mode-state', ViewMode.FORM_CREATE);

  if (!isUserLocationSelected.value) {
    router.push({
      name: 'side-menu-error-code',
      params: { code: ErrorCode.USERLOCATION_REQUIRED },
    });
    return;
  }

  await Promise.all([loadInvestorDDL(), loadCashAccountDDL()]);

  loadFromCache();
  setLocationData();
});

const setLocationData = () => {
  capitalOpeningForm.setData({
    company_id: selectedUserLocation.value.company.id,
    branch_id: selectedUserLocation.value.branch.id,
  });
};

const loadFromCache = () => {
  const data = cacheServices.getLastEntity('CAPITAL_OPENING_CREATE') as Record<string, unknown>;

  if (!data) return;

  capitalOpeningForm.setData(data);
};

const loadInvestorDDL = async (search = '') => {
  if (!selectedUserLocation.value) return;

  const result = await investorService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    search,
    include_id: undefined,
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
    include_id: undefined,
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
  if (capitalOpeningForm.hasErrors) {
    const firstErrorKey = Object.keys(capitalOpeningForm.errors)[0];

    if (firstErrorKey) {
      scrollToError(firstErrorKey);
    }

    return;
  }

  emits('loading-state', true);

  await capitalOpeningForm
    .submit()
    .then(() => {
      resetForm();
      emits('update-profile');
      showAlertPlaceholder('hidden', '', null);
      router.push({ name: 'side-menu-finance-capital-opening-list' });
    })
    .catch((error) => {
      showAlertPlaceholder('danger', '', convertErrorTypeToAlertListType(error));
    })
    .finally(() => {
      emits('loading-state', false);
    });
};

const resetForm = () => {
  capitalOpeningForm.reset();
  capitalOpeningForm.setErrors({});
  setLocationData();
  investorSearch.value = '';
  cashAccountSearch.value = '';
};

const setCode = () => {
  capitalOpeningForm.forgetError('code');

  if (capitalOpeningForm.code === '_AUTO_') {
    capitalOpeningForm.setData({ code: '' });
    return;
  }

  capitalOpeningForm.setData({ code: '_AUTO_' });
};

const clearInvestor = () => {
  capitalOpeningForm.setData({ investor_id: '' });
  capitalOpeningForm.forgetError('investor_id');
};

const clearCashAccount = () => {
  capitalOpeningForm.setData({ cash_account_id: '' });
  capitalOpeningForm.forgetError('cash_account_id');
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
  capitalOpeningForm,
  debounce((newValue): void => {
    cacheServices.setLastEntity('CAPITAL_OPENING_CREATE', newValue.data());
  }, 500),
  { deep: true },
);
</script>

<template>
  <form id="capitalOpeningForm" @submit.prevent="onSubmit">
    <TwoColumnsLayout :cards="cards" :using-side-tab="false" @handle-expand-card="handleExpandCard">
      <template #card-items-0>
        <div class="p-5">
          <FormLabel>
            {{ selectedUserLocation.company.code }}
            <br />
            {{ selectedUserLocation.company.name }}
          </FormLabel>
          <FormInput type="hidden" v-model="capitalOpeningForm.company_id" />

          <FormLabel class="mt-5">
            {{ selectedUserLocation.branch.code }}
            <br />
            {{ selectedUserLocation.branch.name }}
          </FormLabel>
          <FormInput type="hidden" v-model="capitalOpeningForm.branch_id" />
        </div>
      </template>
      <template #card-items-1>
        <div class="grid grid-cols-12 gap-4 p-5">
          <div class="col-span-12 lg:col-span-6">
            <FormLabel :class="{ 'text-danger': capitalOpeningForm.invalid('code') }">
              {{ t('views.capital_opening.fields.code') }}
            </FormLabel>
            <FormInputCode
              v-model="capitalOpeningForm.code"
              :class="{ 'border-danger': capitalOpeningForm.invalid('code') }"
              :placeholder="t('views.capital_opening.fields.code')"
              @set-auto="setCode"
              @change="capitalOpeningForm.validate('code')"
            />
            <FormErrorMessages :messages="capitalOpeningForm.errors.code" />
          </div>
          <div class="col-span-12 lg:col-span-6">
            <FormLabel :class="{ 'text-danger': capitalOpeningForm.invalid('date') }">
              {{ t('views.capital_opening.fields.date') }}
            </FormLabel>
            <FormInputDateTimeAuto
              v-model="capitalOpeningForm.date"
              :class="{ 'border-danger': capitalOpeningForm.invalid('date') }"
              :placeholder="t('views.capital_opening.fields.date')"
              @change="capitalOpeningForm.validate('date')"
            />
            <FormErrorMessages :messages="capitalOpeningForm.errors.date" />
          </div>
          <div class="col-span-12 lg:col-span-6">
            <FormLabel :class="{ 'text-danger': capitalOpeningForm.invalid('investor_id') }">
              {{ t('views.capital_opening.fields.investor') }}
            </FormLabel>
            <FormSelectSearch
              v-model="capitalOpeningForm.investor_id"
              v-model:search="investorSearch"
              :options="investorOptions"
              :placeholder="t('components.dropdown.placeholder')"
              :class="{ 'border-danger': capitalOpeningForm.invalid('investor_id') }"
              @change="capitalOpeningForm.validate('investor_id')"
              @search="loadInvestorDDL"
              @clear="clearInvestor"
            />
            <FormErrorMessages :messages="capitalOpeningForm.errors.investor_id" />
          </div>
          <div class="col-span-12 lg:col-span-6">
            <FormLabel :class="{ 'text-danger': capitalOpeningForm.invalid('cash_account_id') }">
              {{ t('views.capital_opening.fields.cash_account') }}
            </FormLabel>
            <FormSelectSearch
              v-model="capitalOpeningForm.cash_account_id"
              v-model:search="cashAccountSearch"
              :options="cashAccountOptions"
              :placeholder="t('components.dropdown.placeholder')"
              :class="{ 'border-danger': capitalOpeningForm.invalid('cash_account_id') }"
              @change="capitalOpeningForm.validate('cash_account_id')"
              @search="loadCashAccountDDL"
              @clear="clearCashAccount"
            />
            <FormErrorMessages :messages="capitalOpeningForm.errors.cash_account_id" />
          </div>
          <div class="col-span-12 lg:col-span-6">
            <FormLabel :class="{ 'text-danger': capitalOpeningForm.invalid('amount') }">
              {{ t('views.capital_opening.fields.amount') }}
            </FormLabel>
            <FormInputCurrency
              v-model="capitalOpeningForm.amount"
              :allow-negative="false"
              :class="{ 'border-danger': capitalOpeningForm.invalid('amount') }"
              :placeholder="t('views.capital_opening.fields.amount')"
              @change="capitalOpeningForm.validate('amount')"
            />
            <FormErrorMessages :messages="capitalOpeningForm.errors.amount" />
          </div>
          <div class="col-span-12">
            <FormLabel :class="{ 'text-danger': capitalOpeningForm.invalid('remarks') }">
              {{ t('views.capital_opening.fields.remarks') }}
            </FormLabel>
            <FormTextarea
              v-model="capitalOpeningForm.remarks"
              rows="3"
              :class="{ 'border-danger': capitalOpeningForm.invalid('remarks') }"
              :placeholder="t('views.capital_opening.fields.remarks')"
              @change="capitalOpeningForm.validate('remarks')"
            />
            <FormErrorMessages :messages="capitalOpeningForm.errors.remarks" />
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
            :disabled="capitalOpeningForm.validating || capitalOpeningForm.hasErrors"
          >
            <Lucide v-if="capitalOpeningForm.validating" icon="Loader" class="animate-spin" />
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
