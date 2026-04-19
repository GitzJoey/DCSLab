<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { Dialog } from '@/components/Base/Headless';
import { DataListFlex } from '@/components/DataList';
import Button from '@/components/Base/Button';
import Lucide from '@/components/Base/Lucide';
import PurchaseAdditionalCostPaymentService from '@/services/PurchaseAdditionalCostPaymentService';
import { useSelectedUserLocationStore } from '@/stores/selected-user-location';
import { ErrorCode } from '@/types/enums/ErrorCode';
import { ViewMode } from '@/types/enums/ViewMode';
import type { PurchaseAdditionalCostPayment } from '@/types/models/PurchaseAdditionalCostPayment';
import type { NotificationData } from '@/types/models/NotificationData';
import type { Collection } from '@/types/resources/Collection';
import type { DataListEmittedData } from '@/components/DataList/DataList.vue';
import type { PurchaseAdditionalCostPaymentReadAnyPaginateRequest } from '@/types/services/purchase-additional-cost-payment/PurchaseAdditionalCostPaymentRequest';
import type { ServiceResponse } from '@/types/services/ServiceResponse';
import type { AlertPlaceholderProps } from '@/components/AlertPlaceholder/AlertPlaceholder.vue';
import { formatCurrency, formatDate } from '@/utils/helper';

const { t } = useI18n();
const router = useRouter();
const purchaseAdditionalCostPaymentService = new PurchaseAdditionalCostPaymentService();
const selectedUserLocationStore = useSelectedUserLocationStore();

const emits = defineEmits(['mode-state', 'loading-state', 'update-profile', 'show-alertplaceholder', 'show-notification']);
const deleteUlid = ref<string>('');
const deleteModalShow = ref<boolean>(false);
const expandDetail = ref<number | null>(null);
const purchaseAdditionalCostPaymentLists = ref<Collection<Array<PurchaseAdditionalCostPayment>> | null>({
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
  await getPurchaseAdditionalCostPayments('', true, 1, 10);
});

const getPurchaseAdditionalCostPayments = async (search: string, refresh: boolean, page: number, per_page: number) => {
  emits('loading-state', true);
  const searchReq: PurchaseAdditionalCostPaymentReadAnyPaginateRequest = {
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    branch_id: selectedUserLocation.value.branch.id,
    search,
    purchase_additional_cost_id: null,
    include_id: undefined,
    refresh,
    page,
    per_page,
  };
  const result: ServiceResponse<Collection<Array<PurchaseAdditionalCostPayment>> | null> =
    await purchaseAdditionalCostPaymentService.readAnyPaginate(searchReq);

  if (result.success && result.data) {
    purchaseAdditionalCostPaymentLists.value = result.data;
    showAlertPlaceholder('hidden', '', null);
  } else {
    showAlertPlaceholder('danger', '', result.errors as Record<string, Array<string>>);
  }
  emits('loading-state', false);
};

const handleDataListChange = async (data: DataListEmittedData) => {
  await getPurchaseAdditionalCostPayments(data.search.text, false, data.pagination.page, data.pagination.per_page);
};

const viewSelected = (idx: number) => {
  expandDetail.value = expandDetail.value === idx ? null : idx;
};

const editSelected = (idx: number) => {
  if (!purchaseAdditionalCostPaymentLists.value) return;
  router.push({
    name: 'side-menu-purchase-additional-cost-payment-edit',
    params: { ulid: purchaseAdditionalCostPaymentLists.value.data[idx].ulid },
  });
};

const deleteSelected = (idx: number) => {
  if (!purchaseAdditionalCostPaymentLists.value) return;
  deleteUlid.value = purchaseAdditionalCostPaymentLists.value.data[idx].ulid;
  deleteModalShow.value = true;
};

const confirmDelete = async () => {
  deleteModalShow.value = false;
  emits('loading-state', true);
  const result = await purchaseAdditionalCostPaymentService.delete(deleteUlid.value);
  emits('loading-state', false);

  if (result.success) {
    emits('update-profile');
    await getPurchaseAdditionalCostPayments('', true, 1, 10);
    showNotification(
      t('views.purchase_additional_cost_payment.alert.delete_purchase_additional_cost_payment.title'),
      t('views.purchase_additional_cost_payment.alert.delete_purchase_additional_cost_payment.content'),
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
        :data="purchaseAdditionalCostPaymentLists"
        :title="t('views.purchase_additional_cost_payment.table.title')"
        :enable-search="true"
        :can-print="true"
        :can-export="true"
        :rows="purchaseAdditionalCostPaymentLists?.data ?? []"
        row-class="bg-white dark:bg-darkmode-600"
        :pagination="purchaseAdditionalCostPaymentLists ? purchaseAdditionalCostPaymentLists.meta : null"
        @dataListChanged="handleDataListChange"
      >
        <template #row="{ item, index }">
          <div class="col-span-12 md:col-span-5 self-start">
            <div class="space-y-2">
              <div class="text-primary text-xs font-semibold uppercase tracking-wide">
                {{ (item as PurchaseAdditionalCostPayment).code }}
              </div>
              <div class="grid grid-cols-12 items-center gap-x-3 gap-y-2 text-xs">
                <div class="col-span-4 text-slate-500">{{ t('views.purchase_additional_cost_payment.fields.purchase_additional_cost') }}</div>
                <div class="col-span-8 text-slate-700 dark:text-slate-200">
                  {{ (item as PurchaseAdditionalCostPayment).purchase_additional_cost?.code ?? '-' }}
                </div>
                <div class="col-span-4 text-slate-500">{{ t('views.purchase_additional_cost_payment.fields.amount') }}</div>
                <div class="col-span-8 text-slate-700 dark:text-slate-200">
                  {{ formatCurrency(Number((item as PurchaseAdditionalCostPayment).amount ?? 0)) }}
                </div>
                <div class="col-span-4 text-slate-500">{{ t('views.purchase_additional_cost_payment.fields.cash_account') }}</div>
                <div class="col-span-8 text-slate-700 dark:text-slate-200">
                  {{ (item as PurchaseAdditionalCostPayment).cash_account?.name ?? '-' }}
                </div>
              </div>
            </div>
          </div>
          <div class="col-span-12 md:col-span-5 self-start md:pl-3">
            <div v-if="expandDetail === index" class="rounded-md border border-slate-200/60 p-4 text-xs dark:border-darkmode-400">
              <div class="grid grid-cols-12 items-center gap-x-3 gap-y-2">
                <div class="col-span-4 text-slate-500">{{ t('views.purchase_additional_cost_payment.fields.date') }}</div>
                <div class="col-span-8 text-slate-700 dark:text-slate-200">
                  {{ formatDate((item as PurchaseAdditionalCostPayment).date, 'YYYY-MM-DD HH:mm:ss') }}
                </div>
                <div class="col-span-4 text-slate-500">{{ t('views.purchase_additional_cost_payment.fields.remarks') }}</div>
                <div class="col-span-8 text-slate-700 dark:text-slate-200">
                  {{ (item as PurchaseAdditionalCostPayment).remarks ?? '-' }}
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
