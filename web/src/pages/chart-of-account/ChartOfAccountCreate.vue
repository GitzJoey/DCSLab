<script setup lang="ts">
// #region Imports
import { computed, onMounted, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import { debounce } from 'lodash';
import { convertErrorTypeToAlertListType } from '@/utils/helper';
import ChartOfAccountService from '@/services/ChartOfAccountService';
import CacheService from '@/services/CacheService';
import { TwoColumnsLayout } from '@/components/Base/Form/FormLayout';
import { TwoColumnsLayoutCards } from '@/components/Base/Form/FormLayout/TwoColumnsLayout.vue';
import {
  FormInput,
  FormLabel,
  FormErrorMessages,
  FormInputCode,
  FormSelectSearch,
  FormSelect,
  FormTextarea,
  FormSwitch,
} from '@/components/Base/Form';
import { CardState } from '@/types/enums/CardState';
import Button from '@/components/Base/Button';
import Lucide from '@/components/Base/Lucide';
import { ViewMode } from '@/types/enums/ViewMode';
import { useSelectedUserLocationStore } from '@/stores/selected-user-location';
import { ErrorCode } from '@/types/enums/ErrorCode';
import type { AlertPlaceholderProps } from '@/components/AlertPlaceholder/AlertPlaceholder.vue';
import type { DropDownOption } from '@/types/models/DropDownOption';
import { CHART_OF_ACCOUNT_SYSTEM_KEYS } from '@/types/models/ChartOfAccount';
// #endregion

// #region Declarations
const { t } = useI18n();
const router = useRouter();
const selectedUserLocationStore = useSelectedUserLocationStore();
const chartOfAccountService = new ChartOfAccountService();
const cacheService = new CacheService();

const cards = ref<Array<TwoColumnsLayoutCards>>([
  {
    title: 'views.chart_of_account.field_groups.company_info',
    state: CardState.Expanded,
  },
  {
    title: 'views.chart_of_account.field_groups.chart_of_account_data',
    state: CardState.Expanded,
  },
  { title: '', state: CardState.Hidden, id: 'button' },
]);

const chartOfAccountForm = chartOfAccountService.useChartOfAccountCreateForm();
const parentDDL = ref<Array<DropDownOption> | null>(null);
const parentSearch = ref<string>('');
// #endregion

// #region Props, Emits
const emits = defineEmits(['mode-state', 'loading-state', 'update-profile', 'show-alertplaceholder']);
// #endregion

// #region Computed
const isUserLocationSelected = computed(() => selectedUserLocationStore.isUserLocationSelected);
const selectedUserLocation = computed(() => selectedUserLocationStore.selectedUserLocation);
const parentOptions = computed(() =>
  (parentDDL.value ?? []).map((item) => ({
    value: item.code,
    label: item.name,
  })),
);
const scopeOptions = computed(() => [
  { value: 'user', label: t('views.chart_of_account.options.scope.user') },
  { value: 'system', label: t('views.chart_of_account.options.scope.system') },
]);
const accountTypeOptions = computed(() => [
  { value: 'asset', label: t('views.chart_of_account.options.account_type.asset') },
  { value: 'liability', label: t('views.chart_of_account.options.account_type.liability') },
  { value: 'equity', label: t('views.chart_of_account.options.account_type.equity') },
  { value: 'income', label: t('views.chart_of_account.options.account_type.income') },
  { value: 'expense', label: t('views.chart_of_account.options.account_type.expense') },
]);
const normalBalanceOptions = computed(() => [
  { value: 'debit', label: t('views.chart_of_account.options.normal_balance.debit') },
  { value: 'credit', label: t('views.chart_of_account.options.normal_balance.credit') },
]);
const systemKeyOptions = computed(() =>
  CHART_OF_ACCOUNT_SYSTEM_KEYS.map((item) => ({
    value: item,
    label: item,
  })),
);
// #endregion

// #region Vue Core
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
  setCompanyIdData();
  normalizeFormDefaults();
  await loadParentDDL();
});

const handleExpandCard = (index: number) => {
  if (cards.value[index].state === CardState.Collapsed) {
    cards.value[index].state = CardState.Expanded;
  } else if (cards.value[index].state === CardState.Expanded) {
    cards.value[index].state = CardState.Collapsed;
  }
};

watch(
  chartOfAccountForm,
  debounce((newValue): void => {
    cacheService.setLastEntity('CHART_OF_ACCOUNT_CREATE', newValue.data());
  }, 500),
  { deep: true },
);

