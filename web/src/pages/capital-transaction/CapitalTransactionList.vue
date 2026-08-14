<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import DataList from '@/components/DataList';
import { useI18n } from 'vue-i18n';
import Button from '@/components/Base/Button';
import Lucide from '@/components/Base/Lucide';
import Table from '@/components/Base/Table';
import CapitalTransactionService from '@/services/CapitalTransactionService';
import { type CapitalTransaction } from '@/types/models/CapitalTransaction';
import { type Collection } from '@/types/resources/Collection';
import { type DataListEmittedData } from '@/components/DataList/DataList.vue';
import { type ServiceResponse } from '@/types/services/ServiceResponse';
import { useRouter } from 'vue-router';
import { Dialog } from '@/components/Base/Headless';
import { useSelectedUserLocationStore } from '@/stores/selected-user-location';
import { ErrorCode } from '@/types/enums/ErrorCode';
import { ViewMode } from '@/types/enums/ViewMode';
import { type DropDownOption } from '@/types/models/DropDownOption';
import { type NotificationData } from '@/types/models/NotificationData';
import { type AlertPlaceholderProps } from '@/components/AlertPlaceholder/AlertPlaceholder.vue';
import { formatCurrency, formatDate } from '@/utils/helper';
import { type CapitalTransactionReadAnyPaginateRequest } from '@/types/services/capital-transaction/CapitalTransactionRequest';

