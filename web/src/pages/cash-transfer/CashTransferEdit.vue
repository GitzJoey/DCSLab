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
import CashAccountService from '@/services/CashAccountService';
import CashTransferService from '@/services/CashTransferService';
import CacheService from '@/services/CacheService';
import Button from '@/components/Base/Button';
import Lucide from '@/components/Base/Lucide';
import { ErrorCode } from '@/types/enums/ErrorCode';
import { type DropDownOption } from '@/types/models/DropDownOption';
import { type AlertPlaceholderProps } from '@/components/AlertPlaceholder/AlertPlaceholder.vue';
import { convertErrorTypeToAlertListType } from '@/utils/helper';
import { type ServiceResponse } from '@/types/services/ServiceResponse';
import { type CashTransfer } from '@/types/models/CashTransfer';

const { t } = useI18n();
const router = useRouter();
const route = useRoute();
const selectedUserLocationStore = useSelectedUserLocationStore();

const emits = defineEmits(['mode-state', 'loading-state', 'update-profile', 'show-alertplaceholder']);

const isUserLocationSelected = computed(() => selectedUserLocationStore.isUserLocationSelected);
const selectedUserLocation = computed(() => selectedUserLocationStore.selectedUserLocation);

const cashTransferService = new CashTransferService();
const cashAccountService = new CashAccountService();
const cacheServices = new CacheService();

const cards = ref<Array<TwoColumnsLayoutCards>>([
  {
    title: 'views.cash_transfer.field_groups.company_info',
    state: CardState.Expanded,
  },
  {
    title: 'views.cash_transfer.field_groups.transfer_data',
    state: CardState.Expanded,
  },
  { title: '', state: CardState.Hidden, id: 'button' },
]);

const cashTransferForm = cashTransferService.useCashTransferEditForm(route.params.ulid as string);
const cashTransferData = ref<CashTransfer | null>(null);

const sourceCashAccountDDL = ref<Array<DropDownOption> | null>(null);
const sourceCashAccountSearch = ref<string>('');
const sourceCashAccountOptions = computed(() =>
  (sourceCashAccountDDL.value ?? []).map((item) => ({
    value: item.code,
    label: item.name,
  })),
);

const destinationCashAccountDDL = ref<Array<DropDownOption> | null>(null);
const destinationCashAccountSearch = ref<string>('');
const destinationCashAccountOptions = computed(() =>
  (destinationCashAccountDDL.value ?? []).map((item) => ({
    value: item.code,
    label: item.name,
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

  await loadData(route.params.ulid as string);
  await Promise.all([loadSourceCashAccountDDL(), loadDestinationCashAccountDDL()]);
});

const loadData = async (ulid: string) => {
  emits('loading-state', true);

  const result: ServiceResponse<CashTransfer | null> = await cashTransferService.read(ulid);

  if (result.success && result.data) {
    cashTransferData.value = result.data;

    cashTransferForm.setData({
      company_id: result.data.company.id,
      branch_id: result.data.branch.id,
      code: result.data.code,
      date: result.data.date,
      source_cash_account_id: result.data.source_cash_account.id,
      destination_cash_account_id: result.data.destination_cash_account.id,
      amount: result.data.amount,
      remarks: result.data.remarks ?? '',
    });
  } else {
    router.push({ name: 'side-menu-finance-cash-transfer-list' });
  }

  emits('loading-state', false);
};

const loadSourceCashAccountDDL = async (search = '') => {
  if (!selectedUserLocation.value) return;

  const result = await cashAccountService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    branch_id: selectedUserLocation.value.branch.id,
    search,
    include_id: cashTransferData.value?.source_cash_account?.id,
    refresh: false,
    limit: 20,
  });

  if (result.success && result.data) {
    sourceCashAccountDDL.value = result.data.data.map((item) => ({
      code: item.id,
      name: item.name,
    }));
  }
};

