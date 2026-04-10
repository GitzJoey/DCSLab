<script setup lang="ts">
// #region Imports
import { computed, onMounted, ref } from 'vue';
import DataList from '@/components/DataList';
import { useI18n } from 'vue-i18n';
import Button from '@/components/Base/Button';
import Lucide from '@/components/Base/Lucide';
import Table from '@/components/Base/Table';
import ProductService from '@/services/ProductService';
import { Product } from '@/types/models/Product';
import { Collection } from '@/types/resources/Collection';
import { DataListEmittedData } from '@/components/DataList/DataList.vue';
import { ServiceResponse } from '@/types/services/ServiceResponse';
import { ProductReadAnyPaginateRequest } from '@/types/services/product/ProductRequest';
import { useRouter } from 'vue-router';
import { Dialog } from '@/components/Base/Headless';
import { ViewMode } from '@/types/enums/ViewMode';
import { useSelectedUserLocationStore } from '@/stores/selected-user-location';
import { ErrorCode } from '@/types/enums/ErrorCode';
import { NotificationData } from '@/types/models/NotificationData';
import { type AlertPlaceholderProps } from '@/components/AlertPlaceholder/AlertPlaceholder.vue';
import { FormLabel, FormSelectSearch, FormSelect } from '@/components/Base/Form';
import { DropDownOption } from '@/types/models/DropDownOption';
import ProductCategoryService from '@/services/ProductCategoryService';
import VatProfileService from '@/services/VatProfileService';
import DashboardService from '@/services/DashboardService';
import { formatCurrency } from '@/utils/helper';
import ProductImagePreview from '@/components/Product/ProductImagePreview.vue';
// #endregion

// #region Declarations
const { t } = useI18n();
const router = useRouter();
const productServices = new ProductService();
const productCategoryService = new ProductCategoryService();
const vatProfileService = new VatProfileService();
const dashboardServices = new DashboardService();
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

interface ProductServiceListFilters {
  search: string;
  category_id: string | null;
  default_vat_profile_id: string | null;
  is_price_include_vat: string | null;
  status: string | null;
}

// #region Refs
const deleteUlid = ref<string>('');
const deleteModalShow = ref<boolean>(false);
const expandDetail = ref<number | null>(null);
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

const filters = ref<ProductServiceListFilters>({
  search: '',
  category_id: null,
  default_vat_profile_id: null,
  is_price_include_vat: null,
  status: null,
});

const categoryDDL = ref<Array<DropDownOption> | null>(null);
const categorySearch = ref<string>('');
const categoryOptions = computed(() =>
  (categoryDDL.value ?? []).map((item) => ({
    value: item.code,
    label: item.name,
  })),
);

const vatProfileDDL = ref<Array<DropDownOption> | null>(null);
const vatProfileSearch = ref<string>('');
const vatProfileOptions = computed(() =>
  (vatProfileDDL.value ?? []).map((item) => ({
    value: item.code,
    label: item.name,
  })),
);

const statusDDL = ref<Array<DropDownOption> | null>(null);
// #endregion

// #region Computed
const isUserLocationSelected = computed(() => selectedUserLocationStore.isUserLocationSelected);
const selectedUserLocation = computed(() => selectedUserLocationStore.selectedUserLocation);

const booleanOptions = computed<Array<DropDownOption>>(() => [
  { code: 'true', name: t('components.dropdown.values.switch.on') },
  { code: 'false', name: t('components.dropdown.values.switch.off') },
]);
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

  filters.value.search = '';

  await Promise.all([loadCategoryDDL(), loadVatProfileDDL(), loadStatusDDL()]);

  await getProducts('', true, 1, 10);
});
// #endregion

// #region Methods
const getProducts = async (search: string, refresh: boolean, page: number, per_page: number) => {
  emits('loading-state', true);

  let company_id = selectedUserLocation.value.company.id;

  const searchReq: ProductReadAnyPaginateRequest = {
    with_trashed: false,

    company_id: company_id,
    search: search,
    category_id: filters.value.category_id || undefined,
    default_vat_profile_id: filters.value.default_vat_profile_id || undefined,
    is_price_include_vat:
      filters.value.is_price_include_vat === 'true'
        ? true
        : filters.value.is_price_include_vat === 'false'
          ? false
          : undefined,
    type: 4,
    status: filters.value.status || undefined,
    include_id: undefined,

    refresh: refresh,
    page: page,
    per_page: per_page,
  };

  let result: ServiceResponse<Collection<Array<Product>> | null> = await productServices.readAnyPaginate(searchReq);

  if (result.success && result.data) {
    productLists.value = result.data;
    showAlertPlaceholder('hidden', '', null);
  } else {
    showAlertPlaceholder('danger', '', result.errors as Record<string, Array<string>>);
  }

  emits('loading-state', false);
};

