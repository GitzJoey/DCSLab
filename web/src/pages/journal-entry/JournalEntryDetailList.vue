<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import Button from '@/components/Base/Button';
import Lucide from '@/components/Base/Lucide';
import { FormInput, FormInputDateTime, FormLabel } from '@/components/Base/Form';
import Table from '@/components/Base/Table';
import JournalEntryService from '@/services/JournalEntryService';
import { JournalEntry } from '@/types/models/JournalEntry';
import { Resource } from '@/types/resources/Resource';
import { ServiceResponse } from '@/types/services/ServiceResponse';
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

const handleSearch = async () => {
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
      <div class="grid grid-cols-12 gap-4 gap-y-3 mb-3">
        <div class="col-span-12 lg:col-span-4 md:col-span-12">
          <FormLabel>
            {{ t('components.buttons.search') }}
          </FormLabel>
          <FormInput
            v-model="filters.search"
            :placeholder="t('components.buttons.search')"
            @keyup.enter="handleSearch"
          />
        </div>
        <div class="col-span-12 lg:col-span-3 md:col-span-6">
          <FormLabel>
            {{ t('views.journal_entry.fields.start_date') }}
          </FormLabel>
          <FormInputDateTime
            v-model="filters.start_date"
            :placeholder="t('views.journal_entry.fields.start_date')"
          />
        </div>
        <div class="col-span-12 lg:col-span-3 md:col-span-6">
          <FormLabel>
            {{ t('views.journal_entry.fields.end_date') }}
          </FormLabel>
          <FormInputDateTime
            v-model="filters.end_date"
            :placeholder="t('views.journal_entry.fields.end_date')"
          />
        </div>
        <div class="col-span-12 lg:col-span-2 md:col-span-12 flex items-end">
          <Button variant="primary" class="w-full shadow-md" @click="handleSearch">
            <Lucide icon="Search" class="w-4 h-4" />
            &nbsp;{{ t('components.buttons.search') }}
          </Button>
        </div>
      </div>

      <Table class="mt-5" :hover="true">
        <Table.Thead variant="light">
          <Table.Tr>
            <Table.Th>{{ t('views.journal_entry.fields.code') }}</Table.Th>
            <Table.Th>{{ t('views.journal_entry.fields.date') }}</Table.Th>
            <Table.Th>{{ t('views.journal_entry.fields.reference_no') }}</Table.Th>
            <Table.Th>{{ t('views.journal_entry.fields.branch') }}</Table.Th>
            <Table.Th>{{ t('views.journal_entry.fields.chart_of_account') }}</Table.Th>
            <Table.Th class="text-right">{{ t('views.journal_entry.fields.debit') }}</Table.Th>
            <Table.Th class="text-right">{{ t('views.journal_entry.fields.credit') }}</Table.Th>
            <Table.Th>{{ t('views.journal_entry.fields.item_remarks') }}</Table.Th>
          </Table.Tr>
        </Table.Thead>
        <Table.Tbody>
          <template v-if="entryDetails.length === 0">
            <Table.Tr class="intro-x">
              <Table.Td colspan="8">
                <div class="flex justify-center italic">
                  {{ t('components.data-list.data_not_found') }}
                </div>
              </Table.Td>
            </Table.Tr>
          </template>
          <template v-else>
            <Table.Tr v-for="item in entryDetails" :key="item.id" class="intro-x">
              <Table.Td class="font-medium">
                {{ item.journal_code }}
                <div class="text-slate-500 text-xs">
                  #{{ item.sequence }}
                </div>
              </Table.Td>
              <Table.Td>{{ item.date ? formatDate(item.date, 'DD-MMM-YYYY HH:mm:ss') : '-' }}</Table.Td>
              <Table.Td>{{ item.reference_no || '-' }}</Table.Td>
              <Table.Td>{{ item.branch_name || '-' }}</Table.Td>
              <Table.Td>
                {{ item.account_code || '-' }} - {{ item.account_name || '-' }}
              </Table.Td>
              <Table.Td class="text-right">{{ formatCurrency(item.debit) }}</Table.Td>
              <Table.Td class="text-right">{{ formatCurrency(item.credit) }}</Table.Td>
              <Table.Td>
                {{ item.remarks || item.journal_remarks || '-' }}
              </Table.Td>
            </Table.Tr>
          </template>
        </Table.Tbody>
      </Table>
    </div>
  </div>
</template>
