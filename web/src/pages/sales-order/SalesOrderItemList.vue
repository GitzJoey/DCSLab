<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import { FormInput, FormInputDateTime, FormLabel, FormSelectSearch } from '@/components/Base/Form';
import { DataListFlex } from '@/components/DataList';
import Button from '@/components/Base/Button';
import Lucide from '@/components/Base/Lucide';
import ProductImagePreview from '@/components/Product/ProductImagePreview.vue';
import SalesOrderItemService from '@/services/SalesOrderItemService';
import SalesOrderService from '@/services/SalesOrderService';
import CustomerService from '@/services/CustomerService';
import ProductCategoryService from '@/services/ProductCategoryService';
import BrandService from '@/services/BrandService';
import { useSelectedUserLocationStore } from '@/stores/selected-user-location';
import { ErrorCode } from '@/types/enums/ErrorCode';
import { ViewMode } from '@/types/enums/ViewMode';
import type { SalesOrderItem } from '@/types/models/SalesOrderItem';
import type { DropDownOption } from '@/types/models/DropDownOption';
import type { NotificationData } from '@/types/models/NotificationData';
import type { Collection } from '@/types/resources/Collection';
import type { DataListEmittedData } from '@/components/DataList/DataList.vue';
import type { SalesOrderItemReadAnyPaginateRequest } from '@/types/services/sales-order-item/SalesOrderItemRequest';
import type { ServiceResponse } from '@/types/services/ServiceResponse';
import type { AlertPlaceholderProps } from '@/components/AlertPlaceholder/AlertPlaceholder.vue';
import { formatCurrency, formatDate } from '@/utils/helper';

const { t } = useI18n();
const router = useRouter();
const salesOrderItemService = new SalesOrderItemService();
const salesOrderService = new SalesOrderService();
const customerService = new CustomerService();
const productCategoryService = new ProductCategoryService();
const brandService = new BrandService();
const selectedUserLocationStore = useSelectedUserLocationStore();

const emits = defineEmits([
  'mode-state',
  'loading-state',
  'show-alertplaceholder',
  'show-notification',
]);

const expandDetail = ref<number | null>(null);
const startDate = ref<string | null>(null);
const endDate = ref<string | null>(null);
const searchText = ref('');
const salesOrderCode = ref('');
const productUnitCode = ref('');
const productName = ref('');
const selectedCustomerId = ref<string | null>(null);
const selectedProductCategoryId = ref<string | null>(null);
const selectedProductBrandId = ref<string | null>(null);
const customerSearch = ref('');
const productCategorySearch = ref('');
const productBrandSearch = ref('');
const showAdvancedFilters = ref(false);