const handleDataListChange = async (data: DataListEmittedData) => {
  filters.value.search = data.search.text;
  await getProducts(data.search.text, false, data.pagination.page, data.pagination.per_page);
};

const handleCategoryFilterChange = async () => {
  const perPage = productLists.value?.meta.per_page || 10;

  await getProducts(filters.value.search, true, 1, perPage);
};

const handleIsPriceIncludeVatFilterChange = async () => {
  const perPage = productLists.value?.meta.per_page || 10;

  await getProducts(filters.value.search, true, 1, perPage);
};

const handleDefaultVatProfileFilterChange = async () => {
  const perPage = productLists.value?.meta.per_page || 10;

  await getProducts(filters.value.search, true, 1, perPage);
};

const handleStatusFilterChange = async () => {
  const perPage = productLists.value?.meta.per_page || 10;

  await getProducts(filters.value.search, true, 1, perPage);
};

const clearCategoryFilter = () => {
  filters.value.category_id = null;
  categorySearch.value = '';
};

const clearDefaultVatProfileFilter = () => {
  filters.value.default_vat_profile_id = null;
  vatProfileSearch.value = '';
};

const loadCategoryDDL = async (search = '') => {
  if (!selectedUserLocation.value) return;

  const result = await productCategoryService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    search,
    type: 2,
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

const loadVatProfileDDL = async (search = '') => {
  if (!selectedUserLocation.value) return;

  const result = await vatProfileService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    search,
    include_id: filters.value.default_vat_profile_id || undefined,
    refresh: false,
    limit: 20,
  });

  if (result.success && result.data) {
    vatProfileDDL.value = result.data.data.map((item: any) => ({
      code: item.id,
      name: item.name,
    }));
  }
};

const loadStatusDDL = async () => {
  const result = await dashboardServices.getStatusDDL(false);
  if (result) {
    statusDDL.value = result;
  }
};

const viewSelected = (idx: number) => {
  if (expandDetail.value === idx) {
    expandDetail.value = null;
  } else {
    expandDetail.value = idx;
  }
};

const editSelected = (idx: number) => {
  if (!productLists.value) return;
  let ulid = productLists.value.data[idx].ulid;
  emits('mode-state', ViewMode.FORM_EDIT);
  router.push({
    name: 'side-menu-product-product-service-edit',
    params: { ulid: ulid },
  });
};

const deleteSelected = (idx: number) => {
  if (!productLists.value) return;
  let ulid = productLists.value.data[idx].ulid;
  deleteUlid.value = ulid;
  deleteModalShow.value = true;
};

const confirmDelete = async () => {
  deleteModalShow.value = false;
  emits('loading-state', true);

  let result = await productServices.delete(deleteUlid.value);

  emits('loading-state', false);

  if (result.success) {
    await getProducts(filters.value.search, true, 1, productLists.value?.meta.per_page || 10);
    showNotification(t('views.product_service.alert.delete.title'), t('views.product_service.alert.delete.message'));
  } else {
    showAlertPlaceholder('danger', '', result.errors as Record<string, Array<string>>);
  }
};

