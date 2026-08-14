<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import DataList from '@/components/DataList';
import Button from '@/components/Base/Button';
import Lucide from '@/components/Base/Lucide';
import Table from '@/components/Base/Table';
import { Dialog } from '@/components/Base/Headless';
import VatProfileService from '@/services/VatProfileService';
import { VatProfile } from '@/types/models/VatProfile';
import { Collection } from '@/types/resources/Collection';
import { DataListEmittedData } from '@/components/DataList/DataList.vue';
import { ServiceResponse } from '@/types/services/ServiceResponse';
import { VatProfileReadAnyPaginateRequest } from '@/types/services/vat_profile/VatProfileRequest';
import { ViewMode } from '@/types/enums/ViewMode';
import { useSelectedUserLocationStore } from '@/stores/selected-user-location';
import { ErrorCode } from '@/types/enums/ErrorCode';
import { NotificationData } from '@/types/models/NotificationData';
import { type AlertPlaceholderProps } from '@/components/AlertPlaceholder/AlertPlaceholder.vue';
import { formatCurrency } from '@/utils/helper';

const { t } = useI18n();
const router = useRouter();
const vatProfileService = new VatProfileService();
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
const vatProfileLists = ref<Collection<Array<VatProfile>> | null>({
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

const formatVatBaseFraction = (item: VatProfile) => {
  return `${item.vat_base_numerator} / ${item.vat_base_denominator}`;
};

onMounted(async () => {
  emits('mode-state', ViewMode.LIST);

  if (!isUserLocationSelected.value) {
    router.push({
      name: 'side-menu-error-code',
      params: { code: ErrorCode.USERLOCATION_REQUIRED },
    });
    return;
  }

  await getVatProfiles('', true, 1, 10);
});

const getVatProfiles = async (search: string, refresh: boolean, page: number, per_page: number) => {
  emits('loading-state', true);

  const searchReq: VatProfileReadAnyPaginateRequest = {
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    search: search,
    include_id: undefined,
    refresh: refresh,
    page: page,
    per_page: per_page,
  };

  const result: ServiceResponse<Collection<Array<VatProfile>> | null> = await vatProfileService.readAnyPaginate(
    searchReq,
  );

  if (result.success && result.data) {
    vatProfileLists.value = result.data;
  } else {
    showAlertPlaceholder('danger', '', result.errors as Record<string, Array<string>>);
  }

  emits('loading-state', false);
};

const onDataListChanged = async (data: DataListEmittedData) => {
  await getVatProfiles(data.search.text, false, data.pagination.page, data.pagination.per_page);
};

const viewSelected = (idx: number) => {
  if (expandDetail.value === idx) {
    expandDetail.value = null;
  } else {
    expandDetail.value = idx;
  }
};

const editSelected = (itemIdx: number) => {
  if (!vatProfileLists.value) return;

  const ulid = vatProfileLists.value.data[itemIdx].ulid;
  router.push({
    name: 'side-menu-product-vat-profile-edit',
    params: { ulid: ulid },
  });
};

const deleteSelected = (itemIdx: number) => {
  if (!vatProfileLists.value) return;

  deleteUlid.value = vatProfileLists.value.data[itemIdx].ulid;
  deleteModalShow.value = true;
};

const confirmDelete = async () => {
  deleteModalShow.value = false;
  emits('loading-state', true);

  const result: ServiceResponse<any> = await vatProfileService.delete(deleteUlid.value);

  if (result.success) {
    emits('update-profile');
    await getVatProfiles('', true, 1, 10);
    showNotification(t('views.vat_profile.alert.delete_vat_profile.title'), t('views.vat_profile.alert.delete_vat_profile.content'));
  } else {
    showAlertPlaceholder(
      'danger',
      t('components.alert_placeholder.title.danger'),
      result.errors as Record<string, Array<string>>,
    );
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
    :title="t('views.vat_profile.table.title')"
    :enable-search="true"
    :can-print="true"
    :can-export="true"
    :pagination="vatProfileLists ? vatProfileLists.meta : null"
    @dataListChanged="onDataListChanged"
  >
    <template #content>
      <Table class="mt-5" :hover="true">
        <Table.Thead variant="light">
          <Table.Tr>
            <Table.Th class="whitespace-nowrap">
              {{ t('views.vat_profile.table.cols.code') }}
            </Table.Th>
            <Table.Th class="whitespace-nowrap">
              {{ t('views.vat_profile.table.cols.name') }}
            </Table.Th>
            <Table.Th class="whitespace-nowrap text-right">
              {{ t('views.vat_profile.table.cols.vat_rate') }}
            </Table.Th>
            <Table.Th class="whitespace-nowrap text-right">
              {{ t('views.vat_profile.table.cols.vat_base_fraction') }}
            </Table.Th>
            <Table.Th class="whitespace-nowrap">
              {{ t('views.vat_profile.table.cols.is_active') }}
            </Table.Th>
            <Table.Th class="whitespace-nowrap"></Table.Th>
          </Table.Tr>
        </Table.Thead>
        <Table.Tbody v-if="vatProfileLists !== null">
          <template v-if="vatProfileLists.data.length == 0">
            <Table.Tr class="intro-x">
              <Table.Td colspan="6">
                <div class="flex justify-center italic">
                  {{ t('components.data-list.data_not_found') }}
                </div>
              </Table.Td>
            </Table.Tr>
          </template>
          <template v-for="(item, itemIdx) in vatProfileLists.data" :key="item.ulid">
            <Table.Tr class="intro-x">
              <Table.Td>{{ item.code }}</Table.Td>
              <Table.Td>{{ item.name }}</Table.Td>
              <Table.Td class="text-right">{{ formatCurrency(item.vat_rate) }}</Table.Td>
              <Table.Td class="text-right">{{ formatVatBaseFraction(item) }}</Table.Td>
              <Table.Td>
                <Lucide v-if="item.is_active" icon="CheckCircle" class="text-success" />
                <Lucide v-else icon="X" class="text-danger" />
              </Table.Td>
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
                  <div class="ml-5 w-48 text-right pr-5 font-medium">
                    {{ t('views.vat_profile.fields.code') }}
                  </div>
                  <div class="flex-1">{{ item.code }}</div>
                </div>
                <div class="flex flex-row mt-1">
                  <div class="ml-5 w-48 text-right pr-5 font-medium">
                    {{ t('views.vat_profile.fields.name') }}
                  </div>
                  <div class="flex-1">{{ item.name }}</div>
                </div>
                <div class="flex flex-row mt-1">
                  <div class="ml-5 w-48 text-right pr-5 font-medium">
                    {{ t('views.vat_profile.fields.vat_rate') }}
                  </div>
                  <div class="flex-1">{{ formatCurrency(item.vat_rate) }}</div>
                </div>
                <div class="flex flex-row mt-1">
                  <div class="ml-5 w-48 text-right pr-5 font-medium">
                    {{ t('views.vat_profile.fields.vat_base_numerator') }}
                  </div>
                  <div class="flex-1">{{ item.vat_base_numerator }}</div>
                </div>
                <div class="flex flex-row mt-1">
                  <div class="ml-5 w-48 text-right pr-5 font-medium">
                    {{ t('views.vat_profile.fields.vat_base_denominator') }}
                  </div>
                  <div class="flex-1">{{ item.vat_base_denominator }}</div>
                </div>
                <div class="flex flex-row mt-1">
                  <div class="ml-5 w-48 text-right pr-5 font-medium">
                    {{ t('views.vat_profile.fields.is_active') }}
                  </div>
                  <div class="flex-1">
                    <span v-if="item.is_active">
                      {{ t('components.dropdown.values.statusDDL.active') }}
                    </span>
                    <span v-else>
                      {{ t('components.dropdown.values.statusDDL.inactive') }}
                    </span>
                  </div>
                </div>
                <div class="flex flex-row mt-1">
                  <div class="ml-5 w-48 text-right pr-5 font-medium">
                    {{ t('views.vat_profile.fields.remarks') }}
                  </div>
                  <div class="flex-1">{{ item.remarks ?? '-' }}</div>
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
