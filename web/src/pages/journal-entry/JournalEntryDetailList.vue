<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import DataList from '@/components/DataList';
import type { DataListEmittedData } from '@/components/DataList/DataList.vue';
import { FormInputDateTime, FormLabel } from '@/components/Base/Form';
import JournalEntryItemService from '@/services/JournalEntryItemService';
import type { JournalEntryItem } from '@/types/models/JournalEntry';
import type { Collection } from '@/types/resources/Collection';
import type { ServiceResponse } from '@/types/services/ServiceResponse';
import { ViewMode } from '@/types/enums/ViewMode';
import type { AlertPlaceholderProps } from '@/components/AlertPlaceholder/AlertPlaceholder.vue';
import { formatCurrency, formatDate } from '@/utils/helper';
import { useSelectedUserLocationStore } from '@/stores/selected-user-location';
import { ErrorCode } from '@/types/enums/ErrorCode';

interface JournalEntryDetailRow {
  id: string;
  sequence: number;
  journal_code: string;
  date: string;
  reference_no: string | null;
  account_code: string | null;
  account_name: string | null;
  debit: number;
  credit: number;
  remarks: string | null;
  journal_remarks: string | null;
}

const { t } = useI18n();
const router = useRouter();
const journalEntryItemService = new JournalEntryItemService();
const selectedUserLocationStore = useSelectedUserLocationStore();

const emits = defineEmits([
  'mode-state',
  'loading-state',
  'show-alertplaceholder',
]);

const filters = ref<{
  search: string;
  start_date: string | null;
  end_date: string | null;
}>({
  search: '',
  start_date: null,
  end_date: null,
});

const journalEntryItemLists = ref<Collection<Array<JournalEntryItem>> | null>({
  data: [],
  meta: {
    current_page: 1,
    from: null,
    last_page: 0,
    path: '',
    per_page: 10,
    to: null,
    total: 0,
  },
  links: {
    first: '',
    last: '',
    prev: null,
    next: null,
  },
});

const isUserLocationSelected = computed(() => selectedUserLocationStore.isUserLocationSelected);
const selectedUserLocation = computed(() => selectedUserLocationStore.selectedUserLocation);

const entryDetails = computed<JournalEntryDetailRow[]>(() =>
  (journalEntryItemLists.value?.data ?? []).map((item) => ({
    id: item.id,
    sequence: item.sequence,
    journal_code: item.journal_entry?.code ?? '-',
    date: item.journal_entry?.date ?? '',
    reference_no: item.journal_entry?.reference_no ?? null,
    account_code: item.chart_of_account?.code ?? null,
    account_name: item.chart_of_account?.name ?? null,
    debit: item.debit,
    credit: item.credit,
    remarks: item.remarks,
    journal_remarks: item.journal_entry?.remarks ?? null,
  })),
);

onMounted(async () => {
  emits('mode-state', ViewMode.LIST);

  if (!isUserLocationSelected.value) {
    router.push({
      name: 'side-menu-error-code',
      params: { code: ErrorCode.USERLOCATION_REQUIRED },
    });
    return;
  }

  const now = new Date();
  const startOfMonth = new Date(now.getFullYear(), now.getMonth(), 1, 0, 0, 0);
  const endOfMonth = new Date(now.getFullYear(), now.getMonth() + 1, 0, 23, 59, 59);
  filters.value.start_date = formatDate(startOfMonth.toString(), 'YYYY-MM-DD HH:mm:ss');
  filters.value.end_date = formatDate(endOfMonth.toString(), 'YYYY-MM-DD HH:mm:ss');

  await getEntryDetails(true, 1, journalEntryItemLists.value?.meta.per_page ?? 10);
});

const getEntryDetails = async (refresh: boolean, page: number, perPage: number) => {
  emits('loading-state', true);

  const result: ServiceResponse<Collection<Array<JournalEntryItem>> | null> = await journalEntryItemService.readAnyPaginate({
    company_id: selectedUserLocation.value.company.id,
    branch_id: selectedUserLocation.value.branch.id,
    search: filters.value.search || undefined,
    start_date: filters.value.start_date || undefined,
    end_date: filters.value.end_date || undefined,
    refresh,
    page,
    per_page: perPage,
  });

  if (result.success && result.data) {
    journalEntryItemLists.value = result.data;
    showAlertPlaceholder('hidden', '', null);
  } else {
    showAlertPlaceholder('danger', '', result.errors as Record<string, Array<string>>);
  }

  emits('loading-state', false);
};

const handleDataListChange = async (data: DataListEmittedData) => {
  filters.value.search = data.search.text;
  await getEntryDetails(false, data.pagination.page, data.pagination.per_page);
};

const handleDateFilterChange = async () => {
  await getEntryDetails(true, 1, journalEntryItemLists.value?.meta.per_page ?? 10);
};

const formatAmountCell = (value: number) => {
  return Number(value) === 0 ? '-' : formatCurrency(value);
};