const showNotification = (pTitle: string, pContent: string) => {
  let n: NotificationData = {
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
  let ap: AlertPlaceholderProps = {
    alertType: pAlertType,
    title: pTitle,
    alertList: pAlertList,
  };

  emits('show-alertplaceholder', ap);
};
const getProductMainImageUrl = (item: Product): string | null => {
  if (!item.product_images || item.product_images.length === 0) return null;

  const mainImage = item.product_images.find((img) => img.is_main);
  if (mainImage?.url) return mainImage.url;

  const first = item.product_images[0];
  return first?.url ?? null;
};
// #endregion
</script>

<template>
  <div class="grid grid-cols-12 gap-6 mt-5">
    <div class="col-span-12 intro-y lg:col-span-12">
      <div class="grid grid-cols-12 gap-4 gap-y-3 mb-3">
        <div class="col-span-12 md:col-span-6 lg:col-span-3">
          <FormLabel>
            {{ t('views.product_service.fields.category_id') }}
          </FormLabel>
          <div class="flex items-center gap-2">
            <div class="flex-1">
              <FormSelectSearch v-model="filters.category_id" v-model:search="categorySearch"
                :options="categoryOptions" :placeholder="t('components.dropdown.placeholder')"
                @change="handleCategoryFilterChange" @search="loadCategoryDDL" @clear="clearCategoryFilter" />
            </div>
          </div>
        </div>
        <div class="col-span-12 md:col-span-6 lg:col-span-3">
          <FormLabel>
            {{ t('views.product_service.fields.default_vat_profile_id') }}
          </FormLabel>
          <div class="flex items-center gap-2">
            <div class="flex-1">
              <FormSelectSearch v-model="filters.default_vat_profile_id" v-model:search="vatProfileSearch"
                :options="vatProfileOptions" :placeholder="t('components.dropdown.placeholder')"
                @change="handleDefaultVatProfileFilterChange" @search="loadVatProfileDDL"
                @clear="clearDefaultVatProfileFilter" />
            </div>
          </div>
        </div>
        <div class="col-span-12 md:col-span-6 lg:col-span-2">
          <FormLabel>
            {{ t('views.product_service.fields.status') }}
          </FormLabel>
          <FormSelect v-model="filters.status" @change="handleStatusFilterChange">
            <option value="">{{ t('components.dropdown.placeholder') }}</option>
            <option v-for="item in statusDDL ?? []" :key="item.code" :value="item.code">{{ t(item.name) }}</option>
          </FormSelect>
        </div>
        <div class="col-span-12 md:col-span-6 lg:col-span-2">
          <FormLabel>
            {{ t('views.product_service.fields.is_price_include_vat') }}
          </FormLabel>
          <FormSelect v-model="filters.is_price_include_vat" @change="handleIsPriceIncludeVatFilterChange">
            <option value="">{{ t('components.dropdown.placeholder') }}</option>
            <option v-for="item in booleanOptions" :key="item.code" :value="item.code">{{ item.name }}</option>
          </FormSelect>
        </div>
      </div>

      <DataList :title="t('views.product_service.table.title')" :data="productLists" :enable-search="true"
        :can-print="true" :can-export="true" :pagination="productLists ? productLists.meta : null"
        @dataListChanged="handleDataListChange">
        <template #content>
          <Table class="mt-5" :hover="true">
            <Table.Thead variant="light">
              <Table.Tr>
                <Table.Th class="whitespace-nowrap">
                  {{ t('views.product.table.cols.image') }}
                </Table.Th>
                <Table.Th class="whitespace-nowrap">
                  {{ t('views.product.detail.info_title') }}
                </Table.Th>
                <Table.Th class="whitespace-nowrap">
                  {{ t('views.product.table.cols.name') }}
                </Table.Th>
                <Table.Th class="whitespace-nowrap text-right">
                  {{ t('views.product.table.cols.unit') }}
                </Table.Th>
                <Table.Th class="whitespace-nowrap">
                  {{ t('views.product.table.cols.price') }}
                </Table.Th>
                <Table.Th class="whitespace-nowrap"></Table.Th>
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
              <template v-for="(item, itemIdx) in productLists.data" :key="item.ulid">
                <Table.Tr class="intro-x">
                  <Table.Td>
                    <ProductImagePreview :image-url="getProductMainImageUrl(item)" />
                  </Table.Td>
                  <Table.Td>
                    <div class="font-medium whitespace-nowrap">
                      {{ item.code }}
                    </div>
                    <div class="mt-0.5 flex items-center flex-wrap">
                      <span class="font-medium">{{ item.category.name }}</span>
                    </div>
                    <div class="mt-1">
                      <div v-if="item.status == 'ACTIVE'" class="flex items-center text-success text-xs">
                        <Lucide icon="CheckCircle" class="w-3 h-3 mr-1" />
                        {{ t('views.product_service.status.active') }}
                      </div>
                      <div v-else class="flex items-center text-danger text-xs">
                        <Lucide icon="X" class="w-3 h-3 mr-1" />
                        {{ t('views.product_service.status.inactive') }}
                      </div>
                    </div>
                  </Table.Td>
                  <Table.Td>
                    <div class="font-medium">
                      {{ item.name }}
                    </div>
                  </Table.Td>
                  <Table.Td>
                    <div class="flex flex-col gap-0">
                      <div v-for="unit in item.product_units" :key="unit.ulid"
                        class="h-6 flex items-center whitespace-nowrap">
                        {{
                          unit.unit.name
                        }}{{
                          unit.conversion_value > 1
                            ? ': ' + formatCurrency(unit.conversion_value) + ' ' + item.product_units[0].unit.name
                            : ''
                        }}
                      </div>
                    </div>
                  </Table.Td>
                  <Table.Td>
                    <div class="flex flex-col gap-0 items-end">
                      <div v-for="unit in item.product_units" :key="unit.ulid"
                        class="h-6 flex items-center whitespace-nowrap">
                        {{ formatCurrency(unit.price) }}
                      </div>
                    </div>
                  </Table.Td>
                  <Table.Td>
                    <div class="flex justify-end gap-1">
                      <Button variant="outline-secondary" @click="viewSelected(itemIdx)">
                        <Lucide icon="Info" class="w-4 h-4" />
                      </Button>
                      <Button variant="outline-secondary" @click="editSelected(itemIdx)">
                        <Lucide icon="Pen" class="w-4 h-4" />
                      </Button>
                      <Button variant="outline-secondary" @click="deleteSelected(itemIdx)">
                        <Lucide icon="Trash2" class="w-4 h-4 text-danger" />
                      </Button>
                    </div>
                  </Table.Td>
                </Table.Tr>
                <Table.Tr :class="{
                  'intro-x': true,
                  'hidden transition-all': expandDetail !== itemIdx,
                }">
                  <Table.Td colspan="5" class="p-5">
                    <div class="grid grid-cols-12 gap-6">
                      <div class="col-span-12 lg:col-span-6">
                        <div class="font-medium text-base mb-3 border-b pb-2">
                          {{ t('views.product.detail.info_title') }}
                        </div>
                        <div class="grid grid-cols-1 gap-y-2">
                          <div class="flex flex-row">
                            <div class="w-48 text-slate-500">
                              {{ t('views.product_service.fields.code') }}
                            </div>
                            <div class="flex-1 font-medium">
                              {{ item.code }}
                            </div>
                          </div>
                          <div class="flex flex-row">
                            <div class="w-48 text-slate-500">
                              {{ t('views.product_service.fields.category_id') }}
                            </div>
                            <div class="flex-1 font-medium">
                              {{ item.category.name }}
                            </div>
                          </div>
                          <div class="flex flex-row">
                            <div class="w-48 text-slate-500">
                              {{ t('views.product_service.fields.name') }}
                            </div>
                            <div class="flex-1 font-medium">
                              {{ item.name }}
                            </div>
                          </div>
                          <div class="flex flex-row">
                            <div class="w-48 text-slate-500">
                              {{ t('views.product_service.fields.status') }}
                            </div>
                            <div class="flex-1 font-medium">
                              {{
                                item.status == 'ACTIVE'
                                  ? t('views.product_service.status.active')
                                  : t('views.product_service.status.inactive')
                              }}
                            </div>
                          </div>
                          <div class="flex flex-row">
                            <div class="w-48 text-slate-500">
                              {{ t('views.product_service.fields.remarks') }}
                            </div>
                            <div class="flex-1 font-medium">
                              {{ item.remarks || '-' }}
                            </div>
                          </div>
                        </div>
                      </div>

                      <div class="col-span-12 lg:col-span-6">
                        <div class="font-medium text-base mb-3 border-b pb-2">
                          {{ t('views.product.detail.settings_title') }}
                        </div>
                        <div class="grid grid-cols-1 gap-y-2">
                          <div class="flex flex-row">
                            <div class="w-48 text-slate-500">
                              {{ t('views.product_service.fields.default_vat_profile_id') }}
                            </div>
                            <div class="flex-1 font-medium">
                              {{ item.default_vat_profile?.name || '-' }}
                            </div>
                          </div>
                          <div class="flex flex-row">
                            <div class="w-48 text-slate-500">
                              {{ t('views.product_service.fields.is_price_include_vat') }}
                            </div>
                            <div class="flex-1 font-medium">
                              {{
                                item.is_price_include_vat
                                  ? t('components.dropdown.values.switch.on')
                                  : t('components.dropdown.values.switch.off')
                              }}
                            </div>
                          </div>
                          <div class="flex flex-row">
                            <div class="w-48 text-slate-500">
                              {{ t('views.product_service.fields.unit_id') }}
                            </div>
                            <div class="flex-1 font-medium">
                              {{
                                item.product_units && item.product_units.length > 0
                                  ? item.product_units[0].unit.name
                                  : '-'
                              }}
                            </div>
                          </div>
                          <div class="flex flex-row">
                            <div class="w-48 text-slate-500">
                              {{ t('views.product_service.fields.price') }}
                            </div>
                            <div class="flex-1 font-medium">
                              {{
                                item.product_units && item.product_units.length > 0
                                  ? formatCurrency(item.product_units[0].price)
                                  : '-'
                              }}
                            </div>
                          </div>
                          <div class="flex flex-row">
                            <div class="w-48 text-slate-500">
                              {{ t('views.product_service.fields.point') }}
                            </div>
                            <div class="flex-1 font-medium">
                              {{
                                item.product_units && item.product_units.length > 0
                                  ? item.product_units[0].point
                                  : '-'
                              }}
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </Table.Td>
                </Table.Tr>
              </template>
            </Table.Tbody>
          </Table>
        </template>
      </DataList>
    </div>
  </div>
  <Dialog :open="deleteModalShow" @close="
    () => {
      deleteModalShow = false;
    }
  ">
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
        <Button type="button" variant="outline-secondary" @click="
          () => {
            deleteModalShow = false;
          }
        " class="w-24 mr-1">
          {{ t('components.buttons.cancel') }}
        </Button>
        <Button type="button" variant="danger" class="w-24" @click="confirmDelete">
          {{ t('components.buttons.delete') }}
        </Button>
      </div>
    </Dialog.Panel>
  </Dialog>
</template>