const salesOrderItemLists = ref<Collection<Array<SalesOrderItem>> | null>({
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

const customerDDL = ref<Array<DropDownOption> | null>(null);
const progressStatusDDL = ref<Array<DropDownOption> | null>(null);
const productCategoryDDL = ref<Array<DropDownOption> | null>(null);
const productBrandDDL = ref<Array<DropDownOption> | null>(null);

const customerOptions = computed(() => (customerDDL.value ?? []).map((item) => ({ value: item.code, label: item.name })));
const progressStatusLabelMap = computed<Record<string, string>>(() =>
  Object.fromEntries(
    (progressStatusDDL.value ?? []).map((item) => [String(item.code), item.name]),
  ),
);
const productCategoryOptions = computed(() => (productCategoryDDL.value ?? []).map((item) => ({ value: item.code, label: item.name })));
const productBrandOptions = computed(() => (productBrandDDL.value ?? []).map((item) => ({ value: item.code, label: item.name })));

const formatQuantityValue = (value: number | string, precision = 4) =>
  new Intl.NumberFormat('id-ID', {
    minimumFractionDigits: 0,
    maximumFractionDigits: precision,
  }).format(Number(value ?? 0));

const getProgressStatusBadgeClass = (status: string | null | undefined) => {
  switch (status) {
    case 'matched':
      return 'bg-success/15 text-success';
    case 'unmatched':
      return 'bg-warning/15 text-warning';
    case 'unlinked':
    default:
      return 'bg-slate-200/80 text-slate-700 dark:bg-darkmode-400 dark:text-slate-200';
  }
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

  const now = new Date();
  const startOfMonth = new Date(now.getFullYear(), now.getMonth(), 1, 0, 0, 0);
  const endOfMonth = new Date(now.getFullYear(), now.getMonth() + 1, 0, 23, 59, 59);
  startDate.value = formatDate(startOfMonth.toString(), 'YYYY-MM-DD HH:mm:ss');
  endDate.value = formatDate(endOfMonth.toString(), 'YYYY-MM-DD HH:mm:ss');

  await Promise.all([
    loadCustomerDDL(),
    loadProgressStatusDDL(),
    loadProductCategoryDDL(),
    loadProductBrandDDL(),
  ]);
  await getSalesOrderItems('', true, 1, 10);
});

const getSalesOrderItems = async (search: string, refresh: boolean, page: number, perPage: number) => {
  emits('loading-state', true);
  searchText.value = search;

  const request: SalesOrderItemReadAnyPaginateRequest = {
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    branch_id: selectedUserLocation.value.branch.id,
    search,
    sales_order_code: salesOrderCode.value || undefined,
    sales_order_start_date: startDate.value || undefined,
    sales_order_end_date: endDate.value || undefined,
    sales_order_customer_id: selectedCustomerId.value,
    product_unit_code: productUnitCode.value || undefined,
    product_unit_product_name: productName.value || undefined,
    product_unit_product_category_id: selectedProductCategoryId.value,
    product_unit_product_brand_id: selectedProductBrandId.value,
    refresh,
    page,
    per_page: perPage,
  };

  const result = (await salesOrderItemService.readAnyPaginate(
    request,
  )) as ServiceResponse<Collection<Array<SalesOrderItem>> | null>;

  if (result.success && result.data) {
    salesOrderItemLists.value = result.data;
  }

  emits('loading-state', false);
};

const loadCustomerDDL = async (search = '') => {
  if (!selectedUserLocation.value) return;

  const result = await customerService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    search,
    status: undefined,
    include_id: selectedCustomerId.value ?? undefined,
    refresh: false,
    limit: 100,
  });

  if (result.success && result.data) {
    customerDDL.value = result.data.data.map((item: any) => ({ code: item.id, name: item.name }));
  }
};

const loadProgressStatusDDL = async () => {
  const result = await salesOrderService.readProgressStatuses();

  if (result.success && result.data) {
    progressStatusDDL.value = result.data;
  }
};

const loadProductCategoryDDL = async (search = '') => {
  if (!selectedUserLocation.value) return;

  const result = await productCategoryService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    search,
    type: 1,
    refresh: false,
    limit: 100,
  });

  if (result.success && result.data) {
    productCategoryDDL.value = result.data.data.map((item: any) => ({ code: item.id, name: item.name }));
  }
};

const loadProductBrandDDL = async (search = '') => {
  if (!selectedUserLocation.value) return;

  const result = await brandService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    search,
    refresh: false,
    limit: 100,
  });

  if (result.success && result.data) {
    productBrandDDL.value = result.data.data.map((item: any) => ({ code: item.id, name: item.name }));
  }
};

const handleDataListChange = async (emittedData: DataListEmittedData) => {
  await getSalesOrderItems(
    emittedData.search.text,
    true,
    emittedData.pagination.page,
    emittedData.pagination.per_page,
  );
};

const handleFilterChange = async () => {
  await getSalesOrderItems(searchText.value, true, 1, salesOrderItemLists.value?.meta.per_page ?? 10);
};

const clearCustomerFilter = async () => {
  selectedCustomerId.value = null;
  await handleFilterChange();
};

