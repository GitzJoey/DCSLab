<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import { Dialog } from '@/components/Base/Headless';
import { FormInputDateTime, FormLabel, FormSelectSearch } from '@/components/Base/Form';
import { DataListFlex } from '@/components/DataList';
import Button from '@/components/Base/Button';
import Lucide from '@/components/Base/Lucide';
import PurchaseService from '@/services/PurchaseService';
import PurchaseReceiptService from '@/services/PurchaseReceiptService';
import SupplierService from '@/services/SupplierService';
import WarehouseService from '@/services/WarehouseService';
import { useSelectedUserLocationStore } from '@/stores/selected-user-location';
import { ErrorCode } from '@/types/enums/ErrorCode';
import { ViewMode } from '@/types/enums/ViewMode';
import type { PurchaseReceipt } from '@/types/models/PurchaseReceipt';
import type { DropDownOption } from '@/types/models/DropDownOption';
import type { NotificationData } from '@/types/models/NotificationData';
import type { Collection } from '@/types/resources/Collection';
import type { DataListEmittedData } from '@/components/DataList/DataList.vue';
import type { PurchaseReceiptReadAnyPaginateRequest } from '@/types/services/purchase-receipt/PurchaseReceiptRequest';
import type { ServiceResponse } from '@/types/services/ServiceResponse';
import type { AlertPlaceholderProps } from '@/components/AlertPlaceholder/AlertPlaceholder.vue';
import { formatDate } from '@/utils/helper';

const { t } = useI18n();
const router = useRouter();
const purchaseReceiptService = new PurchaseReceiptService();
const purchaseService = new PurchaseService();
const supplierService = new SupplierService();
const warehouseService = new WarehouseService();
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
const selectedPurchaseId = ref<string | null>(null);
const selectedWarehouseId = ref<string | null>(null);
const selectedIsPosted = ref<string | null>(null);
const selectedIsFromDirectPurchase = ref<string | null>(null);

const purchaseReceiptLists = ref<Collection<Array<PurchaseReceipt>> | null>({
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

const purchaseDDL = ref<Array<DropDownOption> | null>(null);
const purchaseSearch = ref<string>('');
const purchaseOptions = computed(() =>
  (purchaseDDL.value ?? []).map((item) => ({
    value: item.code,
    label: item.name,
  })),
);

const warehouseDDL = ref<Array<DropDownOption> | null>(null);
const warehouseSearch = ref<string>('');
const warehouseOptions = computed(() =>
  (warehouseDDL.value ?? []).map((item) => ({
    value: item.code,
    label: item.name,
  })),
);

const postedOptions = computed(() => [
  { value: 'true', label: t('components.buttons.yes') },
  { value: 'false', label: t('components.buttons.no') },
]);

const directPurchaseOptions = computed(() => [
  { value: 'true', label: t('components.buttons.yes') },
  { value: 'false', label: t('components.buttons.no') },
]);

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

  await Promise.all([loadSupplierDDL(), loadPurchaseDDL(), loadWarehouseDDL()]);
  await getPurchaseReceipts('', true, 1, 10);
});

