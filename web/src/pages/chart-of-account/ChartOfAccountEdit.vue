<script setup lang="ts">
// #region Imports
import { computed, onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRoute, useRouter } from 'vue-router';
import { convertErrorTypeToAlertListType } from '@/utils/helper';
import ChartOfAccountService from '@/services/ChartOfAccountService';
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
import type { ChartOfAccount } from '@/types/models/ChartOfAccount';
// #endregion

// #region Declarations
const { t } = useI18n();
const router = useRouter();
const route = useRoute();
const selectedUserLocationStore = useSelectedUserLocationStore();
const chartOfAccountService = new ChartOfAccountService();

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

const chartOfAccountForm = chartOfAccountService.useChartOfAccountEditForm(route.params.ulid.toString());
const chartOfAccountData = ref<ChartOfAccount | null>(null);
const companyCode = ref<string>('');
const companyName = ref<string>('');
const parentDDL = ref<Array<DropDownOption> | null>(null);
const parentSearch = ref<string>('');
// #endregion

// #region Props, Emits
const emits = defineEmits(['mode-state', 'loading-state', 'update-profile', 'show-alertplaceholder']);
// #endregion

// #region Computed
const isUserLocationSelected = computed(() => selectedUserLocationStore.isUserLocationSelected);
const parentOptions = computed(() =>
  (parentDDL.value ?? []).map((item) => ({
    value: item.code,
    label: item.name,
  })),
);
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
const scopeLabel = computed(() =>
  chartOfAccountData.value?.scope
    ? t(`views.chart_of_account.options.scope.${chartOfAccountData.value.scope}`)
    : '-',
);
// #endregion

// #region Vue Core
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
});

const handleExpandCard = (index: number) => {
  if (cards.value[index].state === CardState.Collapsed) {
    cards.value[index].state = CardState.Expanded;
  } else if (cards.value[index].state === CardState.Expanded) {
    cards.value[index].state = CardState.Collapsed;
  }
};
// #endregion

// #region Methods - ChartOfAccount
const loadParentDDL = async (search = ''): Promise<void> => {
  if (!chartOfAccountData.value?.company.id) return;

  const result = await chartOfAccountService.readAnyGet({
    with_trashed: false,
    company_id: chartOfAccountData.value.company.id,
    search,
    parent_id: undefined,
    has_parent: undefined,
    has_children: undefined,
    is_group: true,
    include_id: chartOfAccountData.value.parent?.id ?? chartOfAccountForm.parent_id ?? undefined,
    refresh: false,
    limit: 20,
  });

  if (result.success && result.data) {
    parentDDL.value = result.data.data
      .filter((item) => item.id !== chartOfAccountData.value?.id)
      .map((item) => ({
        code: item.id,
        name: `${item.code} - ${item.name}`,
      }));
  }
};

const loadData = async () => {
  emits('loading-state', true);
  const result = await chartOfAccountService.read(route.params.ulid.toString());

  if (result.success && result.data) {
    chartOfAccountData.value = result.data;
    companyCode.value = result.data.company?.code ?? '';
    companyName.value = result.data.company?.name ?? '';

    chartOfAccountForm.setData({
      company_id: result.data.company?.id ?? '',
      parent_id: result.data.parent?.id ?? null,
      code: result.data.code,
      name: result.data.name,
      account_type: result.data.account_type,
      normal_balance: result.data.normal_balance,
      is_group: result.data.is_group,
      is_active: result.data.is_active,
      remarks: result.data.remarks ?? '',
    } as any);

    await loadParentDDL();
  } else {
    router.push({ name: 'side-menu-chart-of-account-list' });
  }

  emits('loading-state', false);
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
  parentSearch.value = '';
  await loadData();
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
            {{ companyCode }}
            <br />
            {{ companyName }}
          </FormLabel>
          <FormInput type="hidden" v-model="chartOfAccountForm.company_id" />
        </div>
      </template>

      <template #card-items-1>
        <div class="p-5">
          <div class="grid grid-cols-12 gap-4 gap-y-3">
            <div class="col-span-12 sm:col-span-6 lg:col-span-3">
              <FormLabel>{{ t('views.chart_of_account.fields.scope') }}</FormLabel>
              <FormInput :model-value="scopeLabel" type="text" readonly />
            </div>

            <div class="col-span-12 sm:col-span-6 lg:col-span-5">
              <FormLabel>{{ t('views.chart_of_account.fields.system_key') }}</FormLabel>
              <FormInput :model-value="chartOfAccountData?.system_key ?? '-'" type="text" readonly />
            </div>

            <div class="col-span-12 sm:col-span-6 lg:col-span-2">
              <FormLabel>{{ t('views.chart_of_account.fields.level') }}</FormLabel>
              <FormInput :model-value="chartOfAccountData?.level?.toString() ?? '-'" type="text" readonly />
            </div>

            <div class="col-span-12 sm:col-span-6 lg:col-span-2">
              <FormLabel>{{ t('views.chart_of_account.fields.source_id') }}</FormLabel>
              <FormInput :model-value="chartOfAccountData?.source_id?.toString() ?? '-'" type="text" readonly />
            </div>

            <div class="col-span-12 lg:col-span-6">
              <FormLabel>{{ t('views.chart_of_account.fields.source_type') }}</FormLabel>
              <FormInput :model-value="chartOfAccountData?.source_type ?? '-'" type="text" readonly />
            </div>

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
