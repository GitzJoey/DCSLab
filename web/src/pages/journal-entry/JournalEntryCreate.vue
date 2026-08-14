<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue';
import { debounce } from 'lodash';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import { convertErrorTypeToAlertListType, formatCurrency } from '@/utils/helper';
import JournalEntryService from '@/services/JournalEntryService';
import ChartOfAccountService from '@/services/ChartOfAccountService';
import CacheService from '@/services/CacheService';
import { TwoColumnsLayout } from '@/components/Base/Form/FormLayout';
import { TwoColumnsLayoutCards } from '@/components/Base/Form/FormLayout/TwoColumnsLayout.vue';
import {
  FormInput,
  FormLabel,
  FormErrorMessages,
  FormInputCode,
  FormSelect,
  FormTextarea,
  FormInputCurrency,
  FormInputDateTimeAuto,
} from '@/components/Base/Form';
import { CardState } from '@/types/enums/CardState';
import Button from '@/components/Base/Button';
import Lucide from '@/components/Base/Lucide';
import { ViewMode } from '@/types/enums/ViewMode';
import { useSelectedUserLocationStore } from '@/stores/selected-user-location';
import { ErrorCode } from '@/types/enums/ErrorCode';
import type { AlertPlaceholderProps } from '@/components/AlertPlaceholder/AlertPlaceholder.vue';

const { t } = useI18n();
const router = useRouter();
const selectedUserLocationStore = useSelectedUserLocationStore();
const journalEntryService = new JournalEntryService();
const chartOfAccountService = new ChartOfAccountService();
const cacheService = new CacheService();

const cards = ref<Array<TwoColumnsLayoutCards>>([
  {
    title: 'views.journal_entry.field_groups.company_info',
    state: CardState.Expanded,
  },
  {
    title: 'views.journal_entry.field_groups.journal_entry_data',
    state: CardState.Expanded,
  },
  {
    title: 'views.journal_entry.field_groups.items',
    state: CardState.Expanded,
  },
  { title: '', state: CardState.Hidden, id: 'button' },
]);

const journalEntryForm = journalEntryService.useJournalEntryCreateForm();
const accountOptionsDDL = ref<Array<{ value: string; label: string }>>([]);

const emits = defineEmits(['mode-state', 'loading-state', 'update-profile', 'show-alertplaceholder']);

const isUserLocationSelected = computed(() => selectedUserLocationStore.isUserLocationSelected);
const selectedUserLocation = computed(() => selectedUserLocationStore.selectedUserLocation);
const companyLabel = computed(() =>
  selectedUserLocation.value
    ? `${selectedUserLocation.value.company.code}\n${selectedUserLocation.value.company.name}`
    : '',
);
const branchLabel = computed(() =>
  selectedUserLocation.value
    ? `${selectedUserLocation.value.branch.code}\n${selectedUserLocation.value.branch.name}`
    : '',
);
const totalDebit = computed(() =>
  journalEntryForm.items.reduce((total, item) => total + Number(item.debit || 0), 0),
);
const totalCredit = computed(() =>
  journalEntryForm.items.reduce((total, item) => total + Number(item.credit || 0), 0),
);
const isBalanced = computed(() => Number(totalDebit.value.toFixed(8)) === Number(totalCredit.value.toFixed(8)));

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
  setDefaultHeaderData();
  ensureMinimumItems();
  await loadChartOfAccountDDL();
});

const handleExpandCard = (index: number) => {
  if (cards.value[index].state === CardState.Collapsed) {
    cards.value[index].state = CardState.Expanded;
  } else if (cards.value[index].state === CardState.Expanded) {
    cards.value[index].state = CardState.Collapsed;
  }
};

watch(
  journalEntryForm,
  debounce((newValue): void => {
    cacheService.setLastEntity('JOURNAL_ENTRY_CREATE', newValue.data());
  }, 500),
  { deep: true },
);

const loadFromCache = () => {
  const data = cacheService.getLastEntity('JOURNAL_ENTRY_CREATE') as Record<string, unknown> | null;
  if (!data) return;

  journalEntryForm.setData(data);
};

