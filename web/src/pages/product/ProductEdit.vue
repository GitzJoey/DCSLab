<script setup lang="ts">
// #region Imports
import { computed, onMounted, ref, watch, watchEffect } from 'vue';
import { useI18n } from 'vue-i18n';
import ProductService from '@/services/ProductService';
import ProductCategoryService from '@/services/ProductCategoryService';
import BrandService from '@/services/BrandService';
import UnitService from '@/services/UnitService';
import DashboardService from '@/services/DashboardService';
import CacheService from '@/services/CacheService';
import { TwoColumnsLayout } from '@/components/Base/Form/FormLayout';
import {
  FormCheck,
  FormInput,
  FormLabel,
  FormSelect,
  FormInputCode,
  FormInputCurrency,
  FormErrorMessages,
  FormSwitch,
  FormTextarea,
  FormSelectSearch,
} from '@/components/Base/Form';
import { ProductImage } from '@/types/models/ProductImage';
import ProductImagesField from '@/components/Product/ProductImagesField.vue';
import { TwoColumnsLayoutCards } from '@/components/Base/Form/FormLayout/TwoColumnsLayout.vue';
import { CardState } from '@/types/enums/CardState';
import Button from '@/components/Base/Button';
import { ViewMode } from '@/types/enums/ViewMode';
import { debounce } from 'lodash';
import Lucide from '@/components/Base/Lucide';
import { useSelectedUserLocationStore } from '@/stores/selected-user-location';
import { useRoute, useRouter } from 'vue-router';
import { type AlertPlaceholderProps } from '@/components/AlertPlaceholder/AlertPlaceholder.vue';
import { ErrorCode } from '@/types/enums/ErrorCode';
import { DropDownOption } from '@/types/models/DropDownOption';
import { formatCurrency, convertErrorTypeToAlertListType } from '@/utils/helper';
// #endregion

// #region Declarations
const { t } = useI18n();
const router = useRouter();
const route = useRoute();
const selectedUserLocationStore = useSelectedUserLocationStore();

const productService = new ProductService();
const productCategoryService = new ProductCategoryService();
const brandService = new BrandService();
const unitService = new UnitService();
const dashboardServices = new DashboardService();
const cacheServices = new CacheService();
// #endregion

// #region Props, Emits
const emits = defineEmits(['mode-state', 'loading-state', 'update-profile', 'show-alertplaceholder']);
// #endregion

// #region Refs
const cards = ref<Array<TwoColumnsLayoutCards>>([
  {
    title: 'views.product.field_groups.company_info',
    state: CardState.Expanded,
  },
  {
    title: 'views.product.field_groups.product_data',
    state: CardState.Expanded,
  },
  {
    title: 'views.product.field_groups.unit_settings',
    state: CardState.Expanded,
  },
  { title: '', state: CardState.Hidden, id: 'button' },
]);

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

const unitDDL = ref<Record<number, Array<DropDownOption>>>({});
const unitSearch = ref<string[]>([]);
const getUnitOptions = (index: number) =>
  (unitDDL.value[index] ?? []).map((item) => ({
    value: item.code,
    label: item.name,
  }));

const statusDDL = ref<Array<DropDownOption> | null>(null);

const productForm = productService.useProductPhysicalUpdateForm(route.params.ulid.toString());

const uploadedImages = ref<ProductImage[]>([]);

// #endregion

// #region Computed
const isUserLocationSelected = computed(() => selectedUserLocationStore.isUserLocationSelected);
const selectedUserLocation = computed(() => selectedUserLocationStore.selectedUserLocation);
// #endregion

// #region Lifecycle Hooks
onMounted(async () => {
  emits('mode-state', ViewMode.FORM_EDIT);
  if (!isUserLocationSelected.value) {
    router.push({
      name: 'side-menu-error-code',
      params: { code: ErrorCode.USERLOCATION_REQUIRED },
    });
  }

  await Promise.all([getCategoryDDL(), getBrandDDL(), getStatusDDL()]);
  await loadData();

  await Promise.all(
    productForm.product_units.map((_, index) => getUnitDDL(index, '')),
  );
});
// #endregion

