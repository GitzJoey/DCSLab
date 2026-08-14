<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import DataList from '@/components/DataList';
import { useI18n } from 'vue-i18n';
import Button from '@/components/Base/Button';
import Lucide from '@/components/Base/Lucide';
import Table from '@/components/Base/Table';
import CapitalOpeningService from '@/services/CapitalOpeningService';
import { type CapitalOpening } from '@/types/models/CapitalOpening';
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
import { type CapitalOpeningReadAnyPaginateRequest } from '@/types/services/capital-opening/CapitalOpeningRequest';

const { t } = useI18n();
const router = useRouter();
const capitalOpeningService = new CapitalOpeningService();
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
const capitalOpeningLists = ref<Collection<Array<CapitalOpening>> | null>({
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

  await getCapitalOpenings('', true, 1, 10);
});

const getCapitalOpenings = async (search: string, refresh: boolean, page: number, per_page: number) => {
  emits('loading-state', true);

  const requestParams: CapitalOpeningReadAnyPaginateRequest = {
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    branch_id: selectedUserLocation.value.branch.id,
    search,
    investor_id: undefined,
    cash_account_id: undefined,
    refresh,
    page,
    per_page,
  };

  const result: ServiceResponse<Collection<Array<CapitalOpening>> | null> =
    await capitalOpeningService.readAnyPaginate(requestParams);

  if (result.success && result.data) {
    capitalOpeningLists.value = result.data;
    showAlertPlaceholder('hidden', '', null);
  } else {
    showAlertPlaceholder('danger', '', result.errors as Record<string, Array<string>>);
  }

  emits('loading-state', false);
};

const onDataListChanged = async (data: DataListEmittedData) => {
  await getCapitalOpenings(data.search.text, false, data.pagination.page, data.pagination.per_page);
};

const viewSelected = (idx: number) => {
  if (expandDetail.value === idx) {
    expandDetail.value = null;
  } else {
    expandDetail.value = idx;
  }
};

const editSelected = (itemIdx: number) => {
  if (!capitalOpeningLists.value) return;

  const ulid = capitalOpeningLists.value.data[itemIdx].ulid;

  router.push({
    name: 'side-menu-finance-capital-opening-edit',
    params: { ulid },
  });
};

const deleteSelected = (itemIdx: number) => {
  if (!capitalOpeningLists.value) return;

  deleteUlid.value = capitalOpeningLists.value.data[itemIdx].ulid;
  deleteModalShow.value = true;
};

const confirmDelete = async () => {
  deleteModalShow.value = false;
  emits('loading-state', true);

  const result: ServiceResponse<boolean | null> = await capitalOpeningService.delete(deleteUlid.value);

  if (result.success) {
    emits('update-profile');
    await getCapitalOpenings('', true, 1, 10);
    showNotification(
      t('views.capital_opening.alert.delete_capital_opening.title'),
      t('views.capital_opening.alert.delete_capital_opening.content'),
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
    :title="t('views.capital_opening.table.title')"
    :enable-search="true"
    :can-print="true"
    :can-export="true"
    :pagination="capitalOpeningLists ? capitalOpeningLists.meta : null"
    @dataListChanged="onDataListChanged"
  >
    <template #content>
      <Table class="mt-5" :hover="true">
        <Table.Thead variant="light">
          <Table.Tr>
            <Table.Th class="whitespace-nowrap">
              {{ t('views.capital_opening.table.cols.code') }}
            </Table.Th>
            <Table.Th class="whitespace-nowrap">
              {{ t('views.capital_opening.table.cols.date') }}
            </Table.Th>
            <Table.Th class="whitespace-nowrap">
              {{ t('views.capital_opening.table.cols.investor') }}
            </Table.Th>
            <Table.Th class="whitespace-nowrap">
              {{ t('views.capital_opening.table.cols.cash_account') }}
            </Table.Th>
            <Table.Th class="whitespace-nowrap text-right">
              {{ t('views.capital_opening.table.cols.amount') }}
            </Table.Th>
            <Table.Th class="whitespace-nowrap"></Table.Th>
          </Table.Tr>
        </Table.Thead>
        <Table.Tbody v-if="capitalOpeningLists !== null">
          <template v-if="capitalOpeningLists.data.length === 0">
            <Table.Tr class="intro-x">
              <Table.Td colspan="6">
                <div class="flex justify-center italic">
                  {{ t('components.data-list.data_not_found') }}
                </div>
              </Table.Td>
            </Table.Tr>
          </template>
          <template v-for="(item, itemIdx) in capitalOpeningLists.data" :key="item.ulid">
            <Table.Tr class="intro-x">
              <Table.Td>{{ item.code }}</Table.Td>
              <Table.Td>{{ item.date ? formatDate(item.date, 'DD-MMM-YYYY') : '-' }}</Table.Td>
              <Table.Td>{{ item.investor.name }}</Table.Td>
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
              <Table.Td colspan="6">
                <div class="flex flex-row">
                  <div class="ml-5 w-48 text-right pr-5">
                    {{ t('views.capital_opening.fields.branch') }}
                  </div>
                  <div class="flex-1">{{ item.branch.name }}</div>
                </div>
                <div class="flex flex-row">
                  <div class="ml-5 w-48 text-right pr-5">
                    {{ t('views.capital_opening.fields.remarks') }}
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
