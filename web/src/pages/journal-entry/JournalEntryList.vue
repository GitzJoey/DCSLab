<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import DataList from '@/components/DataList';
import { useI18n } from 'vue-i18n';
import Button from '@/components/Base/Button';
import Lucide from '@/components/Base/Lucide';
import Table from '@/components/Base/Table';
import { Dialog } from '@/components/Base/Headless';
import { FormInputDateTime, FormLabel } from '@/components/Base/Form';
import { useRouter } from 'vue-router';
import JournalEntryService from '@/services/JournalEntryService';
import { JournalEntry } from '@/types/models/JournalEntry';
import { Resource } from '@/types/resources/Resource';
import { ServiceResponse } from '@/types/services/ServiceResponse';
import { ViewMode } from '@/types/enums/ViewMode';
import { NotificationData } from '@/types/models/NotificationData';
import type { AlertPlaceholderProps } from '@/components/AlertPlaceholder/AlertPlaceholder.vue';
import { convertErrorTypeToAlertListType, formatCurrency, formatDate } from '@/utils/helper';
import { useSelectedUserLocationStore } from '@/stores/selected-user-location';
import { ErrorCode } from '@/types/enums/ErrorCode';

const { t } = useI18n();
const router = useRouter();
const journalEntryService = new JournalEntryService();
const selectedUserLocationStore = useSelectedUserLocationStore();

const emits = defineEmits([
  'mode-state',
  'loading-state',
  'update-profile',
  'show-alertplaceholder',
  'show-notification',
]);

const deleteUlid = ref<string>('');
const deleteModalShow = ref<boolean>(false);
const expandDetail = ref<string | number | null>(null);
const filters = ref<{
  start_date: string | null;
  end_date: string | null;
}>({
  start_date: null,
  end_date: null,
});
const journalEntryLists = ref<Resource<Array<JournalEntry>> | null>({
  data: [],
});

const isUserLocationSelected = computed(() => selectedUserLocationStore.isUserLocationSelected);
const selectedUserLocation = computed(() => selectedUserLocationStore.selectedUserLocation);

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

  await getJournalEntries(true);
});

