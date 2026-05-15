<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import { DataListFlex } from '@/components/DataList';
import type { DataListEmittedData } from '@/components/DataList/DataList.vue';
import { FormInputDateTime, FormLabel } from '@/components/Base/Form';
import JournalEntryService from '@/services/JournalEntryService';
import type { JournalEntry } from '@/types/models/JournalEntry';
import type { Resource } from '@/types/resources/Resource';
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
  branch_name: string | null;
  account_code: string | null;
  account_name: string | null;
  debit: number;
  credit: number;
  remarks: string | null;
  journal_remarks: string | null;
}

const { t } = useI18n();
const router = useRouter();
const journalEntryService = new JournalEntryService();
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

const journalEntryLists = ref<Resource<Array<JournalEntry>> | null>({
  data: [],
});

const isUserLocationSelected = computed(() => selectedUserLocationStore.isUserLocationSelected);
const selectedUserLocation = computed(() => selectedUserLocationStore.selectedUserLocation);

const entryDetails = computed<JournalEntryDetailRow[]>(() =>
  (journalEntryLists.value?.data ?? []).flatMap((journalEntry) =>
    journalEntry.items.map((item) => ({
      id: item.id,
      sequence: item.sequence,
      journal_code: journalEntry.code,
      date: journalEntry.date,
      reference_no: journalEntry.reference_no,
      branch_name: journalEntry.branch?.name ?? null,
      account_code: item.chart_of_account?.code ?? null,
      account_name: item.chart_of_account?.name ?? null,
      debit: item.debit,
      credit: item.credit,
      remarks: item.remarks,
      journal_remarks: journalEntry.remarks,
    })),
  ),
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

  await getEntryDetails(true);
});

const getEntryDetails = async (refresh: boolean) => {
  emits('loading-state', true);

  const result: ServiceResponse<Resource<Array<JournalEntry>> | null> = await journalEntryService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    branch_id: selectedUserLocation.value.branch.id,
    search: filters.value.search || undefined,
    start_date: filters.value.start_date || undefined,
    end_date: filters.value.end_date || undefined,
    refresh,
    limit: 1000,
  });

  if (result.success && result.data) {
    journalEntryLists.value = result.data;
    showAlertPlaceholder('hidden', '', null);
  } else {
    showAlertPlaceholder('danger', '', result.errors as Record<string, Array<string>>);
  }

  emits('loading-state', false);
};

const handleDataListChange = async (data: DataListEmittedData) => {
  filters.value.search = data.search.text;
  await getEntryDetails(false);
};

const handleDateFilterChange = async () => {
  await getEntryDetails(true);
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

      <DataListFlex
        :enable-search="true"
        :can-print="false"
        :can-export="false"
        :rows="entryDetails"
        row-class="bg-white dark:bg-darkmode-600"
        :pagination="null"
        @dataListChanged="handleDataListChange"
      >
        <template #row="{ item }">
          <div class="col-span-12 md:col-span-4 self-start">
            <div class="space-y-2">
              <div class="text-primary text-xs font-semibold uppercase tracking-wide">
                {{ t('views.journal_entry.page_title') }}
              </div>
              <div class="grid grid-cols-12 items-center gap-x-3 gap-y-2 text-xs">
                <div class="col-span-4 text-slate-500">{{ t('views.journal_entry.fields.code') }}</div>
                <div class="col-span-8 text-slate-700 dark:text-slate-200 break-words">
                  {{ (item as JournalEntryDetailRow).journal_code }}
                </div>
                <div class="col-span-4 text-slate-500">{{ t('views.journal_entry.fields.item') }}</div>
                <div class="col-span-8 text-slate-700 dark:text-slate-200">
                  #{{ (item as JournalEntryDetailRow).sequence }}
                </div>
                <div class="col-span-4 text-slate-500">{{ t('views.journal_entry.fields.date') }}</div>
                <div class="col-span-8 text-slate-700 dark:text-slate-200 break-words">
                  {{ (item as JournalEntryDetailRow).date ? formatDate((item as JournalEntryDetailRow).date, 'DD-MMM-YYYY HH:mm:ss') : '-' }}
                </div>
                <div class="col-span-4 text-slate-500">{{ t('views.journal_entry.fields.reference_no') }}</div>
                <div class="col-span-8 text-slate-700 dark:text-slate-200 break-words">
                  {{ (item as JournalEntryDetailRow).reference_no || '-' }}
                </div>
                <div class="col-span-4 text-slate-500">{{ t('views.journal_entry.fields.branch') }}</div>
                <div class="col-span-8 text-slate-700 dark:text-slate-200 break-words">
                  {{ (item as JournalEntryDetailRow).branch_name || '-' }}
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
                  {{ (item as JournalEntryDetailRow).account_code || '-' }} - {{ (item as JournalEntryDetailRow).account_name || '-' }}
                </div>
                <div class="col-span-4 text-slate-500">{{ t('views.journal_entry.fields.item_remarks') }}</div>
                <div class="col-span-8 text-slate-700 dark:text-slate-200 break-words">
                  {{ (item as JournalEntryDetailRow).remarks || '-' }}
                </div>
                <div class="col-span-4 text-slate-500">{{ t('views.journal_entry.fields.remarks') }}</div>
                <div class="col-span-8 text-slate-700 dark:text-slate-200 break-words">
                  {{ (item as JournalEntryDetailRow).journal_remarks || '-' }}
                </div>
              </div>
            </div>
          </div>

          <div class="col-span-12 md:col-span-3 self-start">
            <div class="space-y-2">
              <div class="text-primary text-xs font-semibold uppercase tracking-wide">
                {{ t('views.journal_entry.field_groups.summary') }}
              </div>
              <div class="grid grid-cols-12 items-center gap-x-3 gap-y-2 text-xs">
                <div class="col-span-6 text-slate-500">{{ t('views.journal_entry.fields.debit') }}</div>
                <div class="col-span-6 text-right text-slate-700 dark:text-slate-200">
                  {{ formatCurrency((item as JournalEntryDetailRow).debit) }}
                </div>
                <div class="col-span-6 text-slate-500">{{ t('views.journal_entry.fields.credit') }}</div>
                <div class="col-span-6 text-right text-slate-700 dark:text-slate-200">
                  {{ formatCurrency((item as JournalEntryDetailRow).credit) }}
                </div>
              </div>
            </div>
          </div>
        </template>
      </DataListFlex>
    </div>
  </div>
</template>
