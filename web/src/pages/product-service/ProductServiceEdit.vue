<script setup lang="ts">
  // #region Imports
  import { computed, onMounted, ref, watch } from 'vue';
  import { useI18n } from 'vue-i18n';
  import { convertErrorTypeToAlertListType } from '@/utils/helper';
  import ProductService from '@/services/ProductService';
  import ProductCategoryService from '@/services/ProductCategoryService';
  import UnitService from '@/services/UnitService';
  import VatProfileService from '@/services/VatProfileService';
  import DashboardService from '@/services/DashboardService';
  import CacheService from '@/services/CacheService';
  import { TwoColumnsLayout } from '@/components/Base/Form/FormLayout';
  import {
    FormInput,
    FormLabel,
    FormSelect,
    FormInputCode,
    FormInputCurrency,
    FormErrorMessages,
    FormSwitch,
    FormTextarea,
    FormSelectSearch,
    FormImagesField,
  } from '@/components/Base/Form';
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
  import { ProductImage } from '@/types/models/ProductImage';
  // #endregion

  // #region Declarations
  const { t } = useI18n();
  const router = useRouter();
  const route = useRoute();
  const selectedUserLocationStore = useSelectedUserLocationStore();

  const productService = new ProductService();
  const productCategoryService = new ProductCategoryService();
  const unitService = new UnitService();
  const vatProfileService = new VatProfileService();
  const dashboardServices = new DashboardService();
  const cacheServices = new CacheService();
  // #endregion

  // #region Props, Emits
  const emits = defineEmits(['mode-state', 'loading-state', 'update-profile', 'show-alertplaceholder']);
  // #endregion

  // #region Refs
  const cards = ref<Array<TwoColumnsLayoutCards>>([
    {
      title: 'views.product_service.field_groups.company_info',
      state: CardState.Expanded,
    },
    {
      title: 'views.product_service.field_groups.product_data',
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

  const unitDDL = ref<Array<DropDownOption> | null>(null);
  const unitSearch = ref<string>('');
  const unitOptions = computed(() =>
    (unitDDL.value ?? []).map((item) => ({
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

  const productServiceForm = productService.useProductServiceUpdateForm(route.params.ulid.toString());

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
    await Promise.all([getCategoryDDL(), getUnitDDL(), getStatusDDL()]);
    await loadData();
    await getVatProfileDDL();
  });
  // #endregion

  // #region Methods
  const loadData = async () => {
    emits('loading-state', true);
    const result = await productService.read(route.params.ulid.toString());
    emits('loading-state', false);

    if (result.success && result.data) {
      productServiceForm.setData({
        company_id: result.data.company.id,
        code: result.data.code,
        category_id: result.data.category.id,
        name: result.data.name,
        default_vat_profile_id: result.data.default_vat_profile?.id ?? null,
        is_price_include_vat: result.data.is_price_include_vat,
        remarks: result.data.remarks,
        status: result.data.status,
        unit_id: result.data.product_units[0].unit.id,
        price: result.data.product_units[0].price,
        point: result.data.product_units[0].point,
        product_units: result.data.product_units,
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
        productServiceForm.image_hashes = images.map((img: any) => ({
          hash: img.hash,
          is_main: img.is_main,
        }));
      }
    } else {
      router.push({ name: 'side-menu-product-product-service-list' });
    }
  };

  const getCategoryDDL = async (search = ''): Promise<void> => {
    const result = await productCategoryService.readAnyGet({
      with_trashed: false,

      search: search,
      company_id: selectedUserLocation.value.company.id,
      type: 2,

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

  const getUnitDDL = async (search = ''): Promise<void> => {
    const result = await unitService.readAnyGet({
      with_trashed: false,

      search: search,
      company_id: selectedUserLocation.value.company.id,

      refresh: false,
      limit: 10,
    });

    if (result.success && result.data) {
      unitDDL.value = result.data.data.map((item: any) => ({
        code: item.id,
        name: item.name,
      }));
    }
  };

  const getVatProfileDDL = async (search = ''): Promise<void> => {
    const result = await vatProfileService.readAnyGet({
      with_trashed: false,
      search: search,
      company_id: selectedUserLocation.value.company.id,
      include_id: productServiceForm.default_vat_profile_id ?? undefined,
      refresh: false,
      limit: 10,
    });

    if (result.success && result.data) {
      vatProfileDDL.value = result.data.data.map((item: any) => ({
        code: item.id,
        name: item.name,
      }));
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
    if (productServiceForm.hasErrors) {
      scrollToError(Object.keys(productServiceForm.errors)[0]);
    }
    emits('loading-state', true);
    await productServiceForm
      .submit()
      .then(() => {
        emits('update-profile');
        router.push({ name: 'side-menu-product-product-service-list' });
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
    productServiceForm.reset();
    productServiceForm.setErrors({});
    await loadData(); // Reload data on reset
  };

  const setCode = () => {
    productServiceForm.forgetError('code');
    if (productServiceForm.code == '_AUTO_') {
      productServiceForm.setData({ code: '' });
    } else {
      productServiceForm.setData({ code: '_AUTO_' });
    }
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
    productServiceForm,
    debounce((newValue): void => {
      cacheServices.setLastEntity('PRODUCT_SERVICE_EDIT', newValue.data());
    }, 500),
    { deep: true },
  );
  // #endregion
</script>

<template>
  <form id="productServiceForm" @submit.prevent="onSubmit">
    <TwoColumnsLayout :cards="cards" :using-side-tab="false" @handle-expand-card="handleExpandCard">
      <!-- Card 1: Company Info -->
      <template #card-items-0>
        <div class="p-5">
          <FormLabel>
            {{ selectedUserLocation.company.code }}
            <br />
            {{ selectedUserLocation.company.name }}
          </FormLabel>
          <FormInput type="hidden" v-model="productServiceForm.company_id" />
        </div>
      </template>

      <!-- Card 2: Product Data -->
      <template #card-items-1>
        <div class="p-5">
          <div class="grid grid-cols-12 gap-4 gap-y-3">
            <div class="col-span-12 md:col-span-2 lg:col-span-3">
              <FormLabel :class="{ 'text-danger': productServiceForm.invalid('code') }">
                {{ t('views.product_service.fields.code') }}
              </FormLabel>
              <FormInputCode
                v-model="productServiceForm.code"
                :class="{ 'border-danger': productServiceForm.invalid('code') }"
                :placeholder="t('views.product_service.fields.code')"
                @set-auto="setCode"
                @change="productServiceForm.validate('code')"
              />
              <FormErrorMessages :messages="productServiceForm.errors.code" />
            </div>

            <div class="col-span-12 md:col-span-2 lg:col-span-2">
              <FormLabel
                :class="{
                  'text-danger': productServiceForm.invalid('category_id'),
                }"
              >
                {{ t('views.product_service.fields.category_id') }}
              </FormLabel>
              <FormSelectSearch
                v-model="productServiceForm.category_id"
                v-model:search="categorySearch"
                :options="categoryOptions"
                :placeholder="t('components.dropdown.placeholder')"
                :class="{
                  'border-danger': productServiceForm.invalid('category_id'),
                }"
                @change="productServiceForm.validate('category_id')"
                @search="getCategoryDDL"
              />
              <FormErrorMessages :messages="productServiceForm.errors.category_id" />
            </div>

            <div class="col-span-12 md:col-span-2 lg:col-span-2">
              <FormLabel :class="{ 'text-danger': productServiceForm.invalid('unit_id') }">
                {{ t('views.product_service.fields.unit_id') }}
              </FormLabel>
              <FormSelectSearch
                v-model="productServiceForm.unit_id"
                v-model:search="unitSearch"
                :options="unitOptions"
                :placeholder="t('components.dropdown.placeholder')"
                :class="{
                  'border-danger': productServiceForm.invalid('unit_id'),
                }"
                @change="productServiceForm.validate('unit_id')"
                @search="getUnitDDL"
              />
              <FormErrorMessages :messages="productServiceForm.errors.unit_id" />
            </div>

            <div class="col-span-12 md:col-span-6 lg:col-span-5">
              <FormLabel :class="{ 'text-danger': productServiceForm.invalid('name') }">
                {{ t('views.product_service.fields.name') }}
              </FormLabel>
              <FormInput
                v-model="productServiceForm.name"
                type="text"
                :class="{ 'border-danger': productServiceForm.invalid('name') }"
                :placeholder="t('views.product_service.fields.name')"
                @change="productServiceForm.validate('name')"
              />
              <FormErrorMessages :messages="productServiceForm.errors.name" />
            </div>

            <div class="col-span-12 md:col-span-2 lg:col-span-3">
              <FormLabel :class="{ 'text-danger': productServiceForm.invalid('default_vat_profile_id') }">
                {{ t('views.product_service.fields.default_vat_profile_id') }}
              </FormLabel>
              <FormSelectSearch
                v-model="productServiceForm.default_vat_profile_id"
                v-model:search="vatProfileSearch"
                :options="vatProfileOptions"
                :placeholder="t('components.dropdown.placeholder')"
                :class="{
                  'border-danger': productServiceForm.invalid('default_vat_profile_id'),
                }"
                @change="productServiceForm.validate('default_vat_profile_id')"
                @search="getVatProfileDDL"
              />
              <FormErrorMessages :messages="productServiceForm.errors.default_vat_profile_id" />
            </div>

            <div class="col-span-12 md:col-span-2 lg:col-span-2">
              <FormLabel
                :class="{
                  'text-danger': productServiceForm.invalid('is_price_include_vat'),
                }"
              >
                {{ t('views.product_service.fields.is_price_include_vat') }}
              </FormLabel>
              <FormSwitch class="mt-2">
                <FormSwitch.Input
                  v-model="productServiceForm.is_price_include_vat"
                  type="checkbox"
                  :class="{
                    'border-danger': productServiceForm.invalid('is_price_include_vat'),
                  }"
                  @change="productServiceForm.validate('is_price_include_vat')"
                />
              </FormSwitch>
              <FormErrorMessages :messages="productServiceForm.errors.is_price_include_vat" />
            </div>

            <div class="col-span-12 md:col-span-2 lg:col-span-2">
              <FormLabel :class="{ 'text-danger': productServiceForm.invalid('price') }">
                {{ t('views.product_service.fields.price') }}
              </FormLabel>
              <FormInputCurrency
                v-model="productServiceForm.price"
                :class="{ 'border-danger': productServiceForm.invalid('price') }"
                :placeholder="t('views.product_service.fields.price')"
                @change="productServiceForm.validate('price')"
              />
              <FormErrorMessages :messages="productServiceForm.errors.price" />
            </div>

            <div class="col-span-12 md:col-span-2 lg:col-span-2">
              <FormLabel :class="{ 'text-danger': productServiceForm.invalid('point') }">
                {{ t('views.product_service.fields.point') }}
              </FormLabel>
              <FormInput
                v-model="productServiceForm.point"
                type="number"
                :class="{ 'border-danger': productServiceForm.invalid('point') }"
                :placeholder="t('views.product_service.fields.point')"
                @change="productServiceForm.validate('point')"
              />
              <FormErrorMessages :messages="productServiceForm.errors.point" />
            </div>

            <div class="col-span-12 md:col-span-2 lg:col-span-3">
              <FormLabel :class="{ 'text-danger': productServiceForm.invalid('status') }">
                {{ t('views.product_service.fields.status') }}
              </FormLabel>
              <FormSelect
                v-model="productServiceForm.status"
                :class="{ 'border-danger': productServiceForm.invalid('status') }"
                @change="productServiceForm.validate('status')"
              >
                <option value="">
                  {{ t('components.dropdown.placeholder') }}
                </option>
                <option v-for="s in statusDDL" :key="s.code" :value="s.code">
                  {{ t(s.name) }}
                </option>
              </FormSelect>
              <FormErrorMessages :messages="productServiceForm.errors.status" />
            </div>

            <div class="col-span-12 md:col-span-12 lg:col-span-12">
              <FormLabel :class="{ 'text-danger': productServiceForm.invalid('remarks') }">
                {{ t('views.product_service.fields.remarks') }}
              </FormLabel>
              <FormTextarea
                v-model="productServiceForm.remarks"
                :class="{
                  'border-danger': productServiceForm.invalid('remarks'),
                }"
                :placeholder="t('views.product_service.fields.remarks')"
                @change="productServiceForm.validate('remarks')"
              />
              <FormErrorMessages :messages="productServiceForm.errors.remarks" />
            </div>

            <div class="col-span-12">
              <FormLabel>
                {{ t('views.product.fields.images') }}
              </FormLabel>
              <FormImagesField
                entity="product"
                v-model="productServiceForm.image_hashes"
                v-model:existing-images="uploadedImages"
                v-model:delete-image-ids="productServiceForm.delete_image_ids"
              />
            </div>
          </div>
        </div>
      </template>

      <!-- Buttons -->
      <template #card-items-button>
        <div class="flex gap-4">
          <Button
            type="submit"
            href="#"
            variant="primary"
            class="w-28 shadow-md"
            :disabled="productServiceForm.validating || productServiceForm.hasErrors"
          >
            <Lucide v-if="productServiceForm.validating" icon="Loader" class="animate-spin" />
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
