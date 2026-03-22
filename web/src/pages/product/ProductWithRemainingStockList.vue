<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import { ViewMode } from '@/types/enums/ViewMode';
import { useSelectedUserLocationStore } from '@/stores/selected-user-location';
import { ErrorCode } from '@/types/enums/ErrorCode';
import DataList from '@/components/DataList';
import Table from '@/components/Base/Table';
import Button from '@/components/Base/Button';
import Lucide from '@/components/Base/Lucide';
import { FormInput, FormInputDateTime, FormLabel, FormSelect, FormSelectSearch } from '@/components/Base/Form';
import { Collection } from '@/types/resources/Collection';
import { Product } from '@/types/models/Product';
import { DataListEmittedData } from '@/components/DataList/DataList.vue';
import ProductService from '@/services/ProductService';
import ProductCategoryService from '@/services/ProductCategoryService';
import BrandService from '@/services/BrandService';
import WarehouseService from '@/services/WarehouseService';
import { ServiceResponse } from '@/types/services/ServiceResponse';
import { ProductReadAnyPaginateRequest } from '@/types/services/product/ProductRequest';
import { type AlertPlaceholderProps } from '@/components/AlertPlaceholder/AlertPlaceholder.vue';
import { formatCurrency, formatDate } from '@/utils/helper';
import { DropDownOption } from '@/types/models/DropDownOption';
import ProductImagePreview from '@/components/Product/ProductImagePreview.vue';

const { t } = useI18n();
const router = useRouter();
const productServices = new ProductService();
const productCategoryService = new ProductCategoryService();
const brandService = new BrandService();
const warehouseService = new WarehouseService();
const selectedUserLocationStore = useSelectedUserLocationStore();

const emits = defineEmits(['mode-state', 'loading-state', 'show-alertplaceholder']);

interface ProductWithRemainingStockFilters {
  search: string;
  endDate: string | null;
  warehouse_id: string | null;
  less_than: number | null;
  greater_than: number | null;
  category_id: string | null;
  brand_id: string | null;
  stock_filter: 'has_stock' | 'empty' | 'valid' | 'invalid' | null;
  sort_by_remaining_stock: 'asc' | 'desc' | null;
}

const filters = ref<ProductWithRemainingStockFilters>({
  search: '',
  endDate: null,
  warehouse_id: null,
  less_than: null,
  greater_than: null,
  category_id: null,
  brand_id: null,
  stock_filter: null,
  sort_by_remaining_stock: null,
});