watch(
  () => chartOfAccountForm.scope,
  (newValue) => {
    if (newValue !== 'system' && chartOfAccountForm.system_key) {
      chartOfAccountForm.setData({ system_key: null });
      chartOfAccountForm.forgetError('system_key');
    }
  },
);
// #endregion

// #region Methods - ChartOfAccount
const setCompanyIdData = () => {
  chartOfAccountForm.setData({
    company_id: selectedUserLocation.value.company.id,
  });
};

const normalizeFormDefaults = () => {
  chartOfAccountForm.setData({
    parent_id: chartOfAccountForm.parent_id ?? null,
    scope: chartOfAccountForm.scope || 'user',
    system_key: chartOfAccountForm.system_key || null,
    source_type: chartOfAccountForm.source_type || null,
    source_id: chartOfAccountForm.source_id || null,
    code: chartOfAccountForm.code || '_AUTO_',
    name: chartOfAccountForm.name || '',
    account_type: chartOfAccountForm.account_type || 'asset',
    normal_balance: chartOfAccountForm.normal_balance || 'debit',
    is_group: chartOfAccountForm.is_group ?? false,
    is_active: chartOfAccountForm.is_active ?? true,
    remarks: chartOfAccountForm.remarks || '',
  });
};

const loadFromCache = () => {
  const data = cacheService.getLastEntity('CHART_OF_ACCOUNT_CREATE') as Record<string, unknown> | null;
  if (!data) return;

  chartOfAccountForm.setData(data);
};

const loadParentDDL = async (search = ''): Promise<void> => {
  if (!selectedUserLocation.value) return;

  const result = await chartOfAccountService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    search,
    parent_id: undefined,
    has_parent: undefined,
    has_children: undefined,
    is_group: true,
    include_id: chartOfAccountForm.parent_id ?? undefined,
    refresh: false,
    limit: 20,
  });

  if (result.success && result.data) {
    parentDDL.value = result.data.data.map((item) => ({
      code: item.id,
      name: `${item.code} - ${item.name}`,
    }));
  }
};

const setCode = () => {
  chartOfAccountForm.forgetError('code');
  if (chartOfAccountForm.code == '_AUTO_') {
    chartOfAccountForm.setData({ code: '' });
  } else {
    chartOfAccountForm.setData({ code: '_AUTO_' });
  }
};
// #endregion

// #region Actions
const scrollToError = (id: string): void => {
  const el = document.getElementById(id);
  if (!el) return;
  el.scrollIntoView({ behavior: 'smooth', block: 'center' });
};

const resetForm = async () => {
  chartOfAccountForm.reset();
  chartOfAccountForm.setErrors({});
  chartOfAccountForm.setData({
    company_id: selectedUserLocation.value.company.id,
    parent_id: null,
    scope: 'user',
    system_key: null,
    source_type: null,
    source_id: null,
    code: '_AUTO_',
    name: '',
    account_type: 'asset',
    normal_balance: 'debit',
    is_group: false,
    is_active: true,
    remarks: '',
  });
  parentSearch.value = '';
  await loadParentDDL();
};