const amountCellClass = (value: number) => {
  return Number(value) === 0
    ? 'text-slate-300 dark:text-slate-500'
    : 'text-slate-700 dark:text-slate-100';
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
  <div class="grid grid-cols-12 gap-6 mt-5">
    <div class="col-span-12 intro-y lg:col-span-12">
      <div class="mb-3 grid grid-cols-12 gap-4 gap-y-3">
        <div class="col-span-12 lg:col-span-6 md:col-span-6">
          <FormLabel>
            {{ t('views.journal_entry.fields.start_date') }}
          </FormLabel>
          <FormInputDateTime
            v-model="filters.start_date"
            :placeholder="t('views.journal_entry.fields.start_date')"
            @change="handleDateFilterChange"
          />
        </div>
        <div class="col-span-12 lg:col-span-6 md:col-span-6">
          <FormLabel>
            {{ t('views.journal_entry.fields.end_date') }}
          </FormLabel>
          <FormInputDateTime
            v-model="filters.end_date"
            :placeholder="t('views.journal_entry.fields.end_date')"
            @change="handleDateFilterChange"
          />
        </div>
      </div>

      <DataList
        :title="t('views.journal_entry.page_title')"
        :enable-search="true"
        :can-print="false"
        :can-export="false"
        :pagination="journalEntryItemLists ? journalEntryItemLists.meta : null"
        @dataListChanged="handleDataListChange"
      >
        <template #content>
          <div v-if="entryDetails.length === 0" class="mt-5 flex justify-center italic">
            {{ t('components.data-list.data_not_found') }}
          </div>

          <div v-else class="mt-5 space-y-4">
            <div
              v-for="item in entryDetails"
              :key="item.id"
              class="grid grid-cols-12 gap-4 rounded-xl border border-slate-200 bg-slate-50/80 p-4 shadow-sm dark:border-darkmode-400 dark:bg-darkmode-600/30"
            >
              <div class="col-span-12 md:col-span-3 self-start">
                <div class="space-y-2">
                  <div class="text-primary text-xs font-semibold uppercase tracking-wide">
                    {{ t('views.journal_entry.page_title') }}
                  </div>
                  <div class="grid grid-cols-12 items-center gap-x-3 gap-y-2 text-xs">
                    <div class="col-span-4 text-slate-500">{{ t('views.journal_entry.fields.code') }}</div>
                    <div class="col-span-8 text-slate-700 dark:text-slate-200 break-words">
                      {{ item.journal_code }}
                    </div>
                    <div class="col-span-4 text-slate-500">{{ t('views.journal_entry.fields.item') }}</div>
                    <div class="col-span-8 text-slate-700 dark:text-slate-200">
                      #{{ item.sequence }}
                    </div>
                    <div class="col-span-4 text-slate-500">{{ t('views.journal_entry.fields.date') }}</div>
                    <div class="col-span-8 text-slate-700 dark:text-slate-200 break-words">
                      {{ item.date ? formatDate(item.date, 'DD-MMM-YYYY HH:mm:ss') : '-' }}
                    </div>
                    <div class="col-span-4 text-slate-500">{{ t('views.journal_entry.fields.reference_no') }}</div>
                    <div class="col-span-8 text-slate-700 dark:text-slate-200 break-words">
                      {{ item.reference_no || '-' }}
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-span-12 md:col-span-5 self-start md:px-3">
                <div class="space-y-2">
                  <div class="text-primary text-xs font-semibold uppercase tracking-wide">
                    {{ t('views.journal_entry.field_groups.items') }}
                  </div>
                  <div class="grid grid-cols-12 items-center gap-x-3 gap-y-2 text-xs">
                    <div class="col-span-4 text-slate-500">{{ t('views.journal_entry.fields.chart_of_account') }}</div>
                    <div class="col-span-8 text-slate-700 dark:text-slate-200 break-words">
                      {{ item.account_code || '-' }} - {{ item.account_name || '-' }}
                    </div>
                    <div class="col-span-4 text-slate-500">{{ t('views.journal_entry.fields.item_remarks') }}</div>
                    <div class="col-span-8 text-slate-700 dark:text-slate-200 break-words">
                      {{ item.remarks || '-' }}
                    </div>
                    <div class="col-span-4 text-slate-500">{{ t('views.journal_entry.fields.remarks') }}</div>
                    <div class="col-span-8 text-slate-700 dark:text-slate-200 break-words">
                      {{ item.journal_remarks || '-' }}
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-span-12 md:col-span-2 self-start">
                <div class="space-y-2">
                  <div class="text-primary text-xs font-semibold uppercase tracking-wide">
                    {{ t('views.journal_entry.fields.debit') }}
                  </div>
                  <div
                    :class="[
                      'flex min-h-[84px] items-center justify-end rounded-lg border border-slate-200/60 bg-white px-4 py-3 shadow-sm dark:border-darkmode-400 dark:bg-darkmode-500/20',
                      amountCellClass(item.debit),
                    ]"
                  >
                    <div class="text-right text-lg font-semibold tabular-nums">
                      {{ formatAmountCell(item.debit) }}
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-span-12 md:col-span-2 self-start">
                <div class="space-y-2">
                  <div class="text-primary text-xs font-semibold uppercase tracking-wide">
                    {{ t('views.journal_entry.fields.credit') }}
                  </div>
                  <div
                    :class="[
                      'flex min-h-[84px] items-center justify-end rounded-lg border border-slate-200/60 bg-white px-4 py-3 shadow-sm dark:border-darkmode-400 dark:bg-darkmode-500/20',
                      amountCellClass(item.credit),
                    ]"
                  >
                    <div class="text-right text-lg font-semibold tabular-nums">
                      {{ formatAmountCell(item.credit) }}
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </template>
      </DataList>
    </div>
  </div>
</template>
