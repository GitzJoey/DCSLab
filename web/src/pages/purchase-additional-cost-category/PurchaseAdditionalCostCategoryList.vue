<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import { Dialog } from '@/components/Base/Headless';
import { DataListFlex } from '@/components/DataList';
import Button from '@/components/Base/Button';
import Lucide from '@/components/Base/Lucide';
import PurchaseAdditionalCostCategoryService from '@/services/PurchaseAdditionalCostCategoryService';
import { useSelectedUserLocationStore } from '@/stores/selected-user-location';
import { ErrorCode } from '@/types/enums/ErrorCode';
import { ViewMode } from '@/types/enums/ViewMode';
import { PurchaseAdditionalCostCategory } from '@/types/models/PurchaseAdditionalCostCategory';
import { NotificationData } from '@/types/models/NotificationData';
import { Collection } from '@/types/resources/Collection';
import { DataListEmittedData } from '@/components/DataList/DataList.vue';
import { PurchaseAdditionalCostCategoryReadAnyPaginateRequest } from '@/types/services/purchase-additional-cost-category/PurchaseAdditionalCostCategoryRequest';
import { ServiceResponse } from '@/types/services/ServiceResponse';
import type { AlertPlaceholderProps } from '@/components/AlertPlaceholder/AlertPlaceholder.vue';

const { t } = useI18n();
const router = useRouter();
const purchaseAdditionalCostCategoryService = new PurchaseAdditionalCostCategoryService();
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
const purchaseAdditionalCostCategoryLists = ref<Collection<Array<PurchaseAdditionalCostCategory>> | null>({
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
  }

  await getPurchaseAdditionalCostCategories('', true, 1, 10);
});

const getPurchaseAdditionalCostCategories = async (
  search: string,
  refresh: boolean,
  page: number,
  per_page: number,
) => {
  emits('loading-state', true);

  const searchReq: PurchaseAdditionalCostCategoryReadAnyPaginateRequest = {
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    search,
    include_id: undefined,
    refresh,
    page,
    per_page,
  };

  const result: ServiceResponse<Collection<Array<PurchaseAdditionalCostCategory>> | null> =
    await purchaseAdditionalCostCategoryService.readAnyPaginate(searchReq);

  if (result.success && result.data) {
    purchaseAdditionalCostCategoryLists.value = result.data;
    showAlertPlaceholder('hidden', '', null);
  } else {
    showAlertPlaceholder('danger', '', result.errors as Record<string, Array<string>>);
  }

  emits('loading-state', false);
};

const handleDataListChange = async (data: DataListEmittedData) => {
  await getPurchaseAdditionalCostCategories(data.search.text, false, data.pagination.page, data.pagination.per_page);
};

const viewSelected = (idx: number) => {
  if (expandDetail.value === idx) {
    expandDetail.value = null;
  } else {
    expandDetail.value = idx;
  }
};

const editSelected = (idx: number) => {
  if (!purchaseAdditionalCostCategoryLists.value) return;
  const ulid = purchaseAdditionalCostCategoryLists.value.data[idx].ulid;
  emits('mode-state', ViewMode.FORM_EDIT);
  router.push({
    name: 'side-menu-purchase-additional-cost-category-edit',
    params: { ulid },
  });
};

const deleteSelected = (idx: number) => {
  if (!purchaseAdditionalCostCategoryLists.value) return;
  deleteUlid.value = purchaseAdditionalCostCategoryLists.value.data[idx].ulid;
  deleteModalShow.value = true;
};

const confirmDelete = async () => {
  deleteModalShow.value = false;
  emits('loading-state', true);

  const result = await purchaseAdditionalCostCategoryService.delete(deleteUlid.value);

  emits('loading-state', false);

  if (result.success) {
    emits('update-profile');
    await getPurchaseAdditionalCostCategories('', true, 1, 10);
    showNotification(
      t('views.purchase_additional_cost_category.alert.delete_purchase_additional_cost_category.title'),
      t('views.purchase_additional_cost_category.alert.delete_purchase_additional_cost_category.content'),
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
        :data="purchaseAdditionalCostCategoryLists"
        :title="t('views.purchase_additional_cost_category.table.title')"
        :enable-search="true"
        :can-print="true"
        :can-export="true"
        :rows="purchaseAdditionalCostCategoryLists?.data ?? []"
        row-class="bg-white dark:bg-darkmode-600"
        :pagination="purchaseAdditionalCostCategoryLists ? purchaseAdditionalCostCategoryLists.meta : null"
        @dataListChanged="handleDataListChange"
      >
        <template #row="{ item, index }">
          <div class="col-span-12 md:col-span-5 self-start">
            <div class="space-y-2">
              <div class="text-primary text-xs font-semibold uppercase tracking-wide">
                {{ t('views.purchase_additional_cost_category.page_title') }}
              </div>
              <div class="grid grid-cols-12 items-center gap-x-3 gap-y-2 text-xs">
                <div class="col-span-4 text-slate-500">{{ t('views.purchase_additional_cost_category.fields.code') }}</div>
                <div class="col-span-8 text-slate-700 dark:text-slate-200">{{ (item as PurchaseAdditionalCostCategory).code }}</div>
                <div class="col-span-4 text-slate-500">{{ t('views.purchase_additional_cost_category.fields.name') }}</div>
                <div class="col-span-8 text-slate-700 dark:text-slate-200 break-words">{{ (item as PurchaseAdditionalCostCategory).name }}</div>
              </div>
            </div>
          </div>

          <div class="col-span-12 md:col-span-5 self-start md:pl-3">
            <div v-if="expandDetail === index" class="rounded-md border border-slate-200/60 p-4 text-xs dark:border-darkmode-400">
              <div class="grid grid-cols-12 items-center gap-x-3 gap-y-2">
                <div class="col-span-4 text-slate-500">{{ t('views.purchase_additional_cost_category.fields.code') }}</div>
                <div class="col-span-8 text-slate-700 dark:text-slate-200">{{ (item as PurchaseAdditionalCostCategory).code }}</div>
                <div class="col-span-4 text-slate-500">{{ t('views.purchase_additional_cost_category.fields.name') }}</div>
                <div class="col-span-8 text-slate-700 dark:text-slate-200 break-words">{{ (item as PurchaseAdditionalCostCategory).name }}</div>
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
        <div class="mt-5 text-3xl">Are you sure?</div>
        <div class="mt-2 text-slate-500">
          This action cannot be undone.
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
