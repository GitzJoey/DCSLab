<script setup lang="ts">
// #region Imports
import { computed, onMounted, ref } from 'vue';
import DataList from '@/components/DataList';
import { useI18n } from 'vue-i18n';
import Button from '@/components/Base/Button';
import Lucide from '@/components/Base/Lucide';
import Table from '@/components/Base/Table';
import { Dialog } from '@/components/Base/Headless';
import { useRouter } from 'vue-router';
import LiabilityCategoryService from '@/services/LiabilityCategoryService';
import { LiabilityCategory } from '@/types/models/LiabilityCategory';
import { Resource } from '@/types/resources/Resource';
import { ServiceResponse } from '@/types/services/ServiceResponse';
import { ViewMode } from '@/types/enums/ViewMode';
import { NotificationData } from '@/types/models/NotificationData';
import type { AlertPlaceholderProps } from '@/components/AlertPlaceholder/AlertPlaceholder.vue';
import { convertErrorTypeToAlertListType } from '@/utils/helper';
import { useSelectedUserLocationStore } from '@/stores/selected-user-location';
import { ErrorCode } from '@/types/enums/ErrorCode';
// #endregion

// #region Declarations
const { t } = useI18n();
const router = useRouter();
const liabilityCategoryService = new LiabilityCategoryService();
const selectedUserLocationStore = useSelectedUserLocationStore();
// #endregion

// #region Props, Emits
const emits = defineEmits([
  'mode-state',
  'loading-state',
  'update-profile',
  'show-alertplaceholder',
  'show-notification',
]);
// #endregion

// #region Refs
const deleteUlid = ref<string>('');
const deleteModalShow = ref<boolean>(false);
const expandDetail = ref<string | null>(null);
const liabilityCategoryLists = ref<Resource<Array<LiabilityCategory>> | null>({
  data: [],
});
// #endregion

// #region Computed
const isUserLocationSelected = computed(() => selectedUserLocationStore.isUserLocationSelected);
const selectedUserLocation = computed(() => selectedUserLocationStore.selectedUserLocation);
// #endregion

// #region Lifecycle Hooks
onMounted(async () => {
  emits('mode-state', ViewMode.LIST);

  if (!isUserLocationSelected.value) {
    router.push({
      name: 'side-menu-error-code',
      params: { code: ErrorCode.USERLOCATION_REQUIRED },
    });
    return;
  }

  await getLiabilityCategories('', true);
});
// #endregion

// #region Methods
const getLiabilityCategories = async (search: string, refresh: boolean) => {
  emits('loading-state', true);

  const result: ServiceResponse<Resource<Array<LiabilityCategory>> | null> = await liabilityCategoryService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    search,
    include_id: undefined,
    refresh,
    limit: 1000,
  });

  if (result.success && result.data) {
    liabilityCategoryLists.value = result.data;
    showAlertPlaceholder('hidden', '', null);
  } else {
    showAlertPlaceholder('danger', '', result.errors as Record<string, Array<string>>);
  }

  emits('loading-state', false);
};

const onDataListChanged = async () => {
  await getLiabilityCategories('', false);
};

const viewSelected = (itemId: string) => {
  if (expandDetail.value === itemId) {
    expandDetail.value = null;
  } else {
    expandDetail.value = itemId;
  }
};

const editSelected = (item: LiabilityCategory) => {
  router.push({
    name: 'side-menu-liability-category-edit',
    params: { ulid: item.ulid },
  });
};

const deleteSelected = (item: LiabilityCategory) => {
  deleteUlid.value = item.ulid;
  deleteModalShow.value = true;
};

const confirmDelete = async () => {
  deleteModalShow.value = false;
  emits('loading-state', true);

  const result: ServiceResponse<boolean | null> = await liabilityCategoryService.delete(deleteUlid.value);

  if (result.success) {
    emits('update-profile');
    await getLiabilityCategories('', true);
    showNotification(
      t('views.liability_category.alert.delete_liability_category.title'),
      t('views.liability_category.alert.delete_liability_category.content'),
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
// #endregion
</script>

<template>
  <DataList
    :title="t('views.liability_category.table.title')"
    :enable-search="false"
    :can-print="true"
    :can-export="true"
    :pagination="null"
    @dataListChanged="onDataListChanged"
  >
    <template #content>
      <Table class="mt-5" :hover="true">
        <Table.Thead variant="light">
          <Table.Tr>
            <Table.Th class="whitespace-nowrap">
              {{ t('views.liability_category.fields.code') }}
            </Table.Th>
            <Table.Th class="whitespace-nowrap">
              {{ t('views.liability_category.fields.name') }}
            </Table.Th>
            <Table.Th class="whitespace-nowrap">
              {{ t('views.liability_category.fields.sequence') }}
            </Table.Th>
            <Table.Th class="whitespace-nowrap"></Table.Th>
          </Table.Tr>
        </Table.Thead>
        <Table.Tbody v-if="liabilityCategoryLists !== null">
          <template v-if="liabilityCategoryLists.data.length == 0">
            <Table.Tr class="intro-x">
              <Table.Td colspan="4">
                <div class="flex justify-center italic">
                  {{ t('components.data-list.data_not_found') }}
                </div>
              </Table.Td>
            </Table.Tr>
          </template>
          <template v-else v-for="item in liabilityCategoryLists.data" :key="item.ulid">
            <Table.Tr class="intro-x">
              <Table.Td>
                <div class="font-medium">{{ item.code }}</div>
              </Table.Td>
              <Table.Td>
                <span>{{ item.name }}</span>
              </Table.Td>
              <Table.Td>{{ item.sequence }}</Table.Td>
              <Table.Td>
                <div class="flex justify-end gap-1">
                  <Button variant="outline-secondary" @click="viewSelected(item.ulid)">
                    <Lucide icon="Info" class="w-4 h-4" />
                  </Button>
                  <Button variant="outline-secondary" @click="editSelected(item as LiabilityCategory)">
                    <Lucide icon="Pen" class="w-4 h-4" />
                  </Button>
                  <Button variant="outline-secondary" @click="deleteSelected(item as LiabilityCategory)">
                    <Lucide icon="Trash2" class="w-4 h-4 text-danger" />
                  </Button>
                </div>
              </Table.Td>
            </Table.Tr>
            <Table.Tr
              :class="{
                'intro-x': true,
                'hidden transition-all': expandDetail !== item.id,
              }"
            >
              <Table.Td colspan="3">
                <div class="flex flex-row">
                  <div class="ml-5 w-48 pr-5 text-right font-medium">
                    {{ t('views.liability_category.fields.code') }}
                  </div>
                  <div class="flex-1">{{ item.code }}</div>
                </div>
                <div class="mt-1 flex flex-row">
                  <div class="ml-5 w-48 pr-5 text-right font-medium">
                    {{ t('views.liability_category.fields.name') }}
                  </div>
                  <div class="flex-1">{{ item.name }}</div>
                </div>
                <div class="mt-1 flex flex-row">
                  <div class="ml-5 w-48 pr-5 text-right font-medium">
                    {{ t('views.liability_category.fields.sequence') }}
                  </div>
                  <div class="flex-1">{{ item.sequence }}</div>
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
            <Lucide icon="XCircle" class="mx-auto mt-3 h-16 w-16 text-danger" />
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
              class="mr-1 w-24"
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