const loadDestinationCashAccountDDL = async (search = '') => {
  if (!selectedUserLocation.value) return;

  const result = await cashAccountService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    branch_id: selectedUserLocation.value.branch.id,
    search,
    include_id: cashTransferData.value?.destination_cash_account?.id,
    refresh: false,
    limit: 20,
  });

  if (result.success && result.data) {
    destinationCashAccountDDL.value = result.data.data.map((item) => ({
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
  if (cashTransferForm.hasErrors) {
    const firstErrorKey = Object.keys(cashTransferForm.errors)[0];

    if (firstErrorKey) {
      scrollToError(firstErrorKey);
    }

    return;
  }

  emits('loading-state', true);

  await cashTransferForm
    .submit()
    .then(() => {
      emits('update-profile');
      showAlertPlaceholder('hidden', '', null);
      router.push({ name: 'side-menu-finance-cash-transfer-list' });
    })
    .catch((error) => {
      showAlertPlaceholder('danger', '', convertErrorTypeToAlertListType(error));
    })
    .finally(() => {
      emits('loading-state', false);
    });
};

const resetForm = async () => {
  cashTransferForm.reset();
  cashTransferForm.setErrors({});
  await loadData(route.params.ulid as string);
  await Promise.all([loadSourceCashAccountDDL(), loadDestinationCashAccountDDL()]);
};

const setCode = () => {
  cashTransferForm.forgetError('code');

  if (cashTransferForm.code === '_AUTO_') {
    cashTransferForm.setData({ code: '' });
    return;
  }

  cashTransferForm.setData({ code: '_AUTO_' });
};

const clearSourceCashAccount = () => {
  cashTransferForm.setData({ source_cash_account_id: '' });
  cashTransferForm.forgetError('source_cash_account_id');
};

const clearDestinationCashAccount = () => {
  cashTransferForm.setData({ destination_cash_account_id: '' });
  cashTransferForm.forgetError('destination_cash_account_id');
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
  cashTransferForm,
  debounce((newValue): void => {
    cacheServices.setLastEntity('CASH_TRANSFER_EDIT', newValue.data());
  }, 500),
  { deep: true },
);
</script>

<template>
  <form id="cashTransferForm" @submit.prevent="onSubmit">
    <TwoColumnsLayout :cards="cards" :using-side-tab="false" @handle-expand-card="handleExpandCard">
      <template #card-items-0>
        <div class="p-5">
          <FormLabel>
            {{ selectedUserLocation.company.code }}
            <br />
            {{ selectedUserLocation.company.name }}
          </FormLabel>
          <FormInput type="hidden" v-model="cashTransferForm.company_id" />

          <FormLabel class="mt-5">
            {{ selectedUserLocation.branch.code }}
            <br />
            {{ selectedUserLocation.branch.name }}
          </FormLabel>
          <FormInput type="hidden" v-model="cashTransferForm.branch_id" />
        </div>
      </template>
      <template #card-items-1>
        <div class="grid grid-cols-12 gap-4 p-5">
          <div class="col-span-12 lg:col-span-6">
            <FormLabel :class="{ 'text-danger': cashTransferForm.invalid('code') }">
              {{ t('views.cash_transfer.fields.code') }}
            </FormLabel>
            <FormInputCode
              v-model="cashTransferForm.code"
              :class="{ 'border-danger': cashTransferForm.invalid('code') }"
              :placeholder="t('views.cash_transfer.fields.code')"
              @set-auto="setCode"
              @change="cashTransferForm.validate('code')"
            />
            <FormErrorMessages :messages="cashTransferForm.errors.code" />
          </div>
          <div class="col-span-12 lg:col-span-6">
            <FormLabel :class="{ 'text-danger': cashTransferForm.invalid('date') }">
              {{ t('views.cash_transfer.fields.date') }}
            </FormLabel>
            <FormInputDateTimeAuto
              v-model="cashTransferForm.date"
              :class="{ 'border-danger': cashTransferForm.invalid('date') }"
              :placeholder="t('views.cash_transfer.fields.date')"
              @change="cashTransferForm.validate('date')"
            />
            <FormErrorMessages :messages="cashTransferForm.errors.date" />
          </div>
          <div class="col-span-12 lg:col-span-6">
            <FormLabel :class="{ 'text-danger': cashTransferForm.invalid('source_cash_account_id') }">
              {{ t('views.cash_transfer.fields.source_cash_account') }}
            </FormLabel>
            <FormSelectSearch
              v-model="cashTransferForm.source_cash_account_id"
              v-model:search="sourceCashAccountSearch"
              :options="sourceCashAccountOptions"
              :placeholder="t('components.dropdown.placeholder')"
              :class="{ 'border-danger': cashTransferForm.invalid('source_cash_account_id') }"
              @change="cashTransferForm.validate('source_cash_account_id')"
              @search="loadSourceCashAccountDDL"
              @clear="clearSourceCashAccount"
            />
            <FormErrorMessages :messages="cashTransferForm.errors.source_cash_account_id" />
          </div>
          <div class="col-span-12 lg:col-span-6">
            <FormLabel :class="{ 'text-danger': cashTransferForm.invalid('destination_cash_account_id') }">
              {{ t('views.cash_transfer.fields.destination_cash_account') }}
            </FormLabel>
            <FormSelectSearch
              v-model="cashTransferForm.destination_cash_account_id"
              v-model:search="destinationCashAccountSearch"
              :options="destinationCashAccountOptions"
              :placeholder="t('components.dropdown.placeholder')"
              :class="{ 'border-danger': cashTransferForm.invalid('destination_cash_account_id') }"
              @change="cashTransferForm.validate('destination_cash_account_id')"
              @search="loadDestinationCashAccountDDL"
              @clear="clearDestinationCashAccount"
            />
            <FormErrorMessages :messages="cashTransferForm.errors.destination_cash_account_id" />
          </div>
          <div class="col-span-12 lg:col-span-6">
            <FormLabel :class="{ 'text-danger': cashTransferForm.invalid('amount') }">
              {{ t('views.cash_transfer.fields.amount') }}
            </FormLabel>
            <FormInputCurrency
              v-model="cashTransferForm.amount"
              :allow-negative="false"
              :class="{ 'border-danger': cashTransferForm.invalid('amount') }"
              :placeholder="t('views.cash_transfer.fields.amount')"
              @change="cashTransferForm.validate('amount')"
            />
            <FormErrorMessages :messages="cashTransferForm.errors.amount" />
          </div>
          <div class="col-span-12">
            <FormLabel :class="{ 'text-danger': cashTransferForm.invalid('remarks') }">
              {{ t('views.cash_transfer.fields.remarks') }}
            </FormLabel>
            <FormTextarea
              v-model="cashTransferForm.remarks"
              rows="3"
              :class="{ 'border-danger': cashTransferForm.invalid('remarks') }"
              :placeholder="t('views.cash_transfer.fields.remarks')"
              @change="cashTransferForm.validate('remarks')"
            />
            <FormErrorMessages :messages="cashTransferForm.errors.remarks" />
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
            :disabled="cashTransferForm.validating || cashTransferForm.hasErrors"
          >
            <Lucide v-if="cashTransferForm.validating" icon="Loader" class="animate-spin" />
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

