<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import { Dialog } from '@/components/Base/Headless';
import { DataListFlex } from '@/components/DataList';
import Button from '@/components/Base/Button';
import Lucide from '@/components/Base/Lucide';
import AssetCategoryService from '@/services/AssetCategoryService';
import { useSelectedUserLocationStore } from '@/stores/selected-user-location';
import { ErrorCode } from '@/types/enums/ErrorCode';
import { ViewMode } from '@/types/enums/ViewMode';
import { AssetCategory } from '@/types/models/AssetCategory';
import { NotificationData } from '@/types/models/NotificationData';
import { Collection } from '@/types/resources/Collection';
import { DataListEmittedData } from '@/components/DataList/DataList.vue';
import { AssetCategoryReadAnyPaginateRequest } from '@/types/services/asset-category/AssetCategoryRequest';
import { ServiceResponse } from '@/types/services/ServiceResponse';
import type { AlertPlaceholderProps } from '@/components/AlertPlaceholder/AlertPlaceholder.vue';

const { t } = useI18n();
const router = useRouter();
const assetCategoryService = new AssetCategoryService();
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
const assetCategoryLists = ref<Collection<Array<AssetCategory>> | null>({
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

  await getAssetCategories('', true, 1, 10);
});

const getAssetCategories = async (search: string, refresh: boolean, page: number, per_page: number) => {
  emits('loading-state', true);

  const searchReq: AssetCategoryReadAnyPaginateRequest = {
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    search,
    include_id: undefined,
    refresh,
    page,
    per_page,
  };

  const result: ServiceResponse<Collection<Array<AssetCategory>> | null> =
    await assetCategoryService.readAnyPaginate(searchReq);

  if (result.success && result.data) {
    assetCategoryLists.value = result.data;
    showAlertPlaceholder('hidden', '', null);
  } else {
    showAlertPlaceholder('danger', '', result.errors as Record<string, Array<string>>);
  }

  emits('loading-state', false);
};

const handleDataListChange = async (data: DataListEmittedData) => {
  await getAssetCategories(data.search.text, false, data.pagination.page, data.pagination.per_page);
};

const viewSelected = (idx: number) => {
  if (expandDetail.value === idx) {
    expandDetail.value = null;
  } else {
    expandDetail.value = idx;
  }
};

const editSelected = (idx: number) => {
  if (!assetCategoryLists.value) return;
  const ulid = assetCategoryLists.value.data[idx].ulid;
  emits('mode-state', ViewMode.FORM_EDIT);
  router.push({
    name: 'side-menu-asset-category-edit',
    params: { ulid },
  });
};

const deleteSelected = (idx: number) => {
  if (!assetCategoryLists.value) return;
  deleteUlid.value = assetCategoryLists.value.data[idx].ulid;
  deleteModalShow.value = true;
};