const productLists = ref<Collection<Array<Product>> | null>({
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

const brandDDL = ref<Array<DropDownOption> | null>(null);
const brandSearch = ref<string>('');
const brandOptions = computed(() =>
  (brandDDL.value ?? []).map((item) => ({
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
const showAdvancedFilters = ref(false);

onMounted(async () => {
  emits('mode-state', ViewMode.LIST);

  if (!isUserLocationSelected.value) {
    router.push({
      name: 'side-menu-error-code',
      params: { code: ErrorCode.USERLOCATION_REQUIRED },
    });
    return;
  }

  filters.value.search = '';
  const now = new Date();
  const endOfMonth = new Date(now.getFullYear(), now.getMonth() + 1, 0, 23, 59, 59);
  filters.value.endDate = formatDate(endOfMonth.toString(), 'YYYY-MM-DD HH:mm:ss');

  await Promise.all([getCategoryDDL(), getBrandDDL(), getWarehouseDDL()]);

  await getProductsWithRemainingStock('', true, 1, 10);
});

const getProductsWithRemainingStock = async (search: string, refresh: boolean, page: number, per_page: number) => {
  emits('loading-state', true);

  const searchReq: ProductReadAnyPaginateRequest = {
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    search: search,
    category_id: filters.value.category_id || undefined,
    brand_id: filters.value.brand_id || undefined,
    type: 1,
    with_remaining_stock: {
      end_date: filters.value.endDate,
      warehouse_id: filters.value.warehouse_id,
      stock_filter: filters.value.stock_filter,
      less_than: normalizeNullableNumber(filters.value.less_than),
      greater_than: normalizeNullableNumber(filters.value.greater_than),
      sort_by_remaining_stock: filters.value.sort_by_remaining_stock,
    },
    refresh: refresh,
    page: page,
    per_page: per_page,
  };

  const result: ServiceResponse<Collection<Array<Product>> | null> =
    await productServices.readAnyPaginate(searchReq);

  if (result.success && result.data) {
    productLists.value = result.data as Collection<Array<Product>>;
    showAlertPlaceholder('hidden', '', null);
  } else {
    showAlertPlaceholder('danger', '', result.errors as Record<string, Array<string>>);
  }

  emits('loading-state', false);
};

const handleDataListChange = async (data: DataListEmittedData) => {
  filters.value.search = data.search.text;

  await getProductsWithRemainingStock(
    filters.value.search,
    false,
    data.pagination.page,
    data.pagination.per_page,
  );
};

const handleEndDateFilterChange = async () => {
  const perPage = productLists.value?.meta.per_page || 10;

  await getProductsWithRemainingStock(filters.value.search, true, 1, perPage);
};

const handleCategoryFilterChange = async () => {
  const perPage = productLists.value?.meta.per_page || 10;

  await getProductsWithRemainingStock(filters.value.search, true, 1, perPage);
};

const handleBrandFilterChange = async () => {
  const perPage = productLists.value?.meta.per_page || 10;

  await getProductsWithRemainingStock(filters.value.search, true, 1, perPage);
};

const handleWarehouseFilterChange = async () => {
  const perPage = productLists.value?.meta.per_page || 10;

  await getProductsWithRemainingStock(filters.value.search, true, 1, perPage);
};

const handleRangeFilterChange = async () => {
  const perPage = productLists.value?.meta.per_page || 10;

  await getProductsWithRemainingStock(filters.value.search, true, 1, perPage);
};

const handleStockFilterChange = async () => {
  const perPage = productLists.value?.meta.per_page || 10;

  await getProductsWithRemainingStock(filters.value.search, true, 1, perPage);
};

const handleSortFilterChange = async () => {
  const perPage = productLists.value?.meta.per_page || 10;

  await getProductsWithRemainingStock(filters.value.search, true, 1, perPage);
};

const clearCategoryFilter = () => {
  filters.value.category_id = null;
  categorySearch.value = '';
};

const clearBrandFilter = () => {
  filters.value.brand_id = null;
  brandSearch.value = '';
};

const clearWarehouseFilter = () => {
  filters.value.warehouse_id = null;
  warehouseSearch.value = '';
};

const getPrimaryUnit = (product: Product) => {
  return product.product_units.find((u) => u.is_primary_unit) ?? null;
};

const getProductMainImageUrl = (product: Product | null | undefined): string | null => {
  const images = product?.product_images ?? [];
  if (images.length === 0) return null;

  const mainImage = images.find((img) => img.is_main);
  if (mainImage?.url) return mainImage.url;

  return images[0]?.url ?? null;
};

const getRemainingStockPrimaryQty = (product: Product): number | null => {
  if (product.remaining_stock_base_unit === undefined || product.remaining_stock_base_unit === null) {
    return null;
  }

  const primaryUnit = getPrimaryUnit(product);
  if (!primaryUnit || !primaryUnit.conversion_value) {
    return null;
  }

  const baseQty = product.remaining_stock_base_unit;
  return baseQty / primaryUnit.conversion_value;
};

const getCategoryDDL = async (search = ''): Promise<void> => {
  const result = await productCategoryService.readAnyGet({
    with_trashed: false,
    search: search,
    company_id: selectedUserLocation.value.company.id,
    type: 1,
    refresh: false,
    limit: 10,
  });

  if (result.success && result.data) {
    categoryDDL.value = result.data.data.map((item: any) => ({
      code: item.id,
      name: item.name,
    }));
  }
};

const getBrandDDL = async (search = ''): Promise<void> => {
  const result = await brandService.readAnyGet({
    with_trashed: false,
    search: search,
    company_id: selectedUserLocation.value.company.id,
    refresh: false,
    limit: 10,
  });

  if (result.success && result.data) {
    brandDDL.value = result.data.data.map((item: any) => ({
      code: item.id,
      name: item.name,
    }));
  }
};

const getWarehouseDDL = async (search = ''): Promise<void> => {
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
    warehouseDDL.value = result.data.data.map((item: any) => ({
      code: item.id,
      name: item.name,
    }));
  }
};

const normalizeNullableNumber = (value: number | null): number | null => {
  if (value === null || Number.isNaN(value)) {
    return null;
  }

  return value;
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

const toggleAdvancedFilters = () => {
  showAdvancedFilters.value = !showAdvancedFilters.value;
};
</script>

<template>
  <div class="grid grid-cols-12 gap-6 mt-5">
    <div class="col-span-12 intro-y lg:col-span-12">
      <div class="grid grid-cols-12 gap-4 gap-y-3 mb-3 relative z-50">
        <div class="col-span-12 lg:col-span-2 md:col-span-6">
          <FormLabel>
            {{ t('views.product.fields.category_id') }}
          </FormLabel>
          <div class="flex items-center gap-2">
            <div class="flex-1 relative z-30">
              <FormSelectSearch v-model="filters.category_id" v-model:search="categorySearch"
                :options="categoryOptions" :placeholder="t('components.dropdown.placeholder')"
                @change="handleCategoryFilterChange" @search="getCategoryDDL" @clear="clearCategoryFilter" />
            </div>
          </div>
        </div>
        <div class="col-span-12 lg:col-span-2 md:col-span-6">
          <FormLabel>
            {{ t('views.product.fields.brand_id') }}
          </FormLabel>
          <div class="flex items-center gap-2">
            <div class="flex-1 relative z-30">
              <FormSelectSearch v-model="filters.brand_id" v-model:search="brandSearch" :options="brandOptions"
                :placeholder="t('components.dropdown.placeholder')" @change="handleBrandFilterChange"
                @search="getBrandDDL" @clear="clearBrandFilter" />
            </div>
          </div>
        </div>
        <div class="col-span-12 lg:col-span-3 md:col-span-6">
          <FormLabel>
            {{ t('views.product.fields.with_remaining_stock_end_date') }}
          </FormLabel>
          <FormInputDateTime v-model="filters.endDate"
            :placeholder="t('views.product.fields.with_remaining_stock_end_date')" @change="handleEndDateFilterChange" />
        </div>
        <div class="col-span-12 lg:col-span-3 md:col-span-6">
          <FormLabel>
            {{ t('views.warehouse.page_title') }}
          </FormLabel>
          <div class="flex items-center gap-2">
            <div class="flex-1 relative z-30">
              <FormSelectSearch v-model="filters.warehouse_id" v-model:search="warehouseSearch"
                :options="warehouseOptions" :placeholder="t('components.dropdown.placeholder')"
                @change="handleWarehouseFilterChange" @search="getWarehouseDDL" @clear="clearWarehouseFilter" />
            </div>
          </div>
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
        <div class="col-span-12 lg:col-span-2 md:col-span-6">
          <FormLabel>
            Filter Stok
          </FormLabel>
          <FormSelect v-model="filters.stock_filter" @change="handleStockFilterChange">
            <option :value="null"></option>
            <option value="has_stock">Punya Stok</option>
            <option value="empty">Stok Kosong</option>
            <option value="valid">Stok Valid</option>
            <option value="invalid">Stok Invalid</option>
          </FormSelect>
        </div>
        <div class="col-span-12 lg:col-span-2 md:col-span-6">
          <FormLabel>
            Stok > Dari
          </FormLabel>
          <FormInput v-model.number="filters.greater_than" type="number" @change="handleRangeFilterChange" />
        </div>
        <div class="col-span-12 lg:col-span-2 md:col-span-6">
          <FormLabel>
            Stok < Dari
          </FormLabel>
          <FormInput v-model.number="filters.less_than" type="number" @change="handleRangeFilterChange" />
        </div>
        <div class="col-span-12 lg:col-span-2 md:col-span-6">
          <FormLabel>
            Urutkan Stok
          </FormLabel>
          <FormSelect v-model="filters.sort_by_remaining_stock" @change="handleSortFilterChange">
            <option :value="null"></option>
            <option value="desc">Tertinggi ke Terendah</option>
            <option value="asc">Terendah ke Tertinggi</option>
          </FormSelect>
        </div>
      </div>

      <DataList :title="t('views.product.table.title') + ' - ' + t('views.product.with_remaining_stock_suffix')"
        :data="productLists" :enable-search="true" :can-print="false" :can-export="false"
        :pagination="productLists ? productLists.meta : null" @dataListChanged="handleDataListChange">
        <template #content>
          <Table class="mt-5" :hover="true">
            <Table.Thead variant="light">
              <Table.Tr>
                <Table.Th class="whitespace-nowrap">
                  {{ t('views.product.table.cols.image') }}
                </Table.Th>
                <Table.Th class="whitespace-nowrap">
                  {{ t('views.product.table.cols.code') }}
                </Table.Th>
                <Table.Th class="whitespace-nowrap">
                  {{ t('views.product.table.cols.category') }}
                </Table.Th>
                <Table.Th class="whitespace-nowrap">
                  {{ t('views.product.table.cols.brand') }}
                </Table.Th>
                <Table.Th class="whitespace-nowrap">
                  {{ t('views.product.table.cols.name') }}
                </Table.Th>
                <Table.Th class="whitespace-nowrap text-right">
                  {{ t('views.stock_adjustment_in_product.table.cols.remaining_stock') }}
                </Table.Th>
                <Table.Th class="whitespace-nowrap">
                  {{ t('views.stock_adjustment_in_product.table.cols.product_unit') }}
                </Table.Th>
              </Table.Tr>
            </Table.Thead>
            <Table.Tbody v-if="productLists !== null">
              <template v-if="productLists.data.length === 0">
                <Table.Tr class="intro-x">
                  <Table.Td colspan="7">
                    <div class="flex justify-center italic">
                      {{ t('components.data-list.data_not_found') }}
                    </div>
                  </Table.Td>
                </Table.Tr>
              </template>
              <template v-for="item in productLists.data" :key="item.ulid">
                <Table.Tr class="intro-x">
                  <Table.Td>
                    <ProductImagePreview :image-url="getProductMainImageUrl(item)" />
                  </Table.Td>
                  <Table.Td>
                    <div class="font-medium whitespace-nowrap">
                      {{ item.code }}
                    </div>
                  </Table.Td>
                  <Table.Td>
                    <div class="font-medium whitespace-nowrap">
                      {{ item.category.name }}
                    </div>
                  </Table.Td>
                  <Table.Td>
                    <div class="font-medium whitespace-nowrap">
                      {{ item.brand?.name ?? '-' }}
                    </div>
                  </Table.Td>
                  <Table.Td>
                    <div class="font-medium">
                      {{ item.name }}
                    </div>
                  </Table.Td>
                  <Table.Td class="text-right">
                    {{ formatCurrency(getRemainingStockPrimaryQty(item) ?? 0) }}
                  </Table.Td>
                  <Table.Td>
                    <span v-if="getPrimaryUnit(item)">
                      {{ getPrimaryUnit(item)?.unit.name }}
                    </span>
                    <span v-else>-</span>
                  </Table.Td>
                </Table.Tr>
              </template>
            </Table.Tbody>
          </Table>
        </template>
      </DataList>
    </div>
  </div>
</template>
