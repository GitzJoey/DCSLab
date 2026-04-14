<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import { Dialog } from '@/components/Base/Headless';
import { FormInputDateTime, FormLabel, FormSelectSearch } from '@/components/Base/Form';
import { DataListFlex } from '@/components/DataList';
import Button from '@/components/Base/Button';
import Lucide from '@/components/Base/Lucide';
import PurchaseOrderService from '@/services/PurchaseOrderService';
import SupplierService from '@/services/SupplierService';
import { useSelectedUserLocationStore } from '@/stores/selected-user-location';
import { ErrorCode } from '@/types/enums/ErrorCode';
import { ViewMode } from '@/types/enums/ViewMode';
import { PurchaseOrder } from '@/types/models/PurchaseOrder';
import { DropDownOption } from '@/types/models/DropDownOption';
import { NotificationData } from '@/types/models/NotificationData';
import { Collection } from '@/types/resources/Collection';
import { DataListEmittedData } from '@/components/DataList/DataList.vue';
import { PurchaseOrderReadAnyPaginateRequest } from '@/types/services/purchase-order/PurchaseOrderRequest';
import { ServiceResponse } from '@/types/services/ServiceResponse';
import type { AlertPlaceholderProps } from '@/components/AlertPlaceholder/AlertPlaceholder.vue';
import { formatCurrency, formatDate } from '@/utils/helper';

const { t } = useI18n();
const router = useRouter();
const purchaseOrderService = new PurchaseOrderService();
const supplierService = new SupplierService();
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
const startDate = ref<string | null>(null);
const endDate = ref<string | null>(null);
const searchText = ref<string>('');
const selectedSupplierId = ref<string | null>(null);

const purchaseOrderLists = ref<Collection<Array<PurchaseOrder>> | null>({
  data: [],
  meta: {
    current_page: 1,
    from: null,
    last_page: 0,
    path: '',
    per_page: 10,
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

const supplierDDL = ref<Array<DropDownOption> | null>(null);
const supplierSearch = ref<string>('');
const supplierOptions = computed(() =>
  (supplierDDL.value ?? []).map((item) => ({
    value: item.code,
    label: item.name,
  })),
);

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
  startDate.value = formatDate(startOfMonth.toString(), 'YYYY-MM-DD HH:mm:ss');
  endDate.value = formatDate(endOfMonth.toString(), 'YYYY-MM-DD HH:mm:ss');

  await loadSupplierDDL();
  await getPurchaseOrders('', true, 1, 10);
});

const getPurchaseOrders = async (search: string, refresh: boolean, page: number, perPage: number) => {
  emits('loading-state', true);
  searchText.value = search;

  const request: PurchaseOrderReadAnyPaginateRequest = {
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    branch_id: selectedUserLocation.value.branch.id,
    search,
    start_date: startDate.value,
    end_date: endDate.value,
    supplier_id: selectedSupplierId.value,
    refresh,
    page,
    per_page: perPage,
  };

  const result = (await purchaseOrderService.readAnyPaginate(
    request,
  )) as ServiceResponse<Collection<Array<PurchaseOrder>> | null>;

  if (result.success && result.data) {
    purchaseOrderLists.value = result.data;
  }

  emits('loading-state', false);
};

const loadSupplierDDL = async (search = '') => {
  if (!selectedUserLocation.value) return;

  const result = await supplierService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    search,
    status: undefined,
    include_id: selectedSupplierId.value ?? undefined,
    refresh: false,
    limit: 100,
  });

  if (result.success && result.data) {
    supplierDDL.value = result.data.data.map((item: any) => ({
      code: item.id,
      name: item.name,
    }));
  }
};

const clearSupplierFilter = async () => {
  selectedSupplierId.value = null;
  await getPurchaseOrders(searchText.value, true, 1, purchaseOrderLists.value?.meta.per_page ?? 10);
};

const handleDataListChange = async (emittedData: DataListEmittedData) => {
  await getPurchaseOrders(
    emittedData.search.text,
    true,
    emittedData.pagination.page,
    emittedData.pagination.per_page,
  );
};

const viewSelected = (index: number) => {
  expandDetail.value = expandDetail.value === index ? null : index;
};

const editSelected = (index: number) => {
  if (!purchaseOrderLists.value?.data?.[index]) return;
  router.push({
    name: 'side-menu-purchase-order-edit',
    params: { ulid: purchaseOrderLists.value.data[index].ulid },
  });
};