const confirmDelete = async () => {
  deleteModalShow.value = false;
  emits('loading-state', true);

  const result = await assetCategoryService.delete(deleteUlid.value);

  emits('loading-state', false);

  if (result.success) {
    emits('update-profile');
    await getAssetCategories('', true, 1, 10);
    showNotification(
      t('views.asset_category.alert.delete_asset_category.title'),
      t('views.asset_category.alert.delete_asset_category.content'),
    );
  } else {
    showAlertPlaceholder('danger', '', result.errors as Record<string, Array<string>>);
  }
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
  <div class="grid grid-cols-12 gap-6 mt-5">
    <div class="col-span-12">
      <DataListFlex
        :data="assetCategoryLists"
        :title="t('views.asset_category.table.title')"
        :enable-search="true"
        :can-print="true"
        :can-export="true"
        :rows="assetCategoryLists?.data ?? []"
        row-class="bg-white dark:bg-darkmode-600"
        :pagination="assetCategoryLists ? assetCategoryLists.meta : null"
        @dataListChanged="handleDataListChange"
      >
        <template #row="{ item, index }">
          <div class="col-span-12 md:col-span-5 self-start">
            <div class="space-y-2">
              <div class="text-primary text-xs font-semibold uppercase tracking-wide">
                {{ t('views.asset_category.page_title') }}
              </div>
              <div class="grid grid-cols-12 items-center gap-x-3 gap-y-2 text-xs">
                <div class="col-span-4 text-slate-500">{{ t('views.asset_category.fields.code') }}</div>
                <div class="col-span-8 text-slate-700 dark:text-slate-200">{{ (item as AssetCategory).code }}</div>
                <div class="col-span-4 text-slate-500">{{ t('views.asset_category.fields.name') }}</div>
                <div class="col-span-8 text-slate-700 dark:text-slate-200 break-words">
                  {{ (item as AssetCategory).name }}
                </div>
                <div class="col-span-4 text-slate-500">
                  {{ t('views.asset_category.fields.estimated_useful_life_months') }}
                </div>
                <div class="col-span-8 text-slate-700 dark:text-slate-200">
                  {{ (item as AssetCategory).estimated_useful_life_months || '-' }}
                </div>
                <div class="col-span-4 text-slate-500">{{ t('views.asset_category.fields.remarks') }}</div>
                <div class="col-span-8 text-slate-700 dark:text-slate-200 break-words">
                  {{ (item as AssetCategory).remarks || '-' }}
                </div>
              </div>
            </div>
          </div>

          <div class="col-span-12 md:col-span-5 self-start md:pl-3">
            <div
              v-if="expandDetail === index"
              class="rounded-md border border-slate-200/60 p-4 text-xs dark:border-darkmode-400"
            >
              <div class="grid grid-cols-12 items-center gap-x-3 gap-y-2">
                <div class="col-span-4 text-slate-500">{{ t('views.asset_category.fields.code') }}</div>
                <div class="col-span-8 text-slate-700 dark:text-slate-200">{{ (item as AssetCategory).code }}</div>
                <div class="col-span-4 text-slate-500">{{ t('views.asset_category.fields.name') }}</div>
                <div class="col-span-8 text-slate-700 dark:text-slate-200 break-words">
                  {{ (item as AssetCategory).name }}
                </div>
                <div class="col-span-4 text-slate-500">
                  {{ t('views.asset_category.fields.estimated_useful_life_months') }}
                </div>
                <div class="col-span-8 text-slate-700 dark:text-slate-200">
                  {{ (item as AssetCategory).estimated_useful_life_months || '-' }}
                </div>
                <div class="col-span-4 text-slate-500">{{ t('views.asset_category.fields.remarks') }}</div>
                <div class="col-span-8 text-slate-700 dark:text-slate-200 break-words">
                  {{ (item as AssetCategory).remarks || '-' }}
                </div>
              </div>
            </div>
          </div>

          <div class="col-span-12 md:col-span-2 self-center flex justify-end items-center gap-2 pt-2 md:flex-col">
            <Button size="sm" variant="outline-secondary" class="flex items-center gap-1" @click="viewSelected(index)">
              <Lucide icon="Info" class="w-4 h-4" />
            </Button>
            <Button size="sm" variant="outline-secondary" class="flex items-center gap-1" @click="editSelected(index)">
              <Lucide icon="Pen" class="w-4 h-4" />
            </Button>
            <Button size="sm" variant="outline-secondary" class="flex items-center gap-1" @click="deleteSelected(index)">
              <Lucide icon="Trash2" class="w-4 h-4 text-danger" />
            </Button>
          </div>
        </template>
      </DataListFlex>
    </div>
  </div>

  <Dialog :open="deleteModalShow" @close="() => { deleteModalShow = false; }">
    <Dialog.Panel>
      <div class="p-5 text-center">
        <Lucide icon="XCircle" class="w-16 h-16 mx-auto mt-3 text-danger" />
        <div class="mt-5 text-3xl">{{ t('components.delete-modal.title') }}</div>
        <div class="mt-2 text-slate-500">
          {{ t('components.delete-modal.desc_1') }}
          <br />
          {{ t('components.delete-modal.desc_2') }}
        </div>
      </div>
      <div class="px-5 pb-8 text-center">
        <Button variant="outline-secondary" type="button" @click="deleteModalShow = false" class="w-24 mr-1">
          {{ t('components.buttons.cancel') }}
        </Button>
        <Button variant="danger" type="button" class="w-24" @click="confirmDelete">
          {{ t('components.buttons.delete') }}
        </Button>
      </div>
    </Dialog.Panel>
  </Dialog>
</template>
