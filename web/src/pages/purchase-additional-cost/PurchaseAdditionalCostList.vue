<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { Dialog } from '@/components/Base/Headless';
import { DataListFlex } from '@/components/DataList';
import Button from '@/components/Base/Button';
import Lucide from '@/components/Base/Lucide';
import PurchaseAdditionalCostService from '@/services/PurchaseAdditionalCostService';
import { useSelectedUserLocationStore } from '@/stores/selected-user-location';
import { ErrorCode } from '@/types/enums/ErrorCode';
import { ViewMode } from '@/types/enums/ViewMode';
import type { PurchaseAdditionalCost } from '@/types/models/PurchaseAdditionalCost';
import type { NotificationData } from '@/types/models/NotificationData';
import type { Collection } from '@/types/resources/Collection';
import type { DataListEmittedData } from '@/components/DataList/DataList.vue';
import type { PurchaseAdditionalCostReadAnyPaginateRequest } from '@/types/services/purchase-additional-cost/PurchaseAdditionalCostRequest';
import type { ServiceResponse } from '@/types/services/ServiceResponse';
import type { AlertPlaceholderProps } from '@/components/AlertPlaceholder/AlertPlaceholder.vue';
import { formatCurrency, formatDate } from '@/utils/helper';

const { t } = useI18n();
const router = useRouter();
const purchaseAdditionalCostService = new PurchaseAdditionalCostService();
const selectedUserLocationStore = useSelectedUserLocationStore();

const emits = defineEmits(['mode-state', 'loading-state', 'update-profile', 'show-alertplaceholder', 'show-notification']);
const deleteUlid = ref<string>('');
const deleteModalShow = ref<boolean>(false);
const expandDetail = ref<number | null>(null);
const purchaseAdditionalCostLists = ref<Collection<Array<PurchaseAdditionalCost>> | null>({
  data: [],
  meta: { current_page: 0, from: null, last_page: 0, path: '', per_page: 0, to: null, total: 0 },
  links: { first: '', last: '', prev: null, next: null },
});

const isUserLocationSelected = computed(() => selectedUserLocationStore.isUserLocationSelected);
const selectedUserLocation = computed(() => selectedUserLocationStore.selectedUserLocation);

onMounted(async () => {
  emits('mode-state', ViewMode.LIST);
  if (!isUserLocationSelected.value) {
    router.push({ name: 'side-menu-error-code', params: { code: ErrorCode.USERLOCATION_REQUIRED } });
  }
  await getPurchaseAdditionalCosts('', true, 1, 10);
});

const getPurchaseAdditionalCosts = async (search: string, refresh: boolean, page: number, per_page: number) => {
  emits('loading-state', true);
  const searchReq: PurchaseAdditionalCostReadAnyPaginateRequest = {
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    branch_id: selectedUserLocation.value.branch.id,
    search,
    purchase_id: null,
    purchase_additional_cost_category_id: null,
    is_amount_payable_paid_off: null,
    include_id: undefined,
    refresh,
    page,
    per_page,
  };
  const result: ServiceResponse<Collection<Array<PurchaseAdditionalCost>> | null> =
    await purchaseAdditionalCostService.readAnyPaginate(searchReq);

  if (result.success && result.data) {
    purchaseAdditionalCostLists.value = result.data;
    showAlertPlaceholder('hidden', '', null);
  } else {
    showAlertPlaceholder('danger', '', result.errors as Record<string, Array<string>>);
  }
  emits('loading-state', false);
};

const handleDataListChange = async (data: DataListEmittedData) => {
  await getPurchaseAdditionalCosts(data.search.text, false, data.pagination.page, data.pagination.per_page);
};

const viewSelected = (idx: number) => {
  expandDetail.value = expandDetail.value === idx ? null : idx;
};

const editSelected = (idx: number) => {
  if (!purchaseAdditionalCostLists.value) return;
  router.push({
    name: 'side-menu-purchase-additional-cost-edit',
    params: { ulid: purchaseAdditionalCostLists.value.data[idx].ulid },
  });
};

const deleteSelected = (idx: number) => {
  if (!purchaseAdditionalCostLists.value) return;
  deleteUlid.value = purchaseAdditionalCostLists.value.data[idx].ulid;
  deleteModalShow.value = true;
};

const confirmDelete = async () => {
  deleteModalShow.value = false;
  emits('loading-state', true);
  const result = await purchaseAdditionalCostService.delete(deleteUlid.value);
  emits('loading-state', false);

  if (result.success) {
    emits('update-profile');
    await getPurchaseAdditionalCosts('', true, 1, 10);
    showNotification(
      t('views.purchase_additional_cost.alert.delete_purchase_additional_cost.title'),
      t('views.purchase_additional_cost.alert.delete_purchase_additional_cost.content'),
    );
  } else {
    showAlertPlaceholder('danger', '', result.errors as Record<string, Array<string>>);
  }
};

const showNotification = (pTitle: string, pContent: string) => {
  const n: NotificationData = { title: pTitle, content: pContent };
  emits('show-notification', n);
};