const getPurchaseReceipts = async (search: string, refresh: boolean, page: number, perPage: number) => {
  emits('loading-state', true);
  searchText.value = search;

  const request: PurchaseReceiptReadAnyPaginateRequest = {
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    branch_id: selectedUserLocation.value.branch.id,
    search,
    start_date: startDate.value,
    end_date: endDate.value,
    supplier_id: selectedSupplierId.value,
    purchase_id: selectedPurchaseId.value,
    warehouse_id: selectedWarehouseId.value,
    is_posted: selectedIsPosted.value === null ? null : selectedIsPosted.value === 'true',
    is_from_direct_purchase:
      selectedIsFromDirectPurchase.value === null ? null : selectedIsFromDirectPurchase.value === 'true',
    refresh,
    page,
    per_page: perPage,
  };

  const result = (await purchaseReceiptService.readAnyPaginate(
    request,
  )) as ServiceResponse<Collection<Array<PurchaseReceipt>> | null>;

  if (result.success && result.data) {
    purchaseReceiptLists.value = result.data;
    showAlertPlaceholder('hidden', '', null);
  } else {
    showAlertPlaceholder('danger', '', result.errors as Record<string, Array<string>>);
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

const loadPurchaseDDL = async (search = '') => {
  if (!selectedUserLocation.value) return;

  const result = await purchaseService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    branch_id: selectedUserLocation.value.branch.id,
    search,
    start_date: null,
    end_date: undefined,
    supplier_id: selectedSupplierId.value,
    refresh: false,
    limit: 100,
  });

  if (result.success && result.data) {
    purchaseDDL.value = result.data.data.map((item) => ({
      code: item.id,
      name: `${item.code} - ${item.supplier?.name ?? '-'}`,
    }));
  }
};

const loadWarehouseDDL = async (search = '') => {
  if (!selectedUserLocation.value) return;

  const result = await warehouseService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    branch_id: selectedUserLocation.value.branch.id,
    search,
    include_id: selectedWarehouseId.value ?? undefined,
    status: undefined,
    refresh: false,
    limit: 100,
  });

  if (result.success && result.data) {
    warehouseDDL.value = result.data.data.map((item: any) => ({
      code: item.id,
      name: item.name,
    }));
  }
};

const reloadList = async () => {
  await getPurchaseReceipts(searchText.value, true, 1, purchaseReceiptLists.value?.meta.per_page ?? 10);
};

const clearSupplierFilter = async () => {
  selectedSupplierId.value = null;
  await loadPurchaseDDL();
  await reloadList();
};

const clearPurchaseFilter = async () => {
  selectedPurchaseId.value = null;
  await reloadList();
};

const clearWarehouseFilter = async () => {
  selectedWarehouseId.value = null;
  await reloadList();
};