const clearProductCategoryFilter = async () => {
  selectedProductCategoryId.value = null;
  await handleFilterChange();
};

const clearProductBrandFilter = async () => {
  selectedProductBrandId.value = null;
  await handleFilterChange();
};

const toggleAdvancedFilters = async () => {
  showAdvancedFilters.value = !showAdvancedFilters.value;
  if (showAdvancedFilters.value) {
    await Promise.all([
      loadCustomerDDL(customerSearch.value),
      loadProductCategoryDDL(productCategorySearch.value),
      loadProductBrandDDL(productBrandSearch.value),
    ]);
  }
};

const viewSelected = (index: number) => {
  expandDetail.value = expandDetail.value === index ? null : index;
};

const editSelected = (index: number) => {
  const ulid = salesOrderItemLists.value?.data?.[index]?.sales_order?.ulid;
  if (!ulid) return;

  router.push({
    name: 'side-menu-sales-order-edit',
    params: { ulid },
  });
};

const getProductMainImageUrl = (item: SalesOrderItem): string | null => {
  const images = item.product_unit?.product?.product_images ?? [];
  if (images.length === 0) return null;

  const mainImage = images.find((img) => img.is_main);
  if (mainImage?.url) return mainImage.url;

  return images[0]?.url ?? null;
};

const getBaseUnitName = (item: SalesOrderItem): string => {
  const units = item.product_unit?.product?.product_units ?? [];
  const baseUnit =
    units.find((unit) => unit.is_base) ??
    units.find((unit) => Number(unit.conversion_value) === 1) ??
    units.find((unit) => unit.is_primary_unit) ??
    null;

  return baseUnit?.unit?.name ?? '';
};

const getBaseQtyDisplay = (qtyBase: number | string, item: SalesOrderItem): string => {
  const baseUnitName = getBaseUnitName(item);
  const qty = formatQuantityValue(qtyBase);

  return baseUnitName ? `${qty} ${baseUnitName}` : qty;
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
  const notification: NotificationData = { title, content };
  emits('show-notification', notification);
};
</script>