const showAlertPlaceholder = (
  pAlertType: 'hidden' | 'danger' | 'success' | 'warning' | 'pending' | 'dark',
  pTitle: string,
  pAlertList: Record<string, Array<string>> | null,
) => {
  const ap: AlertPlaceholderProps = { alertType: pAlertType, title: pTitle, alertList: pAlertList };
  emits('show-alertplaceholder', ap);
};
</script>

<template>
  <div class="grid grid-cols-12 gap-6 mt-5">
    <div class="col-span-12">
      <DataListFlex
        :data="purchaseAdditionalCostLists"
        :title="t('views.purchase_additional_cost.table.title')"
        :enable-search="true"
        :can-print="true"
        :can-export="true"
        :rows="purchaseAdditionalCostLists?.data ?? []"
        row-class="bg-white dark:bg-darkmode-600"
        :pagination="purchaseAdditionalCostLists ? purchaseAdditionalCostLists.meta : null"
        @dataListChanged="handleDataListChange"
      >
        <template #row="{ item, index }">
          <div class="col-span-12 md:col-span-5 self-start">
            <div class="space-y-2">
              <div class="text-primary text-xs font-semibold uppercase tracking-wide">
                {{ (item as PurchaseAdditionalCost).code }}
              </div>
              <div class="grid grid-cols-12 items-center gap-x-3 gap-y-2 text-xs">
                <div class="col-span-4 text-slate-500">{{ t('views.purchase_additional_cost.fields.purchase') }}</div>
                <div class="col-span-8 text-slate-700 dark:text-slate-200">
                  {{ (item as PurchaseAdditionalCost).purchase?.code ?? '-' }}
                </div>
                <div class="col-span-4 text-slate-500">{{ t('views.purchase_additional_cost.fields.category') }}</div>
                <div class="col-span-8 text-slate-700 dark:text-slate-200">
                  {{ (item as PurchaseAdditionalCost).category?.name ?? '-' }}
                </div>
                <div class="col-span-4 text-slate-500">{{ t('views.purchase_additional_cost.fields.amount_total') }}</div>
                <div class="col-span-8 text-slate-700 dark:text-slate-200">
                  {{ formatCurrency(Number((item as PurchaseAdditionalCost).amount_total ?? 0)) }}
                </div>
              </div>
            </div>
          </div>
          <div class="col-span-12 md:col-span-5 self-start md:pl-3">
            <div v-if="expandDetail === index" class="rounded-md border border-slate-200/60 p-4 text-xs dark:border-darkmode-400">
              <div class="grid grid-cols-12 items-center gap-x-3 gap-y-2">
                <div class="col-span-4 text-slate-500">{{ t('views.purchase_additional_cost.fields.date') }}</div>
                <div class="col-span-8 text-slate-700 dark:text-slate-200">
                  {{ formatDate((item as PurchaseAdditionalCost).date, 'YYYY-MM-DD HH:mm:ss') }}
                </div>
                <div class="col-span-4 text-slate-500">{{ t('views.purchase_additional_cost.fields.amount_paid_immediately') }}</div>
                <div class="col-span-8 text-slate-700 dark:text-slate-200">
                  {{ formatCurrency(Number((item as PurchaseAdditionalCost).amount_paid_immediately ?? 0)) }}
                </div>
                <div class="col-span-4 text-slate-500">{{ t('views.purchase_additional_cost.fields.amount_payable_due') }}</div>
                <div class="col-span-8 text-slate-700 dark:text-slate-200">
                  {{ formatCurrency(Number((item as PurchaseAdditionalCost).amount_payable_due ?? 0)) }}
                </div>
                <div class="col-span-4 text-slate-500">{{ t('views.purchase_additional_cost.fields.is_amount_payable_paid_off') }}</div>
                <div class="col-span-8 text-slate-700 dark:text-slate-200">
                  {{ (item as PurchaseAdditionalCost).is_amount_payable_paid_off ? 'Yes' : 'No' }}
                </div>
              </div>
            </div>
          </div>
          <div class="col-span-12 md:col-span-2 self-center flex justify-end items-center gap-2 pt-2 md:flex-col">
            <Button size="sm" variant="outline-secondary" @click="viewSelected(index)">
              <Lucide icon="Info" class="w-4 h-4" />
            </Button>
            <Button size="sm" variant="outline-secondary" @click="editSelected(index)">
              <Lucide icon="Pen" class="w-4 h-4" />
            </Button>
            <Button size="sm" variant="outline-secondary" @click="deleteSelected(index)">
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
        <div class="mt-2 text-slate-500">This action cannot be undone.</div>
      </div>
      <div class="px-5 pb-8 text-center">
        <Button variant="outline-secondary" type="button" class="w-24 mr-1" @click="deleteModalShow = false">
          {{ t('components.buttons.cancel') }}
        </Button>
        <Button variant="danger" type="button" class="w-24" @click="confirmDelete">
          {{ t('components.buttons.delete') }}
        </Button>
      </div>
    </Dialog.Panel>
  </Dialog>
</template>
