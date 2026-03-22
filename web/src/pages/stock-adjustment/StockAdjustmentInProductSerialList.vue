<script setup lang="ts">
  import { computed, onMounted, ref } from 'vue';
  import { useI18n } from 'vue-i18n';
  import { DataListFlex } from '@/components/DataList';
  import Button from '@/components/Base/Button';
  import Lucide from '@/components/Base/Lucide';
  import { Dialog } from '@/components/Base/Headless';
  import StockAdjustmentInProductSerialService from '@/services/StockAdjustmentInProductSerialService';
  import { StockAdjustmentInProductSerial } from '@/types/models/StockAdjustmentInProductSerial';
  import { NotificationData } from '@/types/models/NotificationData';
  import { Collection } from '@/types/resources/Collection';
  import { DataListEmittedData } from '@/components/DataList/DataList.vue';
  import { ServiceResponse } from '@/types/services/ServiceResponse';
  import { StockAdjustmentInProductSerialReadAnyPaginateRequest } from '@/types/services/stock-adjustment-in-product-serial/StockAdjustmentInProductSerialRequest';
  import { useRouter } from 'vue-router';
  import { ViewMode } from '@/types/enums/ViewMode';
  import { useSelectedUserLocationStore } from '@/stores/selected-user-location';
  import { ErrorCode } from '@/types/enums/ErrorCode';
  import type { AlertPlaceholderProps } from '@/components/AlertPlaceholder/AlertPlaceholder.vue';
  import ProductImagePreview from '@/components/Product/ProductImagePreview.vue';
  import { FormInput, FormInputDateTime, FormLabel, FormSelectSearch } from '@/components/Base/Form';
  import { formatDate } from '@/utils/helper';
  import StockAdjustmentCategoryService from '@/services/StockAdjustmentCategoryService';
  import ProductCategoryService from '@/services/ProductCategoryService';
  import BrandService from '@/services/BrandService';
  import WarehouseService from '@/services/WarehouseService';
  import { DropDownOption } from '@/types/models/DropDownOption';

  const { t } = useI18n();
  const router = useRouter();
  const stockAdjustmentInProductSerialService = new StockAdjustmentInProductSerialService();
  const stockAdjustmentCategoryService = new StockAdjustmentCategoryService();
  const productCategoryService = new ProductCategoryService();
  const brandService = new BrandService();
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
  const startDate = ref<string | null>(null);
  const endDate = ref<string | null>(null);
  const searchText = ref<string>('');
  const showAdvancedFilters = ref<boolean>(false);
  const stockAdjustmentCode = ref<string>('');
  const productUnitCode = ref<string>('');
  const productName = ref<string>('');
  const selectedCategoryId = ref<string | null>(null);
  const selectedInWarehouseId = ref<string | null>(null);
  const selectedOutWarehouseId = ref<string | null>(null);
  const selectedProductCategoryId = ref<string | null>(null);
  const selectedProductBrandId = ref<string | null>(null);

  const stockAdjustmentInProductSerialLists = ref<Collection<Array<StockAdjustmentInProductSerial>> | null>({
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
  const categoryDDL = ref<Array<DropDownOption> | null>(null);
  const categorySearch = ref<string>('');
  const categoryOptions = computed(() =>
    (categoryDDL.value ?? []).map((item) => ({
      value: item.code,
      label: item.name,
    })),
  );

  const inWarehouseDDL = ref<Array<DropDownOption> | null>(null);
  const inWarehouseSearch = ref<string>('');
  const inWarehouseOptions = computed(() =>
    (inWarehouseDDL.value ?? []).map((item) => ({
      value: item.code,
      label: item.name,
    })),
  );

  const outWarehouseDDL = ref<Array<DropDownOption> | null>(null);
  const outWarehouseSearch = ref<string>('');
  const outWarehouseOptions = computed(() =>
    (outWarehouseDDL.value ?? []).map((item) => ({
      value: item.code,
      label: item.name,
    })),
  );
  const productCategoryDDL = ref<Array<DropDownOption> | null>(null);
  const productCategorySearch = ref<string>('');
  const productCategoryOptions = computed(() =>
    (productCategoryDDL.value ?? []).map((item) => ({
      value: item.code,
      label: item.name,
    })),
  );
  const productBrandDDL = ref<Array<DropDownOption> | null>(null);
  const productBrandSearch = ref<string>('');
  const productBrandOptions = computed(() =>
    (productBrandDDL.value ?? []).map((item) => ({
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

    await getStockAdjustmentInProductSerials('', true, 1, 10);
  });

  const getStockAdjustmentInProductSerials = async (search: string, refresh: boolean, page: number, per_page: number) => {
    emits('loading-state', true);
    searchText.value = search;

    const request: StockAdjustmentInProductSerialReadAnyPaginateRequest = {
      with_trashed: false,
      company_id: selectedUserLocation.value.company.id,
      branch_id: selectedUserLocation.value.branch.id,
      search,
      stock_adjustment_code: stockAdjustmentCode.value || null,
      stock_adjustment_start_date: startDate.value || null,
      stock_adjustment_end_date: endDate.value || null,
      stock_adjustment_category_id: selectedCategoryId.value,
      stock_adjustment_in_warehouse_id: selectedInWarehouseId.value,
      stock_adjustment_out_warehouse_id: selectedOutWarehouseId.value,
      product_unit_code: productUnitCode.value || null,
      product_unit_product_name: productName.value || null,
      product_unit_product_category_id: selectedProductCategoryId.value,
      product_unit_product_brand_id: selectedProductBrandId.value,
      refresh,
      page,
      per_page,
    };

    const result: ServiceResponse<Collection<Array<StockAdjustmentInProductSerial>> | null> =
      await stockAdjustmentInProductSerialService.readAnyPaginate(request);

    if (result.success && result.data) {
      stockAdjustmentInProductSerialLists.value = result.data;
      showAlertPlaceholder('hidden', '', null);
    } else {
      showAlertPlaceholder('danger', '', result.errors as Record<string, Array<string>>);
    }

    emits('loading-state', false);
  };

  const handleDataListChange = async (data: DataListEmittedData) => {
    await getStockAdjustmentInProductSerials(
      data.search.text, 
      true, 
      data.pagination.page, 
      data.pagination.per_page
    );
  };

  const handleDateFilterChange = async () => {
    const perPage = stockAdjustmentInProductSerialLists.value?.meta.per_page || 10;
    await getStockAdjustmentInProductSerials(searchText.value, true, 1, perPage);
  };

  const handleTextFilterChange = async () => {
    const perPage = stockAdjustmentInProductSerialLists.value?.meta.per_page || 10;
    await getStockAdjustmentInProductSerials(searchText.value, true, 1, perPage);
  };

  const handleCategoryFilterChange = async () => {
    const perPage = stockAdjustmentInProductSerialLists.value?.meta.per_page || 10;
    await getStockAdjustmentInProductSerials(searchText.value, true, 1, perPage);
  };

  const handleInWarehouseFilterChange = async () => {
    const perPage = stockAdjustmentInProductSerialLists.value?.meta.per_page || 10;
    await getStockAdjustmentInProductSerials(searchText.value, true, 1, perPage);
  };

  const handleOutWarehouseFilterChange = async () => {
    const perPage = stockAdjustmentInProductSerialLists.value?.meta.per_page || 10;
    await getStockAdjustmentInProductSerials(searchText.value, true, 1, perPage);
  };

  const handleProductCategoryFilterChange = async () => {
    const perPage = stockAdjustmentInProductSerialLists.value?.meta.per_page || 10;
    await getStockAdjustmentInProductSerials(searchText.value, true, 1, perPage);
  };

  const handleProductBrandFilterChange = async () => {
    const perPage = stockAdjustmentInProductSerialLists.value?.meta.per_page || 10;
    await getStockAdjustmentInProductSerials(searchText.value, true, 1, perPage);
  };

  const loadInWarehouseDDL = async (search = '') => {
    if (!selectedUserLocation.value) return;

    const result = await warehouseService.readAnyGet({
      with_trashed: false,
      company_id: selectedUserLocation.value.company.id,
      branch_id: selectedUserLocation.value.branch.id,
      search,
      status: undefined,
      refresh: false,
      limit: 20,
    });

    if (result.success && result.data) {
      inWarehouseDDL.value = result.data.data.map((item: any) => ({
        code: item.id,
        name: item.name,
      }));
    }
  };

  const loadOutWarehouseDDL = async (search = '') => {
    if (!selectedUserLocation.value) return;

    const result = await warehouseService.readAnyGet({
      with_trashed: false,
      company_id: selectedUserLocation.value.company.id,
      branch_id: selectedUserLocation.value.branch.id,
      search,
      status: undefined,
      refresh: false,
      limit: 20,
    });

    if (result.success && result.data) {
      outWarehouseDDL.value = result.data.data.map((item: any) => ({
        code: item.id,
        name: item.name,
      }));
    }
  };

  const loadCategoryDDL = async (search = '') => {
    if (!selectedUserLocation.value) return;

    const result = await stockAdjustmentCategoryService.readAnyGet({
      with_trashed: false,
      company_id: selectedUserLocation.value.company.id,
      search,
      include_id: undefined,
      refresh: false,
      limit: 20,
    });

    if (result.success && result.data) {
      categoryDDL.value = result.data.data.map((item: any) => ({
        code: item.id,
        name: item.name,
      }));
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
      limit: 20,
    });

    if (result.success && result.data) {
      productCategoryDDL.value = result.data.data.map((item: any) => ({
        code: item.id,
        name: item.name,
      }));
    }
  };

  const loadProductBrandDDL = async (search = '') => {
    if (!selectedUserLocation.value) return;

    const result = await brandService.readAnyGet({
      with_trashed: false,
      company_id: selectedUserLocation.value.company.id,
      search,
      refresh: false,
      limit: 20,
    });

    if (result.success && result.data) {
      productBrandDDL.value = result.data.data.map((item: any) => ({
        code: item.id,
        name: item.name,
      }));
    }
  };

  const clearCategoryFilter = () => {
    selectedCategoryId.value = null;
    categorySearch.value = '';
  };

  const clearInWarehouseFilter = () => {
    selectedInWarehouseId.value = null;
    inWarehouseSearch.value = '';
  };

  const clearOutWarehouseFilter = () => {
    selectedOutWarehouseId.value = null;
    outWarehouseSearch.value = '';
  };

  const clearProductCategoryFilter = () => {
    selectedProductCategoryId.value = null;
    productCategorySearch.value = '';
  };

  const clearProductBrandFilter = () => {
    selectedProductBrandId.value = null;
    productBrandSearch.value = '';
  };

  const toggleAdvancedFilters = async () => {
    showAdvancedFilters.value = !showAdvancedFilters.value;
    if (showAdvancedFilters.value) {
      await Promise.all([
        loadCategoryDDL(categorySearch.value),
        loadInWarehouseDDL(inWarehouseSearch.value),
        loadOutWarehouseDDL(outWarehouseSearch.value),
        loadProductCategoryDDL(productCategorySearch.value),
        loadProductBrandDDL(productBrandSearch.value),
      ]);
    }
  };

  const deleteSelected = (idx: number) => {
    if (!stockAdjustmentInProductSerialLists.value) return;

    const ulid = stockAdjustmentInProductSerialLists.value.data[idx].ulid;
    deleteUlid.value = ulid;
    deleteModalShow.value = true;
  };

  const confirmDelete = async () => {
    deleteModalShow.value = false;
    emits('loading-state', true);

    const result = await stockAdjustmentInProductSerialService.delete(deleteUlid.value);

    emits('loading-state', false);

    if (result.success) {
      emits('update-profile');
      await getStockAdjustmentInProductSerials('', true, 1, 10);
      showNotification(
        t('components.delete-modal.title'),
        t('components.delete-modal.message'),
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

  const getProductMainImageUrl = (item: StockAdjustmentInProductSerial): string | null => {
    const images = item.stock_adjustment_in_product?.product_unit?.product?.product_images ?? [];
    if (images.length === 0) return null;

    const mainImage = images.find((img) => img.is_main);
    if (mainImage?.url) return mainImage.url;

    return images[0]?.url ?? null;
  };
</script>

<template>
  <div class="grid grid-cols-12 gap-6 mt-5">
    <div class="col-span-12 intro-y lg:col-span-12">
      <div class="grid grid-cols-12 gap-4 gap-y-3 mb-3 relative z-50">
        <div class="col-span-12 lg:col-span-3">
          <FormLabel>
            {{ t('views.stock_adjustment.fields.start_date') }}
          </FormLabel>
          <FormInputDateTime v-model="startDate" @change="handleDateFilterChange" />
        </div>
        <div class="col-span-12 lg:col-span-3">
          <FormLabel>
            {{ t('views.stock_adjustment.fields.end_date') }}
          </FormLabel>
          <FormInputDateTime v-model="endDate" @change="handleDateFilterChange" />
        </div>
        <div class="col-span-12 md:col-span-12 lg:col-span-2 flex items-end">
          <Button
            variant="soft-secondary"
            class="shadow-sm border-slate-300 bg-slate-100/80 hover:bg-slate-200 hover:border-slate-400 dark:border-darkmode-300 dark:bg-darkmode-300/40 dark:hover:bg-darkmode-300"
            @click="toggleAdvancedFilters"
          >
            <Lucide icon="Filter" class="w-4 h-5" />
          </Button>
        </div>
      </div>
      <div v-if="showAdvancedFilters" class="grid grid-cols-12 gap-4 gap-y-3 mb-3 relative z-50">
          <div class="col-span-12 md:col-span-6 lg:col-span-2">
            <FormLabel>
              {{ t('views.stock_adjustment.fields.stock_adjustment_code') }}
            </FormLabel>
            <FormInput v-model="stockAdjustmentCode" type="text" @change="handleTextFilterChange" />
          </div>
          <div class="col-span-12 md:col-span-6 lg:col-span-2">
            <FormLabel>
              {{ t('views.stock_adjustment.fields.category_id') }}
            </FormLabel>
            <div class="flex items-center gap-2">
              <div class="flex-1 relative z-30">
                <FormSelectSearch v-model="selectedCategoryId" v-model:search="categorySearch" :options="categoryOptions"
                  :placeholder="t('components.dropdown.placeholder')" @change="handleCategoryFilterChange"
                  @search="loadCategoryDDL" @clear="clearCategoryFilter" />
              </div>
            </div>
          </div>
          <div class="col-span-12 md:col-span-6 lg:col-span-2">
            <FormLabel>
              {{ t('views.stock_adjustment.fields.in_warehouse_id') }}
            </FormLabel>
            <div class="flex items-center gap-2">
              <div class="flex-1 relative z-30">
                <FormSelectSearch v-model="selectedInWarehouseId" v-model:search="inWarehouseSearch"
                  :options="inWarehouseOptions" :placeholder="t('components.dropdown.placeholder')"
                  @change="handleInWarehouseFilterChange" @search="loadInWarehouseDDL" @clear="clearInWarehouseFilter" />
              </div>
            </div>
          </div>
          <div class="col-span-12 md:col-span-6 lg:col-span-2">
            <FormLabel>
              {{ t('views.stock_adjustment.fields.out_warehouse_id') }}
            </FormLabel>
            <div class="flex items-center gap-2">
              <div class="flex-1 relative z-30">
                <FormSelectSearch v-model="selectedOutWarehouseId" v-model:search="outWarehouseSearch"
                  :options="outWarehouseOptions" :placeholder="t('components.dropdown.placeholder')"
                  @change="handleOutWarehouseFilterChange" @search="loadOutWarehouseDDL"
                  @clear="clearOutWarehouseFilter" />
              </div>
            </div>
          </div>
          <div class="col-span-12 md:col-span-6 lg:col-span-2">
            <FormLabel>
              {{ t('views.stock_adjustment.fields.product_unit_code') }}
            </FormLabel>
            <FormInput v-model="productUnitCode" type="text" @change="handleTextFilterChange" />
          </div>
          <div class="col-span-12 md:col-span-6 lg:col-span-2">
            <FormLabel>
              {{ t('views.stock_adjustment.fields.product_name') }}
            </FormLabel>
            <FormInput v-model="productName" type="text" @change="handleTextFilterChange" />
          </div>
          <div class="col-span-12 md:col-span-6 lg:col-span-2">
            <FormLabel>
              {{ t('views.product.fields.category_id') }}
            </FormLabel>
            <div class="flex items-center gap-2">
              <div class="flex-1 relative z-30">
                <FormSelectSearch v-model="selectedProductCategoryId" v-model:search="productCategorySearch"
                  :options="productCategoryOptions" :placeholder="t('components.dropdown.placeholder')"
                  @change="handleProductCategoryFilterChange" @search="loadProductCategoryDDL"
                  @clear="clearProductCategoryFilter" />
              </div>
            </div>
          </div>
          <div class="col-span-12 md:col-span-6 lg:col-span-2">
            <FormLabel>
              {{ t('views.product.fields.brand_id') }}
            </FormLabel>
            <div class="flex items-center gap-2">
              <div class="flex-1 relative z-30">
                <FormSelectSearch v-model="selectedProductBrandId" v-model:search="productBrandSearch"
                  :options="productBrandOptions" :placeholder="t('components.dropdown.placeholder')"
                  @change="handleProductBrandFilterChange" @search="loadProductBrandDDL"
                  @clear="clearProductBrandFilter" />
              </div>
            </div>
          </div>
      </div>
      <DataListFlex
        :data="stockAdjustmentInProductSerialLists"
        :enable-search="true"
        :can-print="true"
        :can-export="true"
        :rows="stockAdjustmentInProductSerialLists?.data ?? []"
        row-class="bg-white dark:bg-darkmode-600"
        :pagination="stockAdjustmentInProductSerialLists ? stockAdjustmentInProductSerialLists.meta : null"
        @dataListChanged="handleDataListChange"
      >
        <template #row="{ item, index }">
            <div class="col-span-12 lg:col-span-1 md:col-span-12 flex items-center justify-center md:justify-start">
              <ProductImagePreview
                :image-url="getProductMainImageUrl(item as StockAdjustmentInProductSerial)"
                wrapper-class="w-12 h-12 rounded-md overflow-hidden bg-slate-100 dark:bg-darkmode-600 flex items-center justify-center cursor-zoom-in"
                icon-class="w-4 h-4 text-slate-400"
              />
            </div>

            <div class="col-span-12 lg:col-span-4 md:col-span-5 self-start">
              <div class="text-primary text-xs font-semibold uppercase tracking-wide mb-1">
                {{ t('views.stock_adjustment.page_title') }}
              </div>
              <div class="text-slate-500 text-xs whitespace-nowrap">
                {{ t('views.stock_adjustment.fields.code') }}:
                {{ (item as StockAdjustmentInProductSerial).stock_adjustment?.code ?? '-' }}
              </div>
              <div class="text-slate-500 text-xs whitespace-nowrap">
                {{ t('views.stock_adjustment.fields.date') }}:
                {{ (item as StockAdjustmentInProductSerial).stock_adjustment?.date ? formatDate(String((item as StockAdjustmentInProductSerial).stock_adjustment?.date), 'DD-MMM-YYYY HH:mm:ss') : '-' }}
              </div>
              <div class="text-slate-500 text-xs whitespace-nowrap">
                {{ t('views.stock_adjustment.fields.category_id') }}:
                {{ (item as StockAdjustmentInProductSerial).stock_adjustment?.category?.name ?? '-' }}
              </div>
              <div class="text-slate-500 text-xs whitespace-nowrap">
                {{ t('views.stock_adjustment.fields.in_warehouse_id') }}:
                {{ (item as StockAdjustmentInProductSerial).stock_adjustment?.in_warehouse?.name ?? '-' }}
              </div>
              <div class="text-slate-500 text-xs whitespace-nowrap">
                {{ t('views.stock_adjustment.fields.out_warehouse_id') }}:
                {{ (item as StockAdjustmentInProductSerial).stock_adjustment?.out_warehouse?.name ?? '-' }}
              </div>
              <div v-if="(item as StockAdjustmentInProductSerial).stock_adjustment?.remarks?.trim()" class="text-slate-500 text-xs whitespace-nowrap">
                {{ t('views.stock_adjustment.fields.remarks') }}:
                {{ (item as StockAdjustmentInProductSerial).stock_adjustment?.remarks }}
              </div>
            </div>

            <div class="col-span-12 lg:col-span-4 md:col-span-4 self-start">
              <div class="text-primary text-xs font-semibold uppercase tracking-wide mb-1">
                {{ t('views.product.page_title') }}
              </div>
              <div class="text-slate-500 text-xs whitespace-nowrap">
                {{ t('views.product.fields.code') }}:
                {{ (item as StockAdjustmentInProductSerial).stock_adjustment_in_product?.product_unit?.product?.code ?? '-' }}
              </div>
              <div class="text-slate-500 text-xs whitespace-nowrap">
                {{ t('views.product.fields.name') }}:
                {{ (item as StockAdjustmentInProductSerial).stock_adjustment_in_product?.product_unit?.product?.name ?? '-' }}
              </div>
              <div class="text-slate-500 text-xs whitespace-nowrap">
                {{ t('views.product.fields.unit_code') }}:
                {{ (item as StockAdjustmentInProductSerial).stock_adjustment_in_product?.product_unit?.code ?? '-' }}
              </div>
            </div>

            <div class="col-span-12 lg:col-span-2 md:col-span-2 self-start">
              <div class="text-primary text-xs font-semibold uppercase tracking-wide mb-1">
                {{ t('views.product.fields.serial_number') }}
              </div>
              <div class="text-slate-500 text-xs break-all">
                {{ (item as StockAdjustmentInProductSerial).serial || '-' }}
              </div>
            </div>

            <div class="col-span-12 lg:col-span-1 md:col-span-12 flex justify-end items-center gap-2">
              <Button size="sm" variant="outline-secondary" class="flex items-center gap-1" @click="deleteSelected(index)">
                <Lucide icon="Trash2" class="w-4 h-4 text-danger" />
              </Button>
            </div>
        </template>
      </DataListFlex>
    </div>
  </div>

  <Dialog :open="deleteModalShow" @close="deleteModalShow = false">
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
        <Button type="button" variant="outline-secondary" @click="deleteModalShow = false" class="w-24 mr-1">
          {{ t('components.buttons.cancel') }}
        </Button>
        <Button type="button" variant="danger" class="w-24" @click="confirmDelete">
          {{ t('components.buttons.delete') }}
        </Button>
      </div>
    </Dialog.Panel>
  </Dialog>
</template>
