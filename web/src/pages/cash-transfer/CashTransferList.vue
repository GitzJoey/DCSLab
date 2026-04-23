<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import DataList from '@/components/DataList';
import { useI18n } from 'vue-i18n';
import Button from '@/components/Base/Button';
import Lucide from '@/components/Base/Lucide';
import Table from '@/components/Base/Table';
import CashTransferService from '@/services/CashTransferService';
import { type CashTransfer } from '@/types/models/CashTransfer';
import { type Collection } from '@/types/resources/Collection';
import { type DataListEmittedData } from '@/components/DataList/DataList.vue';
import { type ServiceResponse } from '@/types/services/ServiceResponse';
import { useRouter } from 'vue-router';
import { Dialog } from '@/components/Base/Headless';
import { useSelectedUserLocationStore } from '@/stores/selected-user-location';
import { ErrorCode } from '@/types/enums/ErrorCode';
import { ViewMode } from '@/types/enums/ViewMode';
import { type NotificationData } from '@/types/models/NotificationData';
import { type AlertPlaceholderProps } from '@/components/AlertPlaceholder/AlertPlaceholder.vue';
import { formatCurrency, formatDate } from '@/utils/helper';
import { type CashTransferReadAnyPaginateRequest } from '@/types/services/cash-transfer/CashTransferRequest';

const { t } = useI18n();
const router = useRouter();
const cashTransferService = new CashTransferService();
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
const cashTransferLists = ref<Collection<Array<CashTransfer>> | null>({
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

  await getCashTransfers('', true, 1, 10);
});

const getCashTransfers = async (search: string, refresh: boolean, page: number, per_page: number) => {
  emits('loading-state', true);

  const requestParams: CashTransferReadAnyPaginateRequest = {
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    branch_id: selectedUserLocation.value.branch.id,
    search,
    refresh,
    page,
    per_page,
  };

  const result: ServiceResponse<Collection<Array<CashTransfer>> | null> =
    await cashTransferService.readAnyPaginate(requestParams);

  if (result.success && result.data) {
    cashTransferLists.value = result.data;
    showAlertPlaceholder('hidden', '', null);
  } else {
    showAlertPlaceholder('danger', '', result.errors as Record<string, Array<string>>);
  }

  emits('loading-state', false);
};

const onDataListChanged = async (data: DataListEmittedData) => {
  await getCashTransfers(data.search.text, false, data.pagination.page, data.pagination.per_page);
};

const viewSelected = (idx: number) => {
  if (expandDetail.value === idx) {
    expandDetail.value = null;
  } else {
    expandDetail.value = idx;
  }
};

const editSelected = (itemIdx: number) => {
  if (!cashTransferLists.value) return;

  const ulid = cashTransferLists.value.data[itemIdx].ulid;

  router.push({
    name: 'side-menu-finance-cash-transfer-edit',
    params: { ulid },
  });
};

const deleteSelected = (itemIdx: number) => {
  if (!cashTransferLists.value) return;

  deleteUlid.value = cashTransferLists.value.data[itemIdx].ulid;
  deleteModalShow.value = true;
};

const confirmDelete = async () => {
  deleteModalShow.value = false;
  emits('loading-state', true);

  const result: ServiceResponse<boolean | null> = await cashTransferService.delete(deleteUlid.value);

  if (result.success) {
    emits('update-profile');
    await getCashTransfers('', true, 1, 10);
    showNotification(
      t('views.cash_transfer.alert.delete_cash_transfer.title'),
      t('views.cash_transfer.alert.delete_cash_transfer.content'),
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
    :title="t('views.cash_transfer.table.title')"
    :enable-search="true"
    :can-print="true"
    :can-export="true"
    :pagination="cashTransferLists ? cashTransferLists.meta : null"
    @dataListChanged="onDataListChanged"
  >
    <template #content>
      <Table class="mt-5" :hover="true">
        <Table.Thead variant="light">
          <Table.Tr>
            <Table.Th class="whitespace-nowrap">
              {{ t('views.cash_transfer.table.cols.code') }}
            </Table.Th>
            <Table.Th class="whitespace-nowrap">
              {{ t('views.cash_transfer.table.cols.date') }}
            </Table.Th>
            <Table.Th class="whitespace-nowrap">
              {{ t('views.cash_transfer.table.cols.source_cash_account') }}
            </Table.Th>
            <Table.Th class="whitespace-nowrap">
              {{ t('views.cash_transfer.table.cols.destination_cash_account') }}
            </Table.Th>
            <Table.Th class="whitespace-nowrap text-right">
              {{ t('views.cash_transfer.table.cols.amount') }}
            </Table.Th>
            <Table.Th class="whitespace-nowrap"></Table.Th>
          </Table.Tr>
        </Table.Thead>
        <Table.Tbody v-if="cashTransferLists !== null">
          <template v-if="cashTransferLists.data.length === 0">
            <Table.Tr class="intro-x">
              <Table.Td colspan="6">
                <div class="flex justify-center italic">
                  {{ t('components.data-list.data_not_found') }}
                </div>
              </Table.Td>
            </Table.Tr>
          </template>
          <template v-for="(item, itemIdx) in cashTransferLists.data" :key="item.ulid">
            <Table.Tr class="intro-x">
              <Table.Td>{{ item.code }}</Table.Td>
              <Table.Td>{{ item.date ? formatDate(item.date, 'DD-MMM-YYYY') : '-' }}</Table.Td>
              <Table.Td>{{ item.source_cash_account.name }}</Table.Td>
              <Table.Td>{{ item.destination_cash_account.name }}</Table.Td>
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
              <Table.Td colspan="6">
                <div class="flex flex-row">
                  <div class="ml-5 w-48 text-right pr-5">
                    {{ t('views.cash_transfer.fields.branch') }}
                  </div>
                  <div class="flex-1">{{ item.branch.name }}</div>
                </div>
                <div class="flex flex-row">
                  <div class="ml-5 w-48 text-right pr-5">
                    {{ t('views.cash_transfer.fields.remarks') }}
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