const handleDataListChange = async (emittedData: DataListEmittedData) => {
  await getPurchaseReceipts(
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
  if (!purchaseReceiptLists.value?.data?.[index]) return;

  router.push({
    name: 'side-menu-purchase-receipt-edit',
    params: { ulid: purchaseReceiptLists.value.data[index].ulid },
  });
};

const deleteSelected = (index: number) => {
  if (!purchaseReceiptLists.value?.data?.[index]) return;

  deleteUlid.value = purchaseReceiptLists.value.data[index].ulid;
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
  const result = await purchaseReceiptService.delete(deleteUlid.value);
  emits('loading-state', false);
  deleteModalShow.value = false;

  if (result.success) {
    showAlertPlaceholder('hidden', '', null);
    emits('update-profile');
    showNotification(
      t('views.purchase_receipt.alert.delete.title'),
      t('views.purchase_receipt.alert.delete.message'),
    );
    await getPurchaseReceipts(
      searchText.value,
      true,
      purchaseReceiptLists.value?.meta.current_page ?? 1,
      purchaseReceiptLists.value?.meta.per_page ?? 10,
    );
  } else {
    showAlertPlaceholder('danger', '', result.errors as Record<string, Array<string>>);
  }
};
</script>

<template>
  <div class="mt-5 grid grid-cols-12 gap-6">
    <div class="col-span-12">
      <div class="mb-5 grid grid-cols-12 gap-4">
        <div class="col-span-12 md:col-span-3">
          <FormLabel>{{ t('views.purchase_receipt.fields.start_date') }}</FormLabel>
          <FormInputDateTime
            v-model="startDate"
            @change="getPurchaseReceipts(searchText, true, 1, purchaseReceiptLists?.meta.per_page ?? 10)"
          />
        </div>
        <div class="col-span-12 md:col-span-3">
          <FormLabel>{{ t('views.purchase_receipt.fields.end_date') }}</FormLabel>
          <FormInputDateTime
            v-model="endDate"
            @change="getPurchaseReceipts(searchText, true, 1, purchaseReceiptLists?.meta.per_page ?? 10)"
          />
        </div>
        <div class="col-span-12 md:col-span-3">
          <FormLabel>{{ t('views.purchase_receipt.fields.supplier_id') }}</FormLabel>
          <FormSelectSearch
            v-model="selectedSupplierId"
            v-model:search="supplierSearch"
            :options="supplierOptions"
            :placeholder="t('components.dropdown.placeholder')"
            @change="async () => { await loadPurchaseDDL(); await getPurchaseReceipts(searchText, true, 1, purchaseReceiptLists?.meta.per_page ?? 10); }"
            @search="loadSupplierDDL"
            @clear="clearSupplierFilter"
          />
        </div>
        <div class="col-span-12 md:col-span-3">
          <FormLabel>{{ t('views.purchase_receipt.fields.purchase_id') }}</FormLabel>
          <FormSelectSearch
            v-model="selectedPurchaseId"
            v-model:search="purchaseSearch"
            :options="purchaseOptions"
            :placeholder="t('components.dropdown.placeholder')"
            @change="getPurchaseReceipts(searchText, true, 1, purchaseReceiptLists?.meta.per_page ?? 10)"
            @search="loadPurchaseDDL"
            @clear="clearPurchaseFilter"
          />
        </div>
        <div class="col-span-12 md:col-span-3">
          <FormLabel>{{ t('views.purchase_receipt.fields.warehouse_id') }}</FormLabel>
          <FormSelectSearch
            v-model="selectedWarehouseId"
            v-model:search="warehouseSearch"
            :options="warehouseOptions"
            :placeholder="t('components.dropdown.placeholder')"
            @change="getPurchaseReceipts(searchText, true, 1, purchaseReceiptLists?.meta.per_page ?? 10)"
            @search="loadWarehouseDDL"
            @clear="clearWarehouseFilter"
          />
        </div>
        <div class="col-span-12 md:col-span-3">
          <FormLabel>{{ t('views.purchase_receipt.fields.is_from_direct_purchase') }}</FormLabel>
          <FormSelectSearch
            v-model="selectedIsFromDirectPurchase"
            :options="directPurchaseOptions"
            :placeholder="t('components.dropdown.placeholder')"
            @change="getPurchaseReceipts(searchText, true, 1, purchaseReceiptLists?.meta.per_page ?? 10)"
            @clear="getPurchaseReceipts(searchText, true, 1, purchaseReceiptLists?.meta.per_page ?? 10)"
          />
        </div>
        <div class="col-span-12 md:col-span-3">
          <FormLabel>{{ t('views.purchase_receipt.fields.is_posted') }}</FormLabel>
          <FormSelectSearch
            v-model="selectedIsPosted"
            :options="postedOptions"
            :placeholder="t('components.dropdown.placeholder')"
            @change="getPurchaseReceipts(searchText, true, 1, purchaseReceiptLists?.meta.per_page ?? 10)"
            @clear="getPurchaseReceipts(searchText, true, 1, purchaseReceiptLists?.meta.per_page ?? 10)"
          />
        </div>
      </div>

      <DataListFlex
        :data="purchaseReceiptLists"
        :title="t('views.purchase_receipt.table.title')"
        :enable-search="true"
        :can-print="true"
        :can-export="true"
        :rows="purchaseReceiptLists?.data ?? []"
        row-class="bg-white dark:bg-darkmode-600"
        :pagination="purchaseReceiptLists ? purchaseReceiptLists.meta : null"
        @dataListChanged="handleDataListChange"
      >
        <template #row="{ item, index }">
          <div class="col-span-12 self-start lg:col-span-5">
            <div class="space-y-2">
              <div class="text-xs font-semibold uppercase tracking-wide text-primary">
                {{ t('views.purchase_receipt.page_title') }}
              </div>
              <div class="grid grid-cols-12 items-center gap-x-3 gap-y-2 text-xs">
                <div class="col-span-4 text-slate-500">{{ t('views.purchase_receipt.fields.code') }}</div>
                <div class="col-span-8 break-words text-slate-700 dark:text-slate-200">{{ (item as PurchaseReceipt).code ?? '-' }}</div>
                <div class="col-span-4 text-slate-500">{{ t('views.purchase_receipt.fields.date') }}</div>
                <div class="col-span-8 break-words text-slate-700 dark:text-slate-200">
                  {{ (item as PurchaseReceipt).date ? formatDate((item as PurchaseReceipt).date, 'DD-MMM-YYYY HH:mm:ss') : '-' }}
                </div>
                <div class="col-span-4 text-slate-500">{{ t('views.purchase_receipt.fields.supplier_id') }}</div>
                <div class="col-span-8 break-words text-slate-700 dark:text-slate-200">{{ (item as PurchaseReceipt).supplier?.name ?? '-' }}</div>
                <div class="col-span-4 text-slate-500">{{ t('views.purchase_receipt.fields.purchase_id') }}</div>
                <div class="col-span-8 break-words text-slate-700 dark:text-slate-200">{{ (item as PurchaseReceipt).purchase?.code ?? '-' }}</div>
                <div class="col-span-4 text-slate-500">{{ t('views.purchase_receipt.fields.warehouse_id') }}</div>
                <div class="col-span-8 break-words text-slate-700 dark:text-slate-200">{{ (item as PurchaseReceipt).warehouse?.name ?? '-' }}</div>
              </div>
            </div>
          </div>

          <div class="col-span-12 self-start lg:col-span-3">
            <div class="space-y-2">
              <div class="text-xs font-semibold uppercase tracking-wide text-primary">
                {{ t('views.purchase_receipt.field_groups.status') }}
              </div>
              <div class="grid grid-cols-12 items-center gap-x-3 gap-y-2 text-xs">
                <div class="col-span-7 text-slate-500">{{ t('views.purchase_receipt.fields.is_from_direct_purchase') }}</div>
                <div class="col-span-5 text-right text-slate-700 dark:text-slate-200">
                  {{ (item as PurchaseReceipt).is_from_direct_purchase ? t('components.buttons.yes') : t('components.buttons.no') }}
                </div>
                <div class="col-span-7 text-slate-500">{{ t('views.purchase_receipt.fields.is_linked_to_purchase') }}</div>
                <div class="col-span-5 text-right text-slate-700 dark:text-slate-200">
                  {{ (item as PurchaseReceipt).is_linked_to_purchase ? t('components.buttons.yes') : t('components.buttons.no') }}
                </div>
                <div class="col-span-7 text-slate-500">{{ t('views.purchase_receipt.fields.is_posted') }}</div>
                <div class="col-span-5 text-right text-slate-700 dark:text-slate-200">
                  {{ (item as PurchaseReceipt).is_posted ? t('components.buttons.yes') : t('components.buttons.no') }}
                </div>
                <div class="col-span-7 text-slate-500">{{ t('views.purchase_receipt.fields.item_count') }}</div>
                <div class="col-span-5 text-right text-slate-700 dark:text-slate-200">{{ (item as PurchaseReceipt).items?.length ?? 0 }}</div>
              </div>
            </div>
          </div>

          <div class="col-span-12 self-start lg:col-span-3">
            <div class="space-y-2">
              <div class="text-xs font-semibold uppercase tracking-wide text-primary">
                {{ t('views.purchase_receipt.field_groups.remarks') }}
              </div>
              <div class="rounded-md border border-slate-200/70 p-3 text-xs text-slate-700 dark:border-darkmode-400 dark:text-slate-200">
                {{ (item as PurchaseReceipt).remarks?.trim() || '-' }}
              </div>
            </div>
          </div>

          <div class="col-span-12 flex items-center justify-end gap-2 self-center pt-2 md:col-span-1 md:flex-col lg:col-span-1 lg:flex-col">
            <Button size="sm" variant="outline-secondary" class="flex items-center gap-1" @click="viewSelected(index)">
              <Lucide icon="Info" class="h-4 w-4" />
            </Button>
            <Button size="sm" variant="outline-secondary" class="flex items-center gap-1" @click="editSelected(index)">
              <Lucide icon="Pen" class="h-4 w-4" />
            </Button>
            <Button size="sm" variant="outline-secondary" class="flex items-center gap-1" @click="deleteSelected(index)">
              <Lucide icon="Trash2" class="h-4 w-4 text-danger" />
            </Button>
          </div>

          <div v-if="expandDetail === index" class="col-span-12 mt-2 border-t border-slate-200 pt-3 dark:border-darkmode-400">
            <div class="space-y-3">
              <div class="text-xs font-semibold uppercase tracking-wide text-primary">
                {{ t('views.purchase_receipt.field_groups.items') }}
              </div>
              <div v-if="!(item as PurchaseReceipt).items?.length" class="text-xs text-slate-500">
                {{ t('views.purchase_receipt.fields.items_empty') }}
              </div>
              <div
                v-for="(receiptItem, itemIndex) in (item as PurchaseReceipt).items ?? []"
                :key="receiptItem.ulid ?? `${(item as PurchaseReceipt).ulid}-item-${itemIndex}`"
                class="rounded-md border border-slate-200/70 p-3 dark:border-darkmode-400"
              >
                <div class="grid grid-cols-12 gap-x-3 gap-y-2 text-xs">
                  <div class="col-span-12 font-medium text-slate-700 dark:text-slate-200">
                    {{ receiptItem.product_unit?.product?.name ?? receiptItem.product_unit?.code ?? '-' }}
                  </div>
                  <div class="col-span-4 text-slate-500">{{ t('views.purchase_receipt.fields.product_unit_id') }}</div>
                  <div class="col-span-8 text-slate-700 dark:text-slate-200">{{ receiptItem.product_unit?.code ?? '-' }}</div>
                  <div class="col-span-4 text-slate-500">{{ t('views.purchase_receipt.fields.qty') }}</div>
                  <div class="col-span-8 text-slate-700 dark:text-slate-200">{{ receiptItem.qty ?? 0 }}</div>
                  <div class="col-span-4 text-slate-500">{{ t('views.purchase_receipt.fields.product_unit_conversion_value') }}</div>
                  <div class="col-span-8 text-slate-700 dark:text-slate-200">{{ receiptItem.product_unit_conversion_value ?? 0 }}</div>
                  <div class="col-span-4 text-slate-500">{{ t('views.purchase_receipt.fields.serial_count') }}</div>
                  <div class="col-span-8 text-slate-700 dark:text-slate-200">{{ receiptItem.serials?.length ?? 0 }}</div>
                  <div class="col-span-4 text-slate-500">{{ t('views.purchase_receipt.fields.remarks') }}</div>
                  <div class="col-span-8 break-words text-slate-700 dark:text-slate-200">{{ receiptItem.remarks?.trim() || '-' }}</div>
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
        <Lucide icon="XCircle" class="mx-auto mt-3 h-16 w-16 text-danger" />
        <div class="mt-5 text-3xl">Are you sure?</div>
        <div class="mt-2 text-slate-500">This action cannot be undone.</div>
      </div>
      <div class="px-5 pb-8 text-center">
        <Button variant="outline-secondary" type="button" class="mr-1 w-24" @click="deleteModalShow = false">
          {{ t('components.buttons.cancel') }}
        </Button>
        <Button variant="danger" type="button" class="w-24" @click="confirmDelete">
          {{ t('components.buttons.delete') }}
        </Button>
      </div>
    </Dialog.Panel>
  </Dialog>
</template>