const onSubmit = async () => {
  if (chartOfAccountForm.hasErrors) {
    const firstErrorKey = Object.keys(chartOfAccountForm.errors)[0];
    if (firstErrorKey) {
      scrollToError(firstErrorKey);
    }
    return;
  }

  emits('loading-state', true);
  await chartOfAccountForm
    .submit()
    .then(() => {
      resetForm();
      showAlertPlaceholder('hidden', '', null);
      emits('update-profile');
      router.push({ name: 'side-menu-chart-of-account-list' });
    })
    .catch((error) => {
      showAlertPlaceholder('danger', '', convertErrorTypeToAlertListType(error));
    })
    .finally(() => {
      emits('loading-state', false);
    });
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
// #endregion
</script>

<template>
  <form id="chartOfAccountForm" @submit.prevent="onSubmit">
    <TwoColumnsLayout :cards="cards" :using-side-tab="false" @handle-expand-card="handleExpandCard">
      <template #card-items-0>
        <div class="p-5">
          <FormLabel>
            {{ selectedUserLocation.company.code }}
            <br />
            {{ selectedUserLocation.company.name }}
          </FormLabel>
          <FormInput type="hidden" v-model="chartOfAccountForm.company_id" />
        </div>
      </template>

      <template #card-items-1>
        <div class="p-5">
          <div class="grid grid-cols-12 gap-4 gap-y-3">
            <div class="col-span-12 lg:col-span-6">
              <FormLabel :class="{ 'text-danger': chartOfAccountForm.invalid('parent_id') }">
                {{ t('views.chart_of_account.fields.parent') }}
              </FormLabel>
              <FormSelectSearch
                id="parent_id"
                v-model="chartOfAccountForm.parent_id"
                v-model:search="parentSearch"
                :options="parentOptions"
                :placeholder="t('components.dropdown.placeholder')"
                :class="{ 'border-danger': chartOfAccountForm.invalid('parent_id') }"
                @change="chartOfAccountForm.validate('parent_id')"
                @search="loadParentDDL"
                @clear="chartOfAccountForm.validate('parent_id')"
              />
              <FormErrorMessages :messages="chartOfAccountForm.errors.parent_id" />
            </div>

            <div class="col-span-12 lg:col-span-3">
              <FormLabel :class="{ 'text-danger': chartOfAccountForm.invalid('scope') }">
                {{ t('views.chart_of_account.fields.scope') }}
              </FormLabel>
              <FormSelect
                id="scope"
                v-model="chartOfAccountForm.scope"
                :class="{ 'border-danger': chartOfAccountForm.invalid('scope') }"
                @change="chartOfAccountForm.validate('scope')"
              >
                <option v-for="item in scopeOptions" :key="item.value" :value="item.value">
                  {{ item.label }}
                </option>
              </FormSelect>
              <FormErrorMessages :messages="chartOfAccountForm.errors.scope" />
            </div>

            <div class="col-span-12 lg:col-span-3">
              <FormLabel :class="{ 'text-danger': chartOfAccountForm.invalid('system_key') }">
                {{ t('views.chart_of_account.fields.system_key') }}
              </FormLabel>
              <FormSelect
                id="system_key"
                v-model="chartOfAccountForm.system_key"
                :disabled="chartOfAccountForm.scope !== 'system'"
                :class="{ 'border-danger': chartOfAccountForm.invalid('system_key') }"
                @change="chartOfAccountForm.validate('system_key')"
              >
                <option :value="null">{{ t('views.chart_of_account.options.placeholder') }}</option>
                <option v-for="item in systemKeyOptions" :key="item.value" :value="item.value">
                  {{ item.label }}
                </option>
              </FormSelect>
              <FormErrorMessages :messages="chartOfAccountForm.errors.system_key" />
            </div>

            <div class="col-span-12 sm:col-span-4">
              <FormLabel :class="{ 'text-danger': chartOfAccountForm.invalid('code') }">
                {{ t('views.chart_of_account.fields.code') }}
              </FormLabel>
              <FormInputCode
                id="code"
                v-model="chartOfAccountForm.code"
                :class="{ 'border-danger': chartOfAccountForm.invalid('code') }"
                :placeholder="t('views.chart_of_account.fields.code')"
                @set-auto="setCode"
                @change="chartOfAccountForm.validate('code')"
              />
              <FormErrorMessages :messages="chartOfAccountForm.errors.code" />
            </div>

            <div class="col-span-12 sm:col-span-8">
              <FormLabel :class="{ 'text-danger': chartOfAccountForm.invalid('name') }">
                {{ t('views.chart_of_account.fields.name') }}
              </FormLabel>
              <FormInput
                id="name"
                v-model="chartOfAccountForm.name"
                type="text"
                :class="{ 'border-danger': chartOfAccountForm.invalid('name') }"
                :placeholder="t('views.chart_of_account.fields.name')"
                @change="chartOfAccountForm.validate('name')"
              />
              <FormErrorMessages :messages="chartOfAccountForm.errors.name" />
            </div>

            <div class="col-span-12 sm:col-span-6 lg:col-span-4">
              <FormLabel :class="{ 'text-danger': chartOfAccountForm.invalid('account_type') }">
                {{ t('views.chart_of_account.fields.account_type') }}
              </FormLabel>
              <FormSelect
                id="account_type"
                v-model="chartOfAccountForm.account_type"
                :class="{ 'border-danger': chartOfAccountForm.invalid('account_type') }"
                @change="chartOfAccountForm.validate('account_type')"
              >
                <option v-for="item in accountTypeOptions" :key="item.value" :value="item.value">
                  {{ item.label }}
                </option>
              </FormSelect>
              <FormErrorMessages :messages="chartOfAccountForm.errors.account_type" />
            </div>

            <div class="col-span-12 sm:col-span-6 lg:col-span-4">
              <FormLabel :class="{ 'text-danger': chartOfAccountForm.invalid('normal_balance') }">
                {{ t('views.chart_of_account.fields.normal_balance') }}
              </FormLabel>
              <FormSelect
                id="normal_balance"
                v-model="chartOfAccountForm.normal_balance"
                :class="{ 'border-danger': chartOfAccountForm.invalid('normal_balance') }"
                @change="chartOfAccountForm.validate('normal_balance')"
              >
                <option v-for="item in normalBalanceOptions" :key="item.value" :value="item.value">
                  {{ item.label }}
                </option>
              </FormSelect>
              <FormErrorMessages :messages="chartOfAccountForm.errors.normal_balance" />
            </div>

            <div class="col-span-12 sm:col-span-6 lg:col-span-2">
              <FormLabel :class="{ 'text-danger': chartOfAccountForm.invalid('is_group') }" class="pr-5">
                {{ t('views.chart_of_account.fields.is_group') }}
              </FormLabel>
              <FormSwitch>
                <FormSwitch.Input
                  id="is_group"
                  v-model="chartOfAccountForm.is_group"
                  type="checkbox"
                  :class="{ 'border-danger': chartOfAccountForm.invalid('is_group') }"
                  @change="chartOfAccountForm.validate('is_group')"
                />
              </FormSwitch>
              <FormErrorMessages :messages="chartOfAccountForm.errors.is_group" />
            </div>

            <div class="col-span-12 sm:col-span-6 lg:col-span-2">
              <FormLabel :class="{ 'text-danger': chartOfAccountForm.invalid('is_active') }" class="pr-5">
                {{ t('views.chart_of_account.fields.is_active') }}
              </FormLabel>
              <FormSwitch>
                <FormSwitch.Input
                  id="is_active"
                  v-model="chartOfAccountForm.is_active"
                  type="checkbox"
                  :class="{ 'border-danger': chartOfAccountForm.invalid('is_active') }"
                  @change="chartOfAccountForm.validate('is_active')"
                />
              </FormSwitch>
              <FormErrorMessages :messages="chartOfAccountForm.errors.is_active" />
            </div>

            <div class="col-span-12 sm:col-span-6">
              <FormLabel :class="{ 'text-danger': chartOfAccountForm.invalid('source_type') }">
                {{ t('views.chart_of_account.fields.source_type') }}
              </FormLabel>
              <FormInput
                id="source_type"
                v-model="chartOfAccountForm.source_type"
                type="text"
                :class="{ 'border-danger': chartOfAccountForm.invalid('source_type') }"
                :placeholder="t('views.chart_of_account.fields.source_type')"
                @change="chartOfAccountForm.validate('source_type')"
              />
              <FormErrorMessages :messages="chartOfAccountForm.errors.source_type" />
            </div>

            <div class="col-span-12 sm:col-span-6">
              <FormLabel :class="{ 'text-danger': chartOfAccountForm.invalid('source_id') }">
                {{ t('views.chart_of_account.fields.source_id') }}
              </FormLabel>
              <FormInput
                id="source_id"
                v-model="chartOfAccountForm.source_id"
                type="number"
                min="1"
                :class="{ 'border-danger': chartOfAccountForm.invalid('source_id') }"
                :placeholder="t('views.chart_of_account.fields.source_id')"
                @change="chartOfAccountForm.validate('source_id')"
              />
              <FormErrorMessages :messages="chartOfAccountForm.errors.source_id" />
            </div>

            <div class="col-span-12">
              <FormLabel :class="{ 'text-danger': chartOfAccountForm.invalid('remarks') }">
                {{ t('views.chart_of_account.fields.remarks') }}
              </FormLabel>
              <FormTextarea
                id="remarks"
                v-model="chartOfAccountForm.remarks"
                :class="{ 'border-danger': chartOfAccountForm.invalid('remarks') }"
                :placeholder="t('views.chart_of_account.fields.remarks')"
                @change="chartOfAccountForm.validate('remarks')"
              />
              <FormErrorMessages :messages="chartOfAccountForm.errors.remarks" />
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
            :disabled="chartOfAccountForm.validating || chartOfAccountForm.hasErrors"
          >
            <Lucide v-if="chartOfAccountForm.validating" icon="Loader" class="animate-spin" />
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