const setDefaultHeaderData = () => {
  journalEntryForm.setData({
    company_id: selectedUserLocation.value.company.id,
    branch_id: selectedUserLocation.value.branch.id,
    code: journalEntryForm.code || '_AUTO_',
    date: journalEntryForm.date || '_AUTO_',
    source_type: journalEntryForm.source_type || null,
    source_id: journalEntryForm.source_id || null,
    reference_no: journalEntryForm.reference_no || '',
    remarks: journalEntryForm.remarks || '',
  });
};

const loadChartOfAccountDDL = async () => {
  const result = await chartOfAccountService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    search: '',
    parent_id: undefined,
    has_parent: undefined,
    has_children: undefined,
    is_group: false,
    is_active: true,
    include_id: undefined,
    refresh: false,
    limit: 1000,
  });

  if (result.success && result.data) {
    accountOptionsDDL.value = result.data.data.map((item) => ({
      value: item.id,
      label: `${item.code} - ${item.name}`,
    }));
  }
};

const setCode = () => {
  journalEntryForm.forgetError('code');
  if (journalEntryForm.code === '_AUTO_') {
    journalEntryForm.setData({ code: '' });
  } else {
    journalEntryForm.setData({ code: '_AUTO_' });
  }
};

const addItem = () => {
  journalEntryForm.items.push({
    chart_of_account_id: '',
    debit: 0,
    credit: 0,
    remarks: '',
  });
};

const ensureMinimumItems = () => {
  while (journalEntryForm.items.length < 2) {
    addItem();
  }
};

const removeItem = (index: number) => {
  journalEntryForm.items.splice(index, 1);
  if (journalEntryForm.items.length === 0) {
    ensureMinimumItems();
  }
};

const invalidItemField = (field: string) => journalEntryForm.invalid(field as any);
const itemFieldErrors = (field: string): string | undefined => {
  const error = (journalEntryForm.errors as Record<string, Array<string> | string | undefined>)[field];

  if (Array.isArray(error)) {
    return error.join(', ');
  }

  return error;
};

const scrollToError = (id: string): void => {
  const el = document.getElementById(id);
  if (!el) return;
  el.scrollIntoView({ behavior: 'smooth', block: 'center' });
};

const resetForm = async () => {
  journalEntryForm.reset();
  journalEntryForm.setErrors({});
  journalEntryForm.setData({
    company_id: selectedUserLocation.value.company.id,
    branch_id: selectedUserLocation.value.branch.id,
    code: '_AUTO_',
    date: '_AUTO_',
    source_type: null,
    source_id: null,
    reference_no: '',
    remarks: '',
    items: [],
  });
  ensureMinimumItems();
};