const getJournalEntries = async (refresh: boolean) => {
  emits('loading-state', true);

  const result: ServiceResponse<Resource<Array<JournalEntry>> | null> = await journalEntryService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    branch_id: selectedUserLocation.value.branch.id,
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

const onDataListChanged = async () => {
  await getJournalEntries(false);
};

const handleFilter = async () => {
  await getJournalEntries(true);
};

const viewSelected = (itemId: string | number) => {
  expandDetail.value = expandDetail.value === itemId ? null : itemId;
};

const editSelected = (item: JournalEntry) => {
  router.push({
    name: 'side-menu-journal-entry-edit',
    params: { ulid: item.ulid },
  });
};

const deleteSelected = (item: JournalEntry) => {
  deleteUlid.value = item.ulid;
  deleteModalShow.value = true;
};

const confirmDelete = async () => {
  deleteModalShow.value = false;
  emits('loading-state', true);

  const result = await journalEntryService.delete(deleteUlid.value);

  if (result.success) {
    emits('update-profile');
    await getJournalEntries(true);
    showNotification(
      t('views.journal_entry.alert.delete_journal_entry.title'),
      t('views.journal_entry.alert.delete_journal_entry.content'),
    );
  } else {
    const alertList = result.errors ?? convertErrorTypeToAlertListType({ message: result.message });
    showAlertPlaceholder('danger', result.message ?? '', alertList);
  }

  emits('loading-state', false);
};

const showNotification = (pTitle: string, pContent: string) => {
  const n: NotificationData = {
    title: pTitle,
    content: pContent,
  };

  emits('show-notification', n);
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
  <DataList
    :title="t('views.journal_entry.table.title')"
    :enable-search="false"
    :can-print="true"
    :can-export="true"
    :pagination="null"
    @dataListChanged="onDataListChanged"
  >
    <template #toolbar-actions>
      <div class="grid grid-cols-12 gap-3">
        <div class="col-span-12 md:col-span-4">
          <FormLabel>
            {{ t('views.journal_entry.fields.start_date') }}
          </FormLabel>
          <FormInputDateTime
            v-model="filters.start_date"
            :placeholder="t('views.journal_entry.fields.start_date')"
          />
        </div>
        <div class="col-span-12 md:col-span-4">
          <FormLabel>
            {{ t('views.journal_entry.fields.end_date') }}
          </FormLabel>
          <FormInputDateTime
            v-model="filters.end_date"
            :placeholder="t('views.journal_entry.fields.end_date')"
          />
        </div>
        <div class="col-span-12 md:col-span-4 flex items-end">
          <Button variant="primary" @click="handleFilter">
            <Lucide icon="Search" class="mr-1 h-4 w-4" />
            {{ t('components.buttons.search') }}
          </Button>
        </div>
      </div>
    </template>
    <template #content>
      <Table class="mt-5" :hover="true">
        <Table.Thead variant="light">
          <Table.Tr>
            <Table.Th>{{ t('views.journal_entry.fields.code') }}</Table.Th>
            <Table.Th>{{ t('views.journal_entry.fields.date') }}</Table.Th>
            <Table.Th>{{ t('views.journal_entry.fields.reference_no') }}</Table.Th>
            <Table.Th class="text-right">{{ t('views.journal_entry.fields.total_debit') }}</Table.Th>
            <Table.Th class="text-right">{{ t('views.journal_entry.fields.total_credit') }}</Table.Th>
            <Table.Th></Table.Th>
          </Table.Tr>
        </Table.Thead>
        <Table.Tbody v-if="journalEntryLists !== null">
          <template v-if="journalEntryLists.data.length == 0">
            <Table.Tr class="intro-x">
              <Table.Td colspan="6">
                <div class="flex justify-center italic">
                  {{ t('components.data-list.data_not_found') }}
                </div>
              </Table.Td>
            </Table.Tr>
          </template>
          <template v-else>
            <template v-for="item in journalEntryLists.data" :key="item.id">
              <Table.Tr class="intro-x">
                <Table.Td class="font-medium">{{ item.code }}</Table.Td>
                <Table.Td>{{ item.date ? formatDate(item.date, 'DD-MMM-YYYY HH:mm:ss') : '-' }}</Table.Td>
                <Table.Td>{{ item.reference_no || '-' }}</Table.Td>
                <Table.Td class="text-right">{{ formatCurrency(item.total_debit) }}</Table.Td>
                <Table.Td class="text-right">{{ formatCurrency(item.total_credit) }}</Table.Td>
                <Table.Td>
                  <div class="flex items-center justify-end gap-2">
                    <Button variant="outline-secondary" @click="viewSelected(item.id)">
                      <Lucide icon="Eye" class="h-4 w-4" />
                    </Button>
                    <Button variant="outline-primary" @click="editSelected(item)">
                      <Lucide icon="Pencil" class="h-4 w-4" />
                    </Button>
                    <Button variant="outline-danger" @click="deleteSelected(item)">
                      <Lucide icon="Trash2" class="h-4 w-4" />
                    </Button>
                  </div>
                </Table.Td>
              </Table.Tr>
              <Table.Tr v-if="expandDetail === item.id">
                <Table.Td colspan="6" class="bg-slate-50 dark:bg-darkmode-600/20">
                  <div class="space-y-3 p-2">
                    <div class="grid grid-cols-12 gap-4 text-sm">
                      <div class="col-span-12 lg:col-span-3">
                        <span class="font-medium">{{ t('views.journal_entry.fields.branch') }}:</span>
                        {{ item.branch?.name ?? '-' }}
                      </div>
                      <div class="col-span-12 lg:col-span-3">
                        <span class="font-medium">{{ t('views.journal_entry.fields.source_type') }}:</span>
                        {{ item.source_type ?? '-' }}
                      </div>
                      <div class="col-span-12 lg:col-span-3">
                        <span class="font-medium">{{ t('views.journal_entry.fields.source_id') }}:</span>
                        {{ item.source_id ?? '-' }}
                      </div>
                      <div class="col-span-12 lg:col-span-3">
                        <span class="font-medium">{{ t('views.journal_entry.fields.remarks') }}:</span>
                        {{ item.remarks || '-' }}
                      </div>
                    </div>

                    <Table class="mt-2">
                      <Table.Thead variant="light">
                        <Table.Tr>
                          <Table.Th>#</Table.Th>
                          <Table.Th>{{ t('views.journal_entry.fields.chart_of_account') }}</Table.Th>
                          <Table.Th class="text-right">{{ t('views.journal_entry.fields.debit') }}</Table.Th>
                          <Table.Th class="text-right">{{ t('views.journal_entry.fields.credit') }}</Table.Th>
                          <Table.Th>{{ t('views.journal_entry.fields.item_remarks') }}</Table.Th>
                        </Table.Tr>
                      </Table.Thead>
                      <Table.Tbody>
                        <Table.Tr v-for="journalEntryItem in item.items" :key="journalEntryItem.id">
                          <Table.Td>{{ journalEntryItem.sequence }}</Table.Td>
                          <Table.Td>
                            {{ journalEntryItem.chart_of_account?.code ?? '-' }} - {{ journalEntryItem.chart_of_account?.name ?? '-' }}
                          </Table.Td>
                          <Table.Td class="text-right">{{ formatCurrency(journalEntryItem.debit) }}</Table.Td>
                          <Table.Td class="text-right">{{ formatCurrency(journalEntryItem.credit) }}</Table.Td>
                          <Table.Td>{{ journalEntryItem.remarks || '-' }}</Table.Td>
                        </Table.Tr>
                      </Table.Tbody>
                    </Table>
                  </div>
                </Table.Td>
              </Table.Tr>
            </template>
          </template>
        </Table.Tbody>
      </Table>
    </template>
  </DataList>

  <Dialog :open="deleteModalShow" @close="deleteModalShow = false">
    <Dialog.Panel>
      <div class="p-5 text-center">
        <Lucide icon="AlertTriangle" class="mx-auto mt-3 h-16 w-16 text-warning" />
        <div class="mt-5 text-3xl">{{ t('components.delete-modal.title') }}</div>
        <div class="mt-2 text-slate-500">{{ t('components.delete-modal.content') }}</div>
      </div>
      <div class="px-5 pb-8 text-center">
        <Button variant="outline-secondary" type="button" class="mr-1 w-24" @click="deleteModalShow = false">
          {{ t('components.buttons.cancel') }}
        </Button>
        <Button variant="danger" type="button" class="w-24" @click="confirmDelete">
          {{ t('components.buttons.delete') }}
        </Button>
      </div>
    </Dialog.Panel>
  </Dialog>
</template>