const deleteSelected = (index: number) => {
  if (!purchaseOrderLists.value?.data?.[index]) return;
  deleteUlid.value = purchaseOrderLists.value.data[index].ulid;
  deleteModalShow.value = true;
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

const showNotification = (title: string, content: string) => {
  const notification: NotificationData = {
    title,
    content,
  };
  emits('show-notification', notification);
};

const confirmDelete = async () => {
  emits('loading-state', true);
  const result = await purchaseOrderService.delete(deleteUlid.value);
  emits('loading-state', false);
  deleteModalShow.value = false;

  if (result.success) {
    showAlertPlaceholder('hidden', '', null);
    emits('update-profile');
    showNotification(t('views.purchase_order.alert.delete.title'), t('views.purchase_order.alert.delete.message'));
    await getPurchaseOrders(searchText.value, true, purchaseOrderLists.value?.meta.current_page ?? 1, purchaseOrderLists.value?.meta.per_page ?? 10);
  }
};
</script>

<template>
  <div class="grid grid-cols-12 gap-6 mt-5">
    <div class="col-span-12">
      <div class="grid grid-cols-12 gap-4 mb-5">
        <div class="col-span-12 md:col-span-4">
          <FormLabel>{{ t('views.purchase_order.fields.start_date') }}</FormLabel>
          <FormInputDateTime v-model="startDate" @change="getPurchaseOrders(searchText, true, 1, purchaseOrderLists?.meta.per_page ?? 10)" />
        </div>
        <div class="col-span-12 md:col-span-4">
          <FormLabel>{{ t('views.purchase_order.fields.end_date') }}</FormLabel>
          <FormInputDateTime v-model="endDate" @change="getPurchaseOrders(searchText, true, 1, purchaseOrderLists?.meta.per_page ?? 10)" />
        </div>
        <div class="col-span-12 md:col-span-4">
          <FormLabel>{{ t('views.purchase_order.fields.supplier_id') }}</FormLabel>
          <FormSelectSearch
            v-model="selectedSupplierId"
            v-model:search="supplierSearch"
            :options="supplierOptions"
            :placeholder="t('components.dropdown.placeholder')"
            @change="getPurchaseOrders(searchText, true, 1, purchaseOrderLists?.meta.per_page ?? 10)"
            @search="loadSupplierDDL"
            @clear="clearSupplierFilter"
          />
        </div>
      </div>

      <DataListFlex
        :data="purchaseOrderLists"
        :enable-search="true"
        :can-print="true"
        :can-export="true"
        :rows="purchaseOrderLists?.data ?? []"
        row-class="bg-white dark:bg-darkmode-600"
        :pagination="purchaseOrderLists ? purchaseOrderLists.meta : null"
        @dataListChanged="handleDataListChange"
      >
        <template #row="{ item, index }">
          <div class="col-span-12 lg:col-span-4 md:col-span-4 self-start">
            <div class="text-primary text-xs font-semibold uppercase tracking-wide mb-1">
              {{ t('views.purchase_order.page_title') }}
            </div>
            <div class="text-slate-500 text-xs whitespace-nowrap">
              {{ t('views.purchase_order.fields.code') }}:
              {{ (item as PurchaseOrder).code ?? '-' }}
            </div>
            <div class="text-slate-500 text-xs whitespace-nowrap">
              {{ t('views.purchase_order.fields.date') }}:
              {{ (item as PurchaseOrder).date ? formatDate((item as PurchaseOrder).date, 'DD-MMM-YYYY HH:mm:ss') : '-' }}
            </div>
            <div class="text-slate-500 text-xs whitespace-nowrap">
              {{ t('views.purchase_order.fields.due_days') }}:
              {{ formatCurrency((item as PurchaseOrder).due_days ?? 0) }}
            </div>
          </div>

          <div class="col-span-12 lg:col-span-4 md:col-span-4 self-start">
            <div class="text-primary text-xs font-semibold uppercase tracking-wide mb-1">
              {{ t('views.purchase_order.fields.supplier_id') }}
            </div>
            <div class="text-slate-500 text-xs whitespace-nowrap">
              {{ (item as PurchaseOrder).supplier?.name ?? '-' }}
            </div>
            <div v-if="(item as PurchaseOrder).remarks?.trim()" class="text-slate-500 text-xs">
              {{ t('views.purchase_order.fields.remarks') }}:
              {{ (item as PurchaseOrder).remarks }}
            </div>
          </div>

          <div class="col-span-12 lg:col-span-3 md:col-span-2 self-start">
            <div class="text-primary text-xs font-semibold uppercase tracking-wide mb-1">
              {{ t('views.purchase_order.fields.grand_total') }}
            </div>
            <div class="text-slate-500 text-xs whitespace-nowrap">
              {{ formatCurrency((item as PurchaseOrder).grand_total ?? 0) }}
            </div>
            <div class="text-slate-500 text-xs whitespace-nowrap">
              {{ t('views.purchase_order.fields.amount_paid_down_payment') }}:
              {{ formatCurrency((item as PurchaseOrder).amount_paid_down_payment ?? 0) }}
            </div>
          </div>

          <div class="col-span-12 lg:col-span-1 md:col-span-2 flex justify-end items-start gap-2">
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

          <div
            v-if="expandDetail === index"
            class="col-span-12 border-t border-slate-200 dark:border-darkmode-400 mt-2 pt-3"
          >
            <div class="grid grid-cols-12 gap-4 text-xs">
              <div class="col-span-12 md:col-span-4">
                <div class="text-primary font-semibold uppercase tracking-wide mb-1">
                  {{ t('views.purchase_order.fields.global_discount') }}
                </div>
                <div class="text-slate-500">
                  {{ formatCurrency((item as PurchaseOrder).global_discount ?? 0) }}
                </div>
              </div>
              <div class="col-span-12 md:col-span-4">
                <div class="text-primary font-semibold uppercase tracking-wide mb-1">
                  {{ t('views.purchase_order.fields.down_payment') }}
                </div>
                <div class="text-slate-500">
                  {{ formatCurrency((item as PurchaseOrder).amount_available_down_payment ?? 0) }}
                </div>
              </div>
              <div class="col-span-12 md:col-span-4">
                <div class="text-primary font-semibold uppercase tracking-wide mb-1">
                  {{ t('views.purchase_order.fields.remarks') }}
                </div>
                <div class="text-slate-500">
                  {{ (item as PurchaseOrder).remarks || '-' }}
                </div>
              </div>
            </div>
          </div>
        </template>
      </DataListFlex>
    </div>
  </div>

  <Dialog :open="deleteModalShow" @close="() => { deleteModalShow = false; }">
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
        <Button type="button" variant="outline-secondary" class="w-24 mr-1" @click="deleteModalShow = false">
          {{ t('components.buttons.cancel') }}
        </Button>
        <Button type="button" variant="danger" class="w-24" @click="confirmDelete">
          {{ t('components.buttons.delete') }}
        </Button>
      </div>
    </Dialog.Panel>
  </Dialog>
</template>