// #region Methods
const loadData = async () => {
  emits('loading-state', true);
  const result = await productService.read(route.params.ulid.toString());
  emits('loading-state', false);

  if (result.success && result.data) {
    productForm.setData({
      company_id: result.data.company?.id ?? '',
      code: result.data.code,
      category_id: result.data.category?.id ?? '',
      brand_id: result.data.brand?.id ?? '',
      name: result.data.name,
      is_taxable: result.data.is_taxable,
      vat_rate: result.data.vat_rate,
      is_price_include_vat: result.data.is_price_include_vat,
      is_use_serial_number: result.data.is_use_serial_number,
      is_expirable: result.data.is_expirable,
      remarks: result.data.remarks,
      status: result.data.status,
      product_units: result.data.product_units.map((u: any) => ({
        id: u.id,
        code: u.code,
        is_manufacturer_sku: false,
        unit_id: u.unit?.id ?? '',
        unit_name: u.unit?.name ?? '',
        price: u.price,
        is_base: u.is_base,
        conversion_value: u.conversion_value,
        is_primary_unit: u.is_primary_unit,
        point: u.point,
        remarks: u.remarks,
      })),
    } as any);

    if (result.data.product_images) {
      const images = result.data.product_images.map((img: any) => ({
        id: img.id,
        product_id: img.product_id,
        path: img.path,
        url: img.url,
        hash: img.hash,
        is_main: img.is_main,
      }));

      uploadedImages.value = images;
      productForm.image_hashes = images.map((img: any) => ({
        hash: img.hash,
        is_main: img.is_main,
      }));
    }

    // Ensure at least one unit exists for the form to render correctly
    if (productForm.product_units.length === 0) {
      productForm.product_units.push({
        code: '_AUTO_',
        is_manufacturer_sku: false,
        unit_id: '',
        unit_name: '',
        price: 0,
        is_base: true,
        conversion_value: 1,
        is_primary_unit: true,
        point: 0,
        remarks: '',
      });
    }
  } else {
    router.push({ name: 'side-menu-product-product-list' });
  }
};