const { t } = useI18n();
const router = useRouter();
const capitalTransactionService = new CapitalTransactionService();
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
const expandDetail = ref<number | null>(null);
const capitalTransactionLists = ref<Collection<Array<CapitalTransaction>> | null>({
  data: [],
  meta: {
    current_page: 0,
    from: null,
    last_page: 0,
    path: '',
    per_page: 0,
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
const capitalTransactionTypesDDL = ref<Array<DropDownOption> | null>(null);

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

  await Promise.all([getCapitalTransactionTypesDDL(), getCapitalTransactions('', true, 1, 10)]);
});

const getCapitalTransactions = async (search: string, refresh: boolean, page: number, per_page: number) => {
  emits('loading-state', true);

  const requestParams: CapitalTransactionReadAnyPaginateRequest = {
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    branch_id: selectedUserLocation.value.branch.id,
    search,
    investor_id: undefined,
    cash_account_id: undefined,
    type: undefined,
    refresh,
    page,
    per_page,
  };

  const result: ServiceResponse<Collection<Array<CapitalTransaction>> | null> =
    await capitalTransactionService.readAnyPaginate(requestParams);

  if (result.success && result.data) {
    capitalTransactionLists.value = result.data;
    showAlertPlaceholder('hidden', '', null);
  } else {
    showAlertPlaceholder('danger', '', result.errors as Record<string, Array<string>>);
  }

  emits('loading-state', false);
};

const onDataListChanged = async (data: DataListEmittedData) => {
  await getCapitalTransactions(data.search.text, false, data.pagination.page, data.pagination.per_page);
};

const getCapitalTransactionTypesDDL = async () => {
  capitalTransactionTypesDDL.value = await capitalTransactionService.getTypes();
};

const getTypeLabel = (code: number | string): string => {
  const type = (capitalTransactionTypesDDL.value ?? []).find((item) => item.code === code);

  return type ? t(type.name) : String(code);
};

const viewSelected = (idx: number) => {
  if (expandDetail.value === idx) {
    expandDetail.value = null;
  } else {
    expandDetail.value = idx;
  }
};

const editSelected = (itemIdx: number) => {
  if (!capitalTransactionLists.value) return;

  const ulid = capitalTransactionLists.value.data[itemIdx].ulid;

  router.push({
    name: 'side-menu-finance-capital-transaction-edit',
    params: { ulid },
  });
};

const deleteSelected = (itemIdx: number) => {
  if (!capitalTransactionLists.value) return;

  deleteUlid.value = capitalTransactionLists.value.data[itemIdx].ulid;
  deleteModalShow.value = true;
};

const confirmDelete = async () => {
  deleteModalShow.value = false;
  emits('loading-state', true);

  const result: ServiceResponse<boolean | null> = await capitalTransactionService.delete(deleteUlid.value);

  if (result.success) {
    emits('update-profile');
    await getCapitalTransactions('', true, 1, 10);
    showNotification(
      t('views.capital_transaction.alert.delete_capital_transaction.title'),
      t('views.capital_transaction.alert.delete_capital_transaction.content'),
    );
  } else {
    showAlertPlaceholder('danger', '', result.errors as Record<string, Array<string>>);
  }

  emits('loading-state', false);
};

const showNotification = (title: string, content: string) => {
  const notification: NotificationData = {
    title,
    content,
  };

  emits('show-notification', notification);
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
    :title="t('views.capital_transaction.table.title')"
    :enable-search="true"
    :can-print="true"
    :can-export="true"
    :pagination="capitalTransactionLists ? capitalTransactionLists.meta : null"
    @dataListChanged="onDataListChanged"
  >
    <template #content>
      <Table class="mt-5" :hover="true">
        <Table.Thead variant="light">
          <Table.Tr>
            <Table.Th class="whitespace-nowrap">
              {{ t('views.capital_transaction.table.cols.code') }}
            </Table.Th>
            <Table.Th class="whitespace-nowrap">
              {{ t('views.capital_transaction.table.cols.date') }}
            </Table.Th>
            <Table.Th class="whitespace-nowrap">
              {{ t('views.capital_transaction.table.cols.investor') }}
            </Table.Th>
            <Table.Th class="whitespace-nowrap">
              {{ t('views.capital_transaction.table.cols.type') }}
            </Table.Th>
            <Table.Th class="whitespace-nowrap">
              {{ t('views.capital_transaction.table.cols.cash_account') }}
            </Table.Th>
            <Table.Th class="whitespace-nowrap text-right">
              {{ t('views.capital_transaction.table.cols.amount') }}
            </Table.Th>
            <Table.Th class="whitespace-nowrap"></Table.Th>
          </Table.Tr>
        </Table.Thead>
        <Table.Tbody v-if="capitalTransactionLists !== null">
          <template v-if="capitalTransactionLists.data.length === 0">
            <Table.Tr class="intro-x">
              <Table.Td colspan="7">
                <div class="flex justify-center italic">
                  {{ t('components.data-list.data_not_found') }}
                </div>
              </Table.Td>
            </Table.Tr>
          </template>
          <template v-for="(item, itemIdx) in capitalTransactionLists.data" :key="item.ulid">
            <Table.Tr class="intro-x">
              <Table.Td>{{ item.code }}</Table.Td>
              <Table.Td>{{ item.date ? formatDate(item.date, 'DD-MMM-YYYY') : '-' }}</Table.Td>
              <Table.Td>{{ item.investor.name }}</Table.Td>
              <Table.Td>{{ getTypeLabel(item.type) }}</Table.Td>
              <Table.Td>{{ item.cash_account.name }}</Table.Td>
              <Table.Td class="text-right">{{ formatCurrency(item.amount) }}</Table.Td>
              <Table.Td>
                <div class="flex justify-end gap-1">
                  <Button variant="outline-secondary" @click="viewSelected(itemIdx)">
                    <Lucide icon="Info" class="w-4 h-4" />
                  </Button>
                  <Button variant="outline-secondary" @click="editSelected(itemIdx)">
                    <Lucide icon="Pen" class="w-4 h-4" />
                  </Button>
                  <Button variant="outline-secondary" @click="deleteSelected(itemIdx)">
                    <Lucide icon="Trash2" class="w-4 h-4 text-danger" />
                  </Button>
                </div>
              </Table.Td>
            </Table.Tr>
            <Table.Tr
              :class="{
                'intro-x': true,
                'hidden transition-all': expandDetail !== itemIdx,
              }"
            >
              <Table.Td colspan="7">
                <div class="flex flex-row">
                  <div class="ml-5 w-48 text-right pr-5">
                    {{ t('views.capital_transaction.fields.branch') }}
                  </div>
                  <div class="flex-1">{{ item.branch.name }}</div>
                </div>
                <div class="flex flex-row">
                  <div class="ml-5 w-48 text-right pr-5">
                    {{ t('views.capital_transaction.fields.remarks') }}
                  </div>
                  <div class="flex-1">{{ item.remarks || '-' }}</div>
                </div>
              </Table.Td>
            </Table.Tr>
          </template>
        </Table.Tbody>
      </Table>
      <Dialog
        :open="deleteModalShow"
        @close="
          () => {
            deleteModalShow = false;
          }
        "
      >
        <Dialog.Panel>
          <div class="p-5 text-center">
            <Lucide icon="XCircle" class="w-16 h-16 mx-auto mt-3 text-danger" />
            <div class="mt-5 text-3xl">
              {{ t('components.delete-modal.title') }}
            </div>
            <div class="mt-2 text-slate-500">
              {{ t('components.delete-modal.desc_1') }}
              <br />
              {{ t('components.delete-modal.desc_2') }}
            </div>
          </div>
          <div class="px-5 pb-8 text-center">
            <Button
              type="button"
              variant="outline-secondary"
              class="w-24 mr-1"
              @click="
                () => {
                  deleteModalShow = false;
                }
              "
            >
              {{ t('components.buttons.cancel') }}
            </Button>
            <Button type="button" variant="danger" class="w-24" @click="confirmDelete">
              {{ t('components.buttons.delete') }}
            </Button>
          </div>
        </Dialog.Panel>
      </Dialog>
    </template>
  </DataList>
</template>