const onSubmit = async () => {
  if (journalEntryForm.hasErrors) {
    const firstErrorKey = Object.keys(journalEntryForm.errors)[0];
    if (firstErrorKey) {
      scrollToError(firstErrorKey);
    }
    return;
  }

  emits('loading-state', true);
  await journalEntryForm
    .submit()
    .then(async () => {
      await resetForm();
      showAlertPlaceholder('hidden', '', null);
      emits('update-profile');
      router.push({ name: 'side-menu-journal-entry-list' });
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
</script>

<template>
  <form id="journalEntryForm" @submit.prevent="onSubmit">
    <TwoColumnsLayout :cards="cards" :using-side-tab="false" @handle-expand-card="handleExpandCard">
      <template #card-items-0>
        <div class="p-5">
          <div class="grid grid-cols-12 gap-4 gap-y-3">
            <div class="col-span-12 lg:col-span-6">
              <FormLabel>{{ t('views.journal_entry.fields.company') }}</FormLabel>
              <FormLabel class="whitespace-pre-line">{{ companyLabel }}</FormLabel>
              <FormInput type="hidden" v-model="journalEntryForm.company_id" />
            </div>
            <div class="col-span-12 lg:col-span-6">
              <FormLabel>{{ t('views.journal_entry.fields.branch') }}</FormLabel>
              <FormLabel class="whitespace-pre-line">{{ branchLabel }}</FormLabel>
              <FormInput type="hidden" v-model="journalEntryForm.branch_id" />
            </div>
          </div>
        </div>
      </template>

      <template #card-items-1>
        <div class="p-5">
          <div class="grid grid-cols-12 gap-4 gap-y-3">
            <div class="col-span-12 lg:col-span-4">
              <FormLabel :class="{ 'text-danger': journalEntryForm.invalid('code') }">
                {{ t('views.journal_entry.fields.code') }}
              </FormLabel>
              <FormInputCode
                v-model="journalEntryForm.code"
                :class="{ 'border-danger': journalEntryForm.invalid('code') }"
                @click="setCode"
                @change="journalEntryForm.validate('code')"
              />
              <FormErrorMessages :messages="journalEntryForm.errors.code" />
            </div>
            <div class="col-span-12 lg:col-span-4">
              <FormLabel :class="{ 'text-danger': journalEntryForm.invalid('date') }">
                {{ t('views.journal_entry.fields.date') }}
              </FormLabel>
              <FormInputDateTimeAuto
                v-model="journalEntryForm.date"
                :class="{ 'border-danger': journalEntryForm.invalid('date') }"
                @change="journalEntryForm.validate('date')"
              />
              <FormErrorMessages :messages="journalEntryForm.errors.date" />
            </div>
            <div class="col-span-12 lg:col-span-4">
              <FormLabel :class="{ 'text-danger': journalEntryForm.invalid('reference_no') }">
                {{ t('views.journal_entry.fields.reference_no') }}
              </FormLabel>
              <FormInput
                v-model="journalEntryForm.reference_no"
                :class="{ 'border-danger': journalEntryForm.invalid('reference_no') }"
                @change="journalEntryForm.validate('reference_no')"
              />
              <FormErrorMessages :messages="journalEntryForm.errors.reference_no" />
            </div>
            <div class="col-span-12 lg:col-span-6">
              <FormLabel :class="{ 'text-danger': journalEntryForm.invalid('source_type') }">
                {{ t('views.journal_entry.fields.source_type') }}
              </FormLabel>
              <FormInput
                v-model="journalEntryForm.source_type"
                :class="{ 'border-danger': journalEntryForm.invalid('source_type') }"
                @change="journalEntryForm.validate('source_type')"
              />
              <FormErrorMessages :messages="journalEntryForm.errors.source_type" />
            </div>
            <div class="col-span-12 lg:col-span-6">
              <FormLabel :class="{ 'text-danger': journalEntryForm.invalid('source_id') }">
                {{ t('views.journal_entry.fields.source_id') }}
              </FormLabel>
              <FormInput
                v-model="journalEntryForm.source_id"
                type="number"
                min="1"
                :class="{ 'border-danger': journalEntryForm.invalid('source_id') }"
                @change="journalEntryForm.validate('source_id')"
              />
              <FormErrorMessages :messages="journalEntryForm.errors.source_id" />
            </div>
            <div class="col-span-12">
              <FormLabel :class="{ 'text-danger': journalEntryForm.invalid('remarks') }">
                {{ t('views.journal_entry.fields.remarks') }}
              </FormLabel>
              <FormTextarea
                v-model="journalEntryForm.remarks"
                :class="{ 'border-danger': journalEntryForm.invalid('remarks') }"
                @change="journalEntryForm.validate('remarks')"
              />
              <FormErrorMessages :messages="journalEntryForm.errors.remarks" />
            </div>
          </div>
        </div>
      </template>

      <template #card-items-2>
        <div class="p-5">
          <div class="mb-4 flex items-center justify-between">
            <div>
              <div class="text-base font-medium">{{ t('views.journal_entry.fields.items') }}</div>
              <div class="text-sm text-slate-500">
                {{ t('views.journal_entry.helper.single_side_amount') }}
              </div>
            </div>
            <Button type="button" variant="outline-primary" @click="addItem">
              <Lucide icon="Plus" class="mr-1 h-4 w-4" />
              {{ t('views.journal_entry.actions.add_item') }}
            </Button>
          </div>

          <FormErrorMessages :messages="journalEntryForm.errors.items" />

          <div class="space-y-4">
            <div
              v-for="(item, index) in journalEntryForm.items"
              :key="index"
              class="rounded-md border border-slate-200/60 p-4 dark:border-darkmode-400"
            >
              <div class="mb-3 flex items-center justify-between">
                <div class="font-medium">{{ t('views.journal_entry.fields.item') }} #{{ index + 1 }}</div>
                <Button type="button" variant="outline-danger" @click="removeItem(index)">
                  <Lucide icon="Trash2" class="h-4 w-4" />
                </Button>
              </div>

              <div class="grid grid-cols-12 gap-4 gap-y-3">
                <div class="col-span-12 lg:col-span-4">
                  <FormLabel :class="{ 'text-danger': invalidItemField(`items.${index}.chart_of_account_id`) }">
                    {{ t('views.journal_entry.fields.chart_of_account') }}
                  </FormLabel>
                  <FormSelect
                    :id="`items.${index}.chart_of_account_id`"
                    v-model="item.chart_of_account_id"
                    :options="accountOptionsDDL"
                    :class="{ 'border-danger': invalidItemField(`items.${index}.chart_of_account_id`) }"
                    @change="journalEntryForm.validate(`items.${index}.chart_of_account_id` as any)"
                  />
                  <FormErrorMessages :messages="itemFieldErrors(`items.${index}.chart_of_account_id`)" />
                </div>

                <div class="col-span-12 lg:col-span-2">
                  <FormLabel :class="{ 'text-danger': invalidItemField(`items.${index}.debit`) }">
                    {{ t('views.journal_entry.fields.debit') }}
                  </FormLabel>
                  <FormInputCurrency
                    :id="`items.${index}.debit`"
                    v-model="item.debit"
                    :allow-negative="false"
                    :class="{ 'border-danger': invalidItemField(`items.${index}.debit`) }"
                    @change="journalEntryForm.validate(`items.${index}.debit` as any)"
                  />
                  <FormErrorMessages :messages="itemFieldErrors(`items.${index}.debit`)" />
                </div>

                <div class="col-span-12 lg:col-span-2">
                  <FormLabel :class="{ 'text-danger': invalidItemField(`items.${index}.credit`) }">
                    {{ t('views.journal_entry.fields.credit') }}
                  </FormLabel>
                  <FormInputCurrency
                    :id="`items.${index}.credit`"
                    v-model="item.credit"
                    :allow-negative="false"
                    :class="{ 'border-danger': invalidItemField(`items.${index}.credit`) }"
                    @change="journalEntryForm.validate(`items.${index}.credit` as any)"
                  />
                  <FormErrorMessages :messages="itemFieldErrors(`items.${index}.credit`)" />
                </div>

                <div class="col-span-12 lg:col-span-4">
                  <FormLabel :class="{ 'text-danger': invalidItemField(`items.${index}.remarks`) }">
                    {{ t('views.journal_entry.fields.item_remarks') }}
                  </FormLabel>
                  <FormInput
                    :id="`items.${index}.remarks`"
                    v-model="item.remarks"
                    :class="{ 'border-danger': invalidItemField(`items.${index}.remarks`) }"
                    @change="journalEntryForm.validate(`items.${index}.remarks` as any)"
                  />
                  <FormErrorMessages :messages="itemFieldErrors(`items.${index}.remarks`)" />
                </div>
              </div>
            </div>
          </div>

          <div class="mt-5 rounded-md border border-slate-200/60 p-4 dark:border-darkmode-400">
            <div class="grid grid-cols-12 gap-4 text-sm">
              <div class="col-span-12 lg:col-span-4">
                <div class="text-slate-500">{{ t('views.journal_entry.fields.total_debit') }}</div>
                <div class="text-lg font-medium">{{ formatCurrency(totalDebit) }}</div>
              </div>
              <div class="col-span-12 lg:col-span-4">
                <div class="text-slate-500">{{ t('views.journal_entry.fields.total_credit') }}</div>
                <div class="text-lg font-medium">{{ formatCurrency(totalCredit) }}</div>
              </div>
              <div class="col-span-12 lg:col-span-4">
                <div class="text-slate-500">{{ t('views.journal_entry.fields.balance_status') }}</div>
                <div :class="['text-lg font-medium', isBalanced ? 'text-success' : 'text-danger']">
                  {{ isBalanced ? t('views.journal_entry.options.balance.balanced') : t('views.journal_entry.options.balance.unbalanced') }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </template>

      <template #button>
        <div class="flex justify-end gap-2 p-5">
          <Button type="button" variant="outline-secondary" @click="resetForm">
            {{ t('components.buttons.reset') }}
          </Button>
          <Button type="submit" variant="primary">
            {{ t('components.buttons.save') }}
          </Button>
        </div>
      </template>
    </TwoColumnsLayout>
  </form>
</template>