const getCategoryDDL = async (search = ''): Promise<void> => {
  const result = await productCategoryService.readAnyGet({
    with_trashed: false,
    search: search,
    company_id: selectedUserLocation.value.company.id,
    type: 1, // Product Type
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

const getUnitDDL = async (index: number, search = ''): Promise<void> => {
  const result = await unitService.readAnyGet({
    with_trashed: false,
    search: search,
    company_id: selectedUserLocation.value.company.id,
    refresh: false,
    limit: 10,
  });

  if (result.success && result.data) {
    const options: Array<DropDownOption> = result.data.data.map((item: any) => ({
      code: item.id,
      name: item.name,
    }));

    const currentUnit = productForm.product_units[index];
    if (currentUnit?.unit_id && !options.some((opt) => opt.code === currentUnit.unit_id)) {
      options.push({
        code: currentUnit.unit_id,
        name: currentUnit.unit_name || '',
      });
    }

    unitDDL.value[index] = options;

    if (currentUnit?.unit_id && !currentUnit.unit_name) {
      const match = options.find((opt) => opt.code === currentUnit.unit_id);
      if (match) currentUnit.unit_name = match.name;
    }
  } else {
    unitDDL.value[index] = [];
  }
};

const getStatusDDL = async (): Promise<void> => {
  const result = await dashboardServices.getStatusDDL(false);
  if (result) {
    statusDDL.value = result;
  }
};

const handleExpandCard = (index: number) => {
  if (cards.value[index].state === CardState.Collapsed) {
    cards.value[index].state = CardState.Expanded;
  } else if (cards.value[index].state === CardState.Expanded) {
    cards.value[index].state = CardState.Collapsed;
  }
};

const scrollToError = (id: string): void => {
  let el = document.getElementById(id);
  if (!el) return;
  el.scrollIntoView({ behavior: 'smooth', block: 'center' });
};

const onSubmit = async () => {
  if (productForm.hasErrors) {
    scrollToError(Object.keys(productForm.errors)[0]);
  }
  emits('loading-state', true);
  await productForm
    .submit()
    .then(() => {
      showAlertPlaceholder('hidden', '', null);
      emits('update-profile');
      router.push({ name: 'side-menu-product-product-list' });
    })
    .catch((error) => {
      const errorList: Record<string, Array<string>> = convertErrorTypeToAlertListType(error);
      showAlertPlaceholder('danger', '', errorList);
    })
    .finally(() => {
      emits('loading-state', false);
    });
};

const resetForm = async () => {
  productForm.reset();
  productForm.setErrors({});
  await loadData();
};

const setCode = () => {
  productForm.forgetError('code');
  if (productForm.code == '_AUTO_') {
    productForm.setData({ code: '' });
  } else {
    productForm.setData({ code: '_AUTO_' });
  }
};

const setUnitCode = (index: number) => {
  if (productForm.product_units[index].code == '_AUTO_') {
    productForm.product_units[index].code = '';
  } else {
    productForm.product_units[index].code = '_AUTO_';
  }
};

const setPrimaryUnit = (index: number) => {
  productForm.product_units.forEach((u: any, i: number) => {
    u.is_primary_unit = i === index;
  });
  productForm.validate('product_units.is_primary_unit' as any);
};

const addUnit = () => {
  productForm.product_units.push({
    code: '_AUTO_',
    is_manufacturer_sku: false,
    unit_id: '',
    unit_name: '',
    price: 0,
    is_base: false,
    conversion_value: '',
    is_primary_unit: false,
    point: 0,
    remarks: '',
  } as any);

  Object.keys(productForm.errors).forEach((key) => {
    if (key.startsWith('product_units.')) {
      productForm.forgetError(key as any);
    }
  });
};

const updateUnitName = (index: number, newUnitId?: string) => {
  const unitId = newUnitId ?? productForm.product_units[index].unit_id;
  if (!unitId) {
    productForm.product_units[index].unit_name = '';
    return;
  }

  const options = unitDDL.value[index] ?? [];
  const unit = options.find((u) => u.code === unitId);

  if (unit) {
    productForm.product_units[index].unit_name = unit.name;
    productForm.forgetError(`product_units.${index}.unit_id` as any);
  }
};

const clearUnit = (index: number) => {
  productForm.product_units[index].unit_id = '';
  productForm.product_units[index].unit_name = '';
};

const removeUnit = (index: number) => {
  const unit = productForm.product_units[index] as any;
  if (unit.id) {
    if (!productForm.delete_product_unit_ids) {
      productForm.delete_product_unit_ids = [];
    }
    productForm.delete_product_unit_ids.push(unit.id);
  }

  const isPrimary = productForm.product_units[index].is_primary_unit;
  productForm.product_units.splice(index, 1);

  // If the removed unit was the primary unit, set the first unit (Base Unit) as primary
  if (isPrimary && productForm.product_units.length > 0) {
    productForm.product_units[0].is_primary_unit = true;
  }

  // Clear errors related to product_units to prevent stale "duplicate" errors
  Object.keys(productForm.errors).forEach((key) => {
    if (key.startsWith('product_units.')) {
      productForm.forgetError(key as any);
    }
  });
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

// #endregion

// #region Watchers
watch(
  productForm,
  debounce((newValue): void => {
    cacheServices.setLastEntity('PRODUCT_EDIT', newValue.data());
  }, 500),
  { deep: true },
);
// #endregion
</script>

<template>
  <form id="productForm" @submit.prevent="onSubmit">
    <TwoColumnsLayout :cards="cards" :using-side-tab="false" @handle-expand-card="handleExpandCard">
      <!-- Card 1: Company Info -->
      <template #card-items-0>
        <div class="p-5">
          <FormLabel>
            {{ selectedUserLocation.company.code }}
            <br />
            {{ selectedUserLocation.company.name }}
          </FormLabel>
          <FormInput type="hidden" v-model="productForm.company_id" />
        </div>
      </template>

      <!-- Card 2: Product Data -->
      <template #card-items-1>
        <div class="p-5">
          <div class="grid grid-cols-12 gap-4 gap-y-3">
            <div class="col-span-12 lg:col-span-2">
              <FormLabel :class="{ 'text-danger': productForm.invalid('code') }">
                {{ t('views.product.fields.code') }}
              </FormLabel>
              <FormInputCode v-model="productForm.code" :class="{ 'border-danger': productForm.invalid('code') }"
                :placeholder="t('views.product.fields.code')" @set-auto="setCode"
                @change="productForm.validate('code')" />
              <FormErrorMessages :messages="productForm.errors.code" />
            </div>

            <div class="col-span-12 lg:col-span-2">
              <FormLabel :class="{ 'text-danger': productForm.invalid('category_id') }">
                {{ t('views.product.fields.category_id') }}
              </FormLabel>
              <FormSelectSearch v-model="productForm.category_id" v-model:search="categorySearch"
                :options="categoryOptions" :placeholder="t('components.dropdown.placeholder')"
                :class="{ 'border-danger': productForm.invalid('category_id') }"
                @change="productForm.validate('category_id')" @search="getCategoryDDL" />
              <FormErrorMessages :messages="productForm.errors.category_id" />
            </div>

            <div class="col-span-12 lg:col-span-2">
              <FormLabel :class="{ 'text-danger': productForm.invalid('brand_id') }">
                {{ t('views.product.fields.brand_id') }}
              </FormLabel>
              <FormSelectSearch v-model="productForm.brand_id" v-model:search="brandSearch" :options="brandOptions"
                :placeholder="t('components.dropdown.placeholder')"
                :class="{ 'border-danger': productForm.invalid('brand_id') }" @change="productForm.validate('brand_id')"
                @search="getBrandDDL" />
              <FormErrorMessages :messages="productForm.errors.brand_id" />
            </div>

            <div class="col-span-12 lg:col-span-6">
              <FormLabel :class="{ 'text-danger': productForm.invalid('name') }">
                {{ t('views.product.fields.name') }}
              </FormLabel>
              <FormInput v-model="productForm.name" type="text"
                :class="{ 'border-danger': productForm.invalid('name') }" :placeholder="t('views.product.fields.name')"
                @change="productForm.validate('name')" />
              <FormErrorMessages :messages="productForm.errors.name" />
            </div>

            <div class="col-span-12 lg:col-span-2">
              <FormLabel :class="{ 'text-danger': productForm.invalid('is_taxable') }">
                {{ t('views.product.fields.is_taxable') }}
              </FormLabel>
              <FormSwitch class="mt-2">
                <FormSwitch.Input v-model="productForm.is_taxable" type="checkbox" :class="{
                  'border-danger': productForm.invalid('is_taxable'),
                }" @change="productForm.validate('is_taxable')" />
              </FormSwitch>
              <FormErrorMessages :messages="productForm.errors.is_taxable" />
            </div>

            <div class="col-span-12 sm:col-span-2" v-if="productForm.is_taxable">
              <FormLabel :class="{ 'text-danger': productForm.invalid('vat_rate') }">
                {{ t('views.product.fields.vat_rate') }}
              </FormLabel>
              <FormInputCurrency v-model="productForm.vat_rate"
                :class="{ 'border-danger': productForm.invalid('vat_rate') }"
                :placeholder="t('views.product.fields.vat_rate')" @change="productForm.validate('vat_rate')" />
              <FormErrorMessages :messages="productForm.errors.vat_rate" />
            </div>

            <div class="col-span-12 sm:col-span-2" v-if="productForm.is_taxable">
              <FormLabel :class="{
                'text-danger': productForm.invalid('is_price_include_vat'),
              }">
                {{ t('views.product.fields.is_price_include_vat') }}
              </FormLabel>
              <FormSwitch class="mt-2">
                <FormSwitch.Input v-model="productForm.is_price_include_vat" type="checkbox" :class="{
                  'border-danger': productForm.invalid('is_price_include_vat'),
                }" @change="productForm.validate('is_price_include_vat')" />
              </FormSwitch>
              <FormErrorMessages :messages="productForm.errors.is_price_include_vat" />
            </div>

            <div class="col-span-12 sm:col-span-2">
              <FormLabel :class="{
                'text-danger': productForm.invalid('is_use_serial_number'),
              }">
                {{ t('views.product.fields.is_use_serial_number') }}
              </FormLabel>
              <FormSwitch class="mt-2">
                <FormSwitch.Input v-model="productForm.is_use_serial_number" type="checkbox" :class="{
                  'border-danger': productForm.invalid('is_use_serial_number'),
                }" @change="productForm.validate('is_use_serial_number')" />
              </FormSwitch>
              <FormErrorMessages :messages="productForm.errors.is_use_serial_number" />
            </div>

            <div class="col-span-12 sm:col-span-2">
              <FormLabel :class="{ 'text-danger': productForm.invalid('is_expirable') }">
                {{ t('views.product.fields.is_expirable') }}
              </FormLabel>
              <FormSwitch class="mt-2">
                <FormSwitch.Input v-model="productForm.is_expirable" type="checkbox" :class="{
                  'border-danger': productForm.invalid('is_expirable'),
                }" @change="productForm.validate('is_expirable')" />
              </FormSwitch>
              <FormErrorMessages :messages="productForm.errors.is_expirable" />
            </div>

            <div class="col-span-12">
              <FormLabel :class="{ 'text-danger': productForm.invalid('remarks') }">
                {{ t('views.product.fields.remarks') }}
              </FormLabel>
              <FormTextarea v-model="productForm.remarks" :class="{ 'border-danger': productForm.invalid('remarks') }"
                :placeholder="t('views.product.fields.remarks')" @change="productForm.validate('remarks')" />
              <FormErrorMessages :messages="productForm.errors.remarks" />
            </div>

            <div class="col-span-12 sm:col-span-2">
              <FormLabel :class="{ 'text-danger': productForm.invalid('status') }">
                {{ t('views.product.fields.status') }}
              </FormLabel>
              <FormSelect v-model="productForm.status" :class="{ 'border-danger': productForm.invalid('status') }"
                @change="productForm.validate('status')">
                <option value="">
                  {{ t('components.dropdown.placeholder') }}
                </option>
                <option v-for="s in statusDDL" :key="s.code" :value="s.code">
                  {{ t(s.name) }}
                </option>
              </FormSelect>
              <FormErrorMessages :messages="productForm.errors.status" />
            </div>

            <!-- Column 12: Images -->
            <div class="col-span-12">
              <FormLabel>
                {{ t('views.product.fields.images') }}
              </FormLabel>
              <ProductImagesField
                v-model="productForm.image_hashes"
                v-model:existing-images="uploadedImages"
                v-model:delete-image-ids="productForm.delete_image_ids"
              />
            </div>
          </div>
        </div>
      </template>

      <!-- Card 3: Product Unit Settings -->
      <template #card-items-2>
        <div class="p-5">
          <!-- No Product Unit Found -->
          <div v-if="productForm.product_units.length === 0" class="text-slate-500 text-sm">
            {{ t('components.data-list.data_not_found') }}
          </div>

          <!-- Product Unit List -->
          <div v-else class="space-y-5">
            <div v-for="(unit, index) in productForm.product_units" :key="index"
              class="border border-slate-200/60 dark:border-darkmode-400 rounded-md p-4">
              <!-- title -->
              <div class="flex items-center justify-between mb-3">
                <div class="font-medium text-sm">
                  {{
                    index === 0
                      ? t('views.product.fields.base_unit')
                      : t('views.product.fields.other_unit') + ' #' + index
                  }}
                </div>
                <Button v-if="index > 0" type="button" variant="outline-secondary" @click="removeUnit(index)">
                  <Lucide icon="Trash2" class="w-4 h-4 text-danger" />
                </Button>
              </div>

              <!-- Columns Unit Code, Unit Name, Conversion Value, Price, Point, Is Primary Unit -->
              <div class="grid grid-cols-12 gap-4 gap-y-3">
                <!-- Column 1: Unit Code + Is Manufacturer SKU -->
                <div class="col-span-12 lg:col-span-4">
                  <div class="grid grid-cols-12 items-center gap-4">
                    <div class="col-span-7">
                      <FormLabel :class="{
                        'text-danger': productForm.invalid(`product_units.${index}.code` as any),
                      }">
                        {{ t('views.product.fields.unit_code') }}
                      </FormLabel>
                    </div>
                    <div class="col-span-5">
                      <FormLabel :class="{
                        'text-danger': productForm.invalid(`product_units.${index}.is_manufacturer_sku` as any),
                      }">
                        {{ t('views.product.fields.is_manufacturer_sku') }}
                      </FormLabel>
                    </div>
                  </div>
                  <div class="grid grid-cols-12 items-center gap-4 mt-1">
                    <!-- Unit Code Input -->
                    <div class="col-span-7">
                      <FormInputCode v-model="productForm.product_units[index].code" :class="{
                        'border-danger': productForm.invalid(`product_units.${index}.code` as any),
                      }" :placeholder="t('views.product.fields.unit_code')" @set-auto="setUnitCode(index)"
                        @change="productForm.validate(`product_units.${index}.code` as any)" />
                      <FormErrorMessages :messages="(productForm.errors as any)[`product_units.${index}.code`]" />
                    </div>
                    <!-- Is Manufacturer SKU Switch -->
                    <div class="col-span-5">
                      <div class="flex items-center">
                        <FormSwitch>
                          <FormSwitch.Input v-model="productForm.product_units[index].is_manufacturer_sku"
                            type="checkbox" :class="{
                              'border-danger': productForm.invalid(
                                `product_units.${index}.is_manufacturer_sku` as any,
                              ),
                            }" @change="productForm.validate(`product_units.${index}.is_manufacturer_sku` as any)" />
                        </FormSwitch>
                      </div>
                    </div>
                  </div>
                  <FormErrorMessages
                    :messages="(productForm.errors as any)[`product_units.${index}.is_manufacturer_sku`]" />
                </div>

                <!-- Column 2: Unit Name (Base Unit) -->
                <div class="col-span-12 lg:col-span-3" v-if="index === 0">
                  <!-- Unit Name Input -->
                  <FormLabel :class="{
                    'text-danger': productForm.invalid(`product_units.${index}.unit_id` as any),
                  }">
                    {{ t('views.product.fields.unit_id') }}
                  </FormLabel>
                  <!-- Unit Name Dropdown -->
                  <div class="flex items-center gap-2">
                    <div class="flex-1">
                      <FormSelectSearch v-model="productForm.product_units[index].unit_id"
                        v-model:search="unitSearch[index]" :options="getUnitOptions(index)"
                        :placeholder="t('components.dropdown.placeholder')" :class="{
                          'border-danger': productForm.invalid(`product_units.${index}.unit_id` as any),
                        }" @change="
                          () => {
                            updateUnitName(index);
                            productForm.validate(`product_units.${index}.unit_id` as any);
                          }
                        " @search="(q) => getUnitDDL(index, q)" @clear="clearUnit(index)" />
                    </div>
                  </div>
                  <FormErrorMessages :messages="(productForm.errors as any)[`product_units.${index}.unit_id`]" />
                </div>

                <!-- Column 2: Unit Name (Other Units) -->
                <div class="col-span-12 lg:col-span-2" v-else>
                  <FormLabel :class="{
                    'text-danger': productForm.invalid(`product_units.${index}.unit_id` as any),
                  }">
                    {{ t('views.product.fields.unit_id') }}
                  </FormLabel>
                  <div class="flex items-center gap-2">
                    <div class="flex-1">
                      <FormSelectSearch v-model="productForm.product_units[index].unit_id"
                        v-model:search="unitSearch[index]" :options="getUnitOptions(index)"
                        :placeholder="t('components.dropdown.placeholder')" :class="{
                          'border-danger': productForm.invalid(`product_units.${index}.unit_id` as any),
                        }" @change="
                          () => {
                            updateUnitName(index);
                            productForm.validate(`product_units.${index}.unit_id` as any);
                          }
                        " @search="(q) => getUnitDDL(index, q)" @clear="clearUnit(index)" />
                    </div>
                  </div>
                  <FormErrorMessages :messages="(productForm.errors as any)[`product_units.${index}.unit_id`]" />
                </div>

                <!-- Column 3: Conversion Value -->
                <div class="col-span-12 lg:col-span-1" v-if="index > 0">
                  <FormLabel :class="{
                    'text-danger': productForm.invalid(`product_units.${index}.conversion_value` as any),
                  }">
                    {{ t('views.product.fields.conversion_value') }}
                  </FormLabel>
                  <FormErrorMessages :messages="(productForm.errors as any)['product_units.is_primary_unit']" />
                  <FormInputCurrency v-model="productForm.product_units[index].conversion_value" :class="{
                    'border-danger': productForm.invalid(`product_units.${index}.conversion_value` as any),
                  }" :placeholder="t('views.product.fields.conversion_value')"
                    @change="productForm.validate(`product_units.${index}.conversion_value` as any)" />
                  <!-- base unit name -->
                  <div class="flex justify-end">
                    <FormLabel class="text-xs font-bold mt-1 text-slate-500 text-right">
                      {{ productForm.product_units[0].unit_name }}
                    </FormLabel>
                  </div>
                  <FormErrorMessages
                    :messages="(productForm.errors as any)[`product_units.${index}.conversion_value`]" />
                </div>

                <!-- Column 4: Price (Base Unit) -->
                <div class="col-span-12 lg:col-span-2" v-else>
                  <FormLabel :class="{
                    'text-danger': productForm.invalid(`product_units.${index}.price` as any),
                  }">
                    {{ t('views.product.fields.base_unit_price') }}
                  </FormLabel>
                  <FormInputCurrency v-model="productForm.product_units[index].price" :class="{
                    'border-danger': productForm.invalid(`product_units.${index}.price` as any),
                  }" :placeholder="t('views.product.fields.base_unit_price')"
                    @change="productForm.validate(`product_units.${index}.price` as any)" />
                  <FormErrorMessages :messages="(productForm.errors as any)[`product_units.${index}.price`]" />
                </div>

                <!-- Column 4: Price (Other-->
                <div class="col-span-12 lg:col-span-2" v-if="index > 0">
                  <FormLabel :class="{
                    'text-danger': productForm.invalid(`product_units.${index}.price` as any),
                  }">
                    {{ t('views.product.fields.price') }}
                  </FormLabel>
                  <FormInputCurrency v-model="productForm.product_units[index].price" :class="{
                    'border-danger': productForm.invalid(`product_units.${index}.price` as any),
                  }" :placeholder="t('views.product.fields.price')"
                    @change="productForm.validate(`product_units.${index}.price` as any)" />
                  <div v-if="
                    !productForm.product_units[index].is_base &&
                    productForm.product_units[index].conversion_value > 0 &&
                    productForm.product_units[index].price > 0
                  " class="text-xs text-slate-500 mt-1 text-right">
                    {{ t('views.product.fields.base_unit_price') }}:
                    {{
                      formatCurrency(
                        (
                          productForm.product_units[index].price / productForm.product_units[index].conversion_value
                        ).toFixed(2),
                      )
                    }}
                  </div>
                  <FormErrorMessages :messages="(productForm.errors as any)[`product_units.${index}.price`]" />
                </div>

                <!-- Column 6: Point (Base Unit) -->
                <div class="col-span-12 lg:col-span-1" v-if="index === 0">
                  <FormLabel :class="{
                    'text-danger': productForm.invalid(`product_units.${index}.point` as any),
                  }">
                    {{ t('views.product.fields.point') }}
                  </FormLabel>
                  <FormInputCurrency v-model="productForm.product_units[index].point" :class="{
                    'border-danger': productForm.invalid(`product_units.${index}.point` as any),
                  }" :placeholder="t('views.product.fields.point')"
                    @change="productForm.validate(`product_units.${index}.point` as any)" />
                  <FormErrorMessages :messages="(productForm.errors as any)[`product_units.${index}.point`]" />
                </div>

                <!-- Column 6: Point (Other Units) -->
                <div class="col-span-12 sm:col-span-1" v-if="index > 0">
                  <FormLabel :class="{
                    'text-danger': productForm.invalid(`product_units.${index}.point` as any),
                  }">
                    {{ t('views.product.fields.point') }}
                  </FormLabel>
                  <FormInputCurrency v-model="productForm.product_units[index].point" :class="{
                    'border-danger': productForm.invalid(`product_units.${index}.point` as any),
                  }" :placeholder="t('views.product.fields.point')"
                    @change="productForm.validate(`product_units.${index}.point` as any)" />
                  <FormErrorMessages :messages="(productForm.errors as any)[`product_units.${index}.point`]" />
                </div>

                <!-- Column 7: Is Primary Unit -->
                <div class="col-span-12 lg:col-span-2">
                  <FormCheck class="mt-9">
                    <FormCheck.Input type="radio" name="primary_unit" class="mr-2 border-slate-300" :class="{
                      'border-danger': (productForm.errors as any)['product_units.is_primary_unit'],
                    }" :checked="productForm.product_units[index].is_primary_unit" @change="setPrimaryUnit(index)" />
                    <FormCheck.Label class="text-sm" :class="{
                      'text-danger': (productForm.errors as any)['product_units.is_primary_unit'],
                      '': !(productForm.errors as any)['product_units.is_primary_unit'],
                    }">
                      {{ t('views.product.fields.is_primary_unit') }}
                    </FormCheck.Label>
                  </FormCheck>
                  <FormErrorMessages :messages="(productForm.errors as any)['product_units.is_primary_unit']" />
                </div>
              </div>
            </div>
          </div>

          <!-- Add Product Unit Button -->
          <div class="flex items-center justify-between mt-4">
            <FormLabel></FormLabel>
            <Button type="button" variant="primary" class="shadow-md" @click="addUnit">
              <Lucide icon="Plus" class="w-4 h-4 mr-2" />
              {{ t('views.product.actions.add_unit') }}
            </Button>
          </div>
        </div>
      </template>

      <!-- Buttons -->
      <template #card-items-button>
        <div class="flex gap-4">
          <Button type="submit" href="#" variant="primary" class="w-28 shadow-md" :disabled="productForm.validating">
            <Lucide v-if="productForm.validating" icon="Loader" class="animate-spin" />
            <template v-else>
              {{ t('components.buttons.submit') }}
            </template>
          </Button>
          <Button type="button" href="#" variant="soft-secondary" class="w-28 shadow-md" @click="resetForm">
            {{ t('components.buttons.reset') }}
          </Button>
        </div>
      </template>
    </TwoColumnsLayout>
  </form>
</template>