<template>
  <div class="grid grid-cols-12 gap-6 mt-5">
    <div class="col-span-12">
      <div class="grid grid-cols-12 gap-4 gap-y-3 mb-3">
        <div class="col-span-12 md:col-span-6 lg:col-span-3">
          <FormLabel>{{ t('views.sales_order.fields.start_date') }}</FormLabel>
          <FormInputDateTime v-model="startDate" @change="handleFilterChange" />
        </div>
        <div class="col-span-12 md:col-span-6 lg:col-span-3">
          <FormLabel>{{ t('views.sales_order.fields.end_date') }}</FormLabel>
          <FormInputDateTime v-model="endDate" @change="handleFilterChange" />
        </div>
        <div class="col-span-12 md:col-span-12 lg:col-span-2 flex items-end">
          <Button variant="soft-secondary" class="shadow-sm" @click="toggleAdvancedFilters">
            <Lucide icon="Filter" class="w-4 h-5" />
          </Button>
        </div>
      </div>

      <div v-if="showAdvancedFilters" class="grid grid-cols-12 gap-4 gap-y-3 mb-3">
        <div class="col-span-12 md:col-span-6 lg:col-span-2">
          <FormLabel>{{ t('views.sales_order.fields.code') }}</FormLabel>
          <FormInput v-model="salesOrderCode" type="text" @change="handleFilterChange" />
        </div>
        <div class="col-span-12 md:col-span-6 lg:col-span-2">
          <FormLabel>{{ t('views.sales_order.fields.customer_id') }}</FormLabel>
          <FormSelectSearch
            v-model="selectedCustomerId"
            v-model:search="customerSearch"
            :options="customerOptions"
            :placeholder="t('components.dropdown.placeholder')"
            @change="handleFilterChange"
            @search="loadCustomerDDL"
            @clear="clearCustomerFilter"
          />
        </div>
        <div class="col-span-12 md:col-span-6 lg:col-span-2">
          <FormLabel>{{ t('views.sales_order.fields.product_unit_code') }}</FormLabel>
          <FormInput v-model="productUnitCode" type="text" @change="handleFilterChange" />
        </div>
        <div class="col-span-12 md:col-span-6 lg:col-span-2">
          <FormLabel>{{ t('views.stock_transfer.fields.product_name') }}</FormLabel>
          <FormInput v-model="productName" type="text" @change="handleFilterChange" />
        </div>
        <div class="col-span-12 md:col-span-6 lg:col-span-2">
          <FormLabel>{{ t('views.product.fields.category_id') }}</FormLabel>
          <FormSelectSearch
            v-model="selectedProductCategoryId"
            v-model:search="productCategorySearch"
            :options="productCategoryOptions"
            :placeholder="t('components.dropdown.placeholder')"
            @change="handleFilterChange"
            @search="loadProductCategoryDDL"
            @clear="clearProductCategoryFilter"
          />
        </div>
        <div class="col-span-12 md:col-span-6 lg:col-span-2">
          <FormLabel>{{ t('views.product.fields.brand_id') }}</FormLabel>
          <FormSelectSearch
            v-model="selectedProductBrandId"
            v-model:search="productBrandSearch"
            :options="productBrandOptions"
            :placeholder="t('components.dropdown.placeholder')"
            @change="handleFilterChange"
            @search="loadProductBrandDDL"
            @clear="clearProductBrandFilter"
          />
        </div>
      </div>

      <DataListFlex
        :data="salesOrderItemLists"
        :enable-search="true"
        :can-print="true"
        :can-export="true"
        :rows="salesOrderItemLists?.data ?? []"
        row-class="bg-white dark:bg-darkmode-600"
        :pagination="salesOrderItemLists ? salesOrderItemLists.meta : null"
        @dataListChanged="handleDataListChange"
      >
        <template #row="{ item, index }">
          <div class="col-span-12 md:col-span-12 lg:col-span-4 self-start">
            <div class="space-y-2">
              <div class="text-primary text-xs font-semibold uppercase tracking-wide">
                {{ t('views.sales_order.page_title') }}
              </div>
              <div class="grid grid-cols-12 items-center gap-x-3 gap-y-2 text-xs">
                <div class="col-span-4 text-slate-500">{{ t('views.sales_order.fields.code') }}</div>
                <div class="col-span-8 text-slate-700 dark:text-slate-200 break-words">
                  {{ (item as SalesOrderItem).sales_order?.code ?? '-' }}
                </div>
                <div class="col-span-4 text-slate-500">{{ t('views.sales_order.fields.date') }}</div>
                <div class="col-span-8 text-slate-700 dark:text-slate-200 break-words">
                  {{ (item as SalesOrderItem).sales_order?.date ? formatDate((item as SalesOrderItem).sales_order?.date as string, 'DD-MMM-YYYY HH:mm:ss') : '-' }}
                </div>
                <div class="col-span-4 text-slate-500">{{ t('views.sales_order.fields.customer_id') }}</div>
                <div class="col-span-8 text-slate-700 dark:text-slate-200 break-words">
                  {{ (item as SalesOrderItem).sales_order?.customer?.name ?? '-' }}
                </div>
                <div class="col-span-4 text-slate-500">{{ t('views.sales_order.fields.due_days') }}</div>
                <div class="col-span-8 text-slate-700 dark:text-slate-200">
                  {{ (item as SalesOrderItem).sales_order?.due_days ?? 0 }}
                </div>
                <div class="col-span-4 text-slate-500">{{ t('views.sales_order.fields.progress_status') }}</div>
                <div class="col-span-8">
                  <span
                    class="inline-flex rounded-full px-2 py-1 text-[11px] font-medium"
                    :class="getProgressStatusBadgeClass((item as SalesOrderItem).sales_order?.progress_status)"
                  >
                    {{ t(progressStatusLabelMap[(item as SalesOrderItem).sales_order?.progress_status ?? '']
                      ?? 'views.sales_order.filters.progress_status_unlinked') }}
                  </span>
                </div>
                <div class="col-span-4 text-slate-500">{{ t('views.sales_order.fields.remarks') }}</div>
                <div class="col-span-8 text-slate-700 dark:text-slate-200 break-words">
                  {{ (item as SalesOrderItem).sales_order?.remarks?.trim() || '-' }}
                </div>
              </div>
            </div>
          </div>

          <div class="col-span-12 md:col-span-6 lg:col-span-4 self-start md:pr-3 lg:pr-4">
            <div class="space-y-3">
              <div class="text-primary text-xs font-semibold uppercase tracking-wide">
                {{ t('views.sales_order.field_groups.items') }}
              </div>
              <div class="flex items-start gap-3">
                <ProductImagePreview
                  :image-url="getProductMainImageUrl(item as SalesOrderItem)"
                  wrapper-class="w-14 h-14 rounded-md overflow-hidden bg-slate-100 dark:bg-darkmode-600 flex items-center justify-center cursor-zoom-in shrink-0"
                  image-class="object-cover w-full h-full"
                  empty-icon-class="w-5 h-5 text-slate-400"
                />
                <div class="min-w-0 flex-1">
                  <div class="font-medium text-slate-700 dark:text-slate-200 break-words">
                    {{ (item as SalesOrderItem).product_unit?.product?.name ?? '-' }}
                  </div>
                  <div class="mt-1 text-xs text-slate-500 break-words">
                    {{ (item as SalesOrderItem).product_unit?.code ?? '-' }}
                  </div>
                </div>
              </div>
              <div class="grid grid-cols-12 items-center gap-x-3 gap-y-2 text-xs">
                <div class="col-span-4 text-slate-500">{{ t('views.product.fields.category_id') }}</div>
                <div class="col-span-8 text-slate-700 dark:text-slate-200 break-words">
                  {{ (item as SalesOrderItem).product_unit?.product?.category?.name ?? '-' }}
                </div>
                <div class="col-span-4 text-slate-500">{{ t('views.product.fields.brand_id') }}</div>
                <div class="col-span-8 text-slate-700 dark:text-slate-200 break-words">
                  {{ (item as SalesOrderItem).product_unit?.product?.brand?.name ?? '-' }}
                </div>
                <div class="col-span-4 text-slate-500">{{ t('views.sales_order.fields.vat_profile_id') }}</div>
                <div class="col-span-8 text-slate-700 dark:text-slate-200 break-words">{{ (item as SalesOrderItem).vat_profile?.name ?? '-' }}</div>
                <div class="col-span-4 text-slate-500">{{ t('views.sales_order.fields.vat_rate') }}</div>
                <div class="col-span-8 text-slate-700 dark:text-slate-200 break-words">{{ formatQuantityValue((item as SalesOrderItem).vat_rate ?? 0) }}</div>
                <div class="col-span-4 text-slate-500">{{ t('views.sales_order.fields.vat_base_fraction') }}</div>
                <div class="col-span-8 text-slate-700 dark:text-slate-200 break-words">
                  {{ `${formatQuantityValue((item as SalesOrderItem).vat_base_numerator ?? 0)}/${formatQuantityValue((item as SalesOrderItem).vat_base_denominator ?? 0)}` }}
                </div>
              </div>
            </div>
          </div>

          <div class="col-span-12 md:col-span-5 lg:col-span-3 self-start md:pl-3 lg:pl-4">
            <div class="space-y-2">
              <div class="text-primary text-xs font-semibold uppercase tracking-wide">
                {{ t('views.sales_order.field_groups.summary') }}
              </div>
              <div class="grid grid-cols-12 items-center gap-x-3 gap-y-2 text-xs">
                <div class="col-span-7 text-slate-500">{{ t('views.sales_order.fields.qty') }}</div>
                <div class="col-span-5 text-right text-slate-700 dark:text-slate-200 break-words">
                  {{ `${formatQuantityValue((item as SalesOrderItem).qty ?? 0)} ${(item as SalesOrderItem).product_unit?.unit?.name ?? ''}`.trim() || '-' }}
                </div>
                <div class="col-span-7 text-slate-500">{{ t('views.sales_order.fields.product_unit_price') }}</div>
                <div class="col-span-5 text-right text-slate-700 dark:text-slate-200">{{ formatCurrency((item as SalesOrderItem).product_unit_price ?? 0) }}</div>
                <div class="col-span-7 text-slate-500">{{ t('views.sales_order.fields.price_discount') }}</div>
                <div class="col-span-5 text-right text-slate-700 dark:text-slate-200">{{ formatCurrency((item as SalesOrderItem).price_discount ?? 0) }}</div>
                <div class="col-span-7 text-slate-500">{{ t('views.sales_order.fields.subtotal_discount') }}</div>
                <div class="col-span-5 text-right text-slate-700 dark:text-slate-200">{{ formatCurrency((item as SalesOrderItem).subtotal_discount ?? 0) }}</div>
                <div class="col-span-7 text-slate-500">{{ t('views.sales_order.fields.subtotal_after_discount') }}</div>
                <div class="col-span-5 text-right text-slate-700 dark:text-slate-200">{{ formatCurrency((item as SalesOrderItem).subtotal_after_discount ?? 0) }}</div>
                <div class="col-span-7 text-slate-500">{{ t('views.sales_order.fields.vat') }}</div>
                <div class="col-span-5 text-right text-slate-700 dark:text-slate-200">{{ formatCurrency((item as SalesOrderItem).vat ?? 0) }}</div>
                <div class="col-span-7 text-slate-500">{{ t('views.sales_order.fields.qty_delivered_base') }}</div>
                <div class="col-span-5 text-right text-slate-700 dark:text-slate-200">{{ getBaseQtyDisplay((item as
                  SalesOrderItem).qty_delivered_base ?? 0, item as SalesOrderItem) }}</div>
                <div class="col-span-7 text-slate-500">{{ t('views.sales_order.fields.qty_invoiced_base') }}</div>
                <div class="col-span-5 text-right text-slate-700 dark:text-slate-200">{{ getBaseQtyDisplay((item as
                  SalesOrderItem).qty_invoiced_base ?? 0, item as SalesOrderItem) }}</div>
                <div class="col-span-7 text-slate-500">{{ t('views.sales_order.fields.qty_outstanding_base') }}</div>
                <div class="col-span-5 text-right text-warning">{{ getBaseQtyDisplay((item as
                  SalesOrderItem).qty_outstanding_base ?? 0, item as SalesOrderItem) }}</div>
                <div class="col-span-7 text-slate-500">{{ t('views.sales_order.fields.qty_excess_base') }}</div>
                <div class="col-span-5 text-right text-danger">{{ getBaseQtyDisplay((item as SalesOrderItem).qty_excess_base
                  ?? 0, item as SalesOrderItem) }}</div>
                <div class="col-span-7 text-primary font-medium">{{ t('views.sales_order.fields.amount_payable') }}</div>
                <div class="col-span-5 text-right text-primary font-medium">{{ formatCurrency((item as SalesOrderItem).amount_payable ?? 0) }}</div>
              </div>
            </div>
          </div>

          <div class="col-span-12 lg:col-span-1 md:col-span-12 self-center flex justify-end items-center gap-2 pt-2 md:flex-col lg:flex-col">
            <Button size="sm" variant="outline-secondary" class="flex items-center gap-1" @click="viewSelected(index)">
              <Lucide icon="Info" class="w-4 h-4" />
            </Button>
            <Button size="sm" variant="outline-secondary" class="flex items-center gap-1" @click="editSelected(index)">
              <Lucide icon="Pen" class="w-4 h-4" />
            </Button>
          </div>

          <div v-if="expandDetail === index" class="col-span-12 border-t border-slate-200 dark:border-darkmode-400 mt-2 pt-3">
            <div class="grid grid-cols-12 gap-4">
              <div class="col-span-12 lg:col-span-6 space-y-3">
                <div class="text-primary text-xs font-semibold uppercase tracking-wide">
                  {{ t('views.sales_order.field_groups.item_additional_details') }}
                </div>
                <div class="grid grid-cols-12 items-center gap-x-3 gap-y-2 text-xs">
                  <div class="col-span-4 text-slate-500">{{ t('views.sales_order.fields.product_unit_conversion_value') }}</div>
                  <div class="col-span-8 text-right text-slate-700 dark:text-slate-200">{{ formatQuantityValue((item as SalesOrderItem).product_unit_conversion_value ?? 0) }}</div>
                  <div class="col-span-4 text-slate-500">{{ t('views.sales_order.fields.item_total_count') }}</div>
                  <div class="col-span-8 text-right text-slate-700 dark:text-slate-200">{{ formatQuantityValue((item as
                    SalesOrderItem).sales_order?.item_total_count ?? 0, 0) }}</div>
                  <div class="col-span-4 text-slate-500">{{ t('views.sales_order.fields.item_matched_count') }}</div>
                  <div class="col-span-8 text-right text-success">{{ formatQuantityValue((item as
                    SalesOrderItem).sales_order?.item_matched_count ?? 0, 0) }}</div>
                  <div class="col-span-4 text-slate-500">{{ t('views.sales_order.fields.item_less_count') }}</div>
                  <div class="col-span-8 text-right text-warning">{{ formatQuantityValue((item as
                    SalesOrderItem).sales_order?.item_less_count ?? 0, 0) }}</div>
                  <div class="col-span-4 text-slate-500">{{ t('views.sales_order.fields.item_more_count') }}</div>
                  <div class="col-span-8 text-right text-danger">{{ formatQuantityValue((item as
                    SalesOrderItem).sales_order?.item_more_count ?? 0, 0) }}</div>
                  <div class="col-span-4 text-slate-500">{{ t('views.sales_order.fields.item_unlinked_count') }}</div>
                  <div class="col-span-8 text-right text-slate-700 dark:text-slate-200">{{ formatQuantityValue((item as
                    SalesOrderItem).sales_order?.item_unlinked_count ?? 0, 0) }}</div>
                  <div class="col-span-4 text-slate-500">{{ t('views.sales_order.fields.global_discount') }}</div>
                  <div class="col-span-8 text-right text-slate-700 dark:text-slate-200">{{ formatCurrency((item as SalesOrderItem).global_discount ?? 0) }}</div>
                  <div class="col-span-4 text-slate-500">{{ t('views.sales_order.fields.product_unit_is_price_include_vat') }}</div>
                  <div class="col-span-8 text-right text-slate-700 dark:text-slate-200">{{ (item as SalesOrderItem).product_unit_is_price_include_vat ? 'Yes' : 'No' }}</div>
                </div>
              </div>
              <div class="col-span-12 lg:col-span-6 space-y-3">
                <div class="text-primary text-xs font-semibold uppercase tracking-wide">
                  {{ t('views.sales_order.fields.remarks') }}
                </div>
                <div class="rounded-md border border-slate-200/60 dark:border-darkmode-400 px-3 py-2 text-slate-700 dark:text-slate-200 break-words min-h-[72px]">
                  {{ (item as SalesOrderItem).remarks?.trim() || '-' }}
                </div>
              </div>
            </div>
          </div>
        </template>
      </DataListFlex>
    </div>
  </div>
</template>
