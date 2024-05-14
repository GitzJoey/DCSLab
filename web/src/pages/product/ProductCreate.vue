<script setup lang="ts">
// #region Imports
import { onMounted, ref, computed, watch } from "vue";
import { useI18n } from "vue-i18n";
import ProductService from "../../services/ProductService";
import DashboardService from "../../services/DashboardService";
import CacheService from "../../services/CacheService";
import { DropDownOption } from "../../types/models/DropDownOption";
import { TwoColumnsLayout } from "../../base-components/Form/FormLayout";
import {
    FormInput,
    FormLabel,
    FormTextarea,
    FormSelect,
    FormInputCode,
    FormSwitch,
    FormErrorMessages
} from "../../base-components/Form";
import { TwoColumnsLayoutCards } from "../../base-components/Form/FormLayout/TwoColumnsLayout.vue";
import { CardState } from "../../types/enums/CardState";
import Button from "../../base-components/Button";
import { ViewMode } from "../../types/enums/ViewMode";
import { debounce } from "lodash";
import Lucide from "../../base-components/Lucide";
import { useSelectedUserLocationStore } from "../../stores/user-location";
import { useRouter } from "vue-router";
import { ErrorCode } from "../../types/enums/ErrorCode";
import { ReadAnyRequest } from "../../types/services/ServiceRequest";
import { ServiceResponse } from "../../types/services/ServiceResponse";
import { Collection } from "../../types/resources/Collection";
import { Resource } from "../../types/resources/Resource";
import { ProductGroup } from "../../types/models/ProductGroup";
import ProductGroupService from "../../services/ProductGroupService";
import BrandService from "../../services/BrandService";
import { Brand } from "../../types/models/Brand";
// #endregion

// #region Interfaces
// #endregion

// #region Declarations
const { t } = useI18n();
const router = useRouter();
const productServices = new ProductService();
const dashboardServices = new DashboardService();
const cacheServices = new CacheService();
const productGroupServices = new ProductGroupService();
const BrandServices = new BrandService();

const selectedUserLocationStore = useSelectedUserLocationStore();
// #endregion

// #region Props, Emits
const emits = defineEmits(['mode-state', 'loading-state']);
// #endregion

// #region Refs
const cards = ref<Array<TwoColumnsLayoutCards>>([
    { title: 'views.product.field_groups.company_info', state: CardState.Expanded, },
    { title: 'views.product.field_groups.product_data', state: CardState.Expanded, },
    { title: '', state: CardState.Hidden, id: 'button' }
]);

const productGroupDDL = ref<Array<ProductGroup> | null>([]);

const brandDDL = ref<Array<Brand> | null>([]);

const statusDDL = ref<Array<DropDownOption> | null>(null);

const productForm = productServices.useProductCreateForm();
// #endregion

// #region Computed
const isUserLocationSelected = computed(() => selectedUserLocationStore.isUserLocationSelected);
const selectedUserLocation = computed(() => selectedUserLocationStore.selectedUserLocation);
// #endregion

// #region Lifecycle Hooks
onMounted(async () => {
    emits('mode-state', ViewMode.FORM_CREATE);

    if (!isUserLocationSelected.value) {
        router.push({ name: 'side-menu-error-code', params: { code: ErrorCode.USERLOCATION_REQUIRED } });
    }

    getDDL();

    setCompanyIdData();
    loadFromCache();
});
// #endregion

// #region Methods
const setCompanyIdData = () => {
    productForm.setData({
        company_id: selectedUserLocation.value.company.id,
    });
}
const loadFromCache = () => {
    let data = cacheServices.getLastEntity('PRODUCT_CREATE') as Record<string, unknown>;
    if (!data) return;
    productForm.setData(data);
}

const getDDL = (): void => {
    dashboardServices.getStatusDDL().then((result: Array<DropDownOption> | null) => {
        statusDDL.value = result;
    });

    getProductGroupDDL();

    getBrandDDL();
}

const getProductGroupDDL = async (): Promise<void> => {
    const searchReq: ReadAnyRequest = {
        company_id: selectedUserLocation.value.company.id,
        search: '',
        refresh: true,
        paginate: false,
    };

    const result = await productGroupServices.readAny(searchReq);
    if (result.success && result.data) {
        productGroupDDL.value = result.data as Collection<Array<ProductGroup>>;
    }
}

const getBrandDDL = async (): Promise<void> => {
    const searchReq: ReadAnyRequest = {
        company_id: selectedUserLocation.value.company.id,
        search: '',
        refresh: true,
        paginate: false,
    };

    const result = await BrandServices.readAny(searchReq);
    if (result.success && result.data) {
        brandDDL.value = result.data as Collection<Array<Brand>>;
    }
}

const handleExpandCard = (index: number) => {
    if (cards.value[index].state === CardState.Collapsed) {
        cards.value[index].state = CardState.Expanded
    } else if (cards.value[index].state === CardState.Expanded) {
        cards.value[index].state = CardState.Collapsed
    }
}

const scrollToError = (id: string): void => {
    let el = document.getElementById(id);

    if (!el) return;

    el.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

const onSubmit = async () => {
    if (productForm.hasErrors) {
        scrollToError(Object.keys(productForm.errors)[0]);
    }
};

const resetForm = () => {
    productForm.reset();
    productForm.setErrors({});
}
// #endregion

// #region Watchers
watch(
    productForm,
    debounce((newValue): void => {
        cacheServices.setLastEntity('PRODUCT_CREATE', newValue.data())
    }, 500),
    { deep: true }
);
// #endregion
</script>

<template>
    <form id="productForm" @submit.prevent="onSubmit">
        <TwoColumnsLayout :cards="cards" :using-side-tab="false" @handle-expand-card="handleExpandCard">
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
            <template #card-items-1>
                <div class="p-5">
                    <div class="pb-4">
                        <FormLabel html-for="code" :class="{ 'text-danger': productForm.invalid('code') }">
                            {{ t('views.product.fields.code') }}
                        </FormLabel>
                        <FormInputCode id="code" v-model="productForm.code" name="code" type="text"
                            :class="{ 'border-danger': productForm.invalid('code') }"
                            :placeholder="t('views.product.fields.code')" @change="productForm.validate('code')" />
                        <FormErrorMessages :messages="productForm.errors.code" />
                    </div>
                    <div class="pb-4">
                        <FormLabel html-for="name" :class="{ 'text-danger': productForm.invalid('name') }">
                            {{ t('views.product.fields.name') }}
                        </FormLabel>
                        <FormInput id="name" v-model="productForm.name" name="name" type="text"
                            :class="{ 'border-danger': productForm.invalid('name') }"
                            :placeholder="t('views.product.fields.name')" @change="productForm.validate('name')" />
                        <FormErrorMessages :messages="productForm.errors.name" />
                    </div>
                    <div class="pb-4">
                        <FormLabel html-for="product_group"
                            :class="{ 'text-danger': productForm.invalid('product_group_id') }">
                            {{ t('views.product.fields.product_group') }}
                        </FormLabel>
                        <FormSelect id="product_group" v-model="productForm.product_group_id"
                            :class="{ 'border-danger': productForm.invalid('product_group_id') }">
                            <option value="">{{ t('components.dropdown.placeholder') }}</option>
                            <option v-for="productGroup in productGroupDDL.data" :key="productGroup.code" :value="productGroup.code">{{ t(productGroup.name) }}</option>
                        </FormSelect>
                    </div>
                    <div class="pb-4">
                        <FormLabel html-for="brand" :class="{ 'text-danger': productForm.invalid('brand_id') }">
                            {{ t('views.product.fields.brand') }}
                        </FormLabel>
                       
                        <FormSelect id="brand" v-model="productForm.brand_id" name="brand"
                            :class="{ 'border-danger': productForm.invalid('brand_id') }">
                            <option value="">{{ t('components.dropdown.placeholder') }}</option>
                            <option v-for="brand in brandDDL.data" :key="brand.code" :value="brand.code">{{ t(brand.name) }}</option>
                        </FormSelect>
                    </div>
                    <div class="pb-4">
                        <FormLabel html-for="taxable_supply" :class="{ 'text-danger': productForm.invalid('taxable_supply') }">
                            {{ t('views.product.fields.taxable_supply') }}
                        </FormLabel>
                        <FormSwitch class="mt-2">
                            <FormSwitch.Input id="taxable_supply" v-model="productForm.taxable_supply" name="taxable_supply" type="checkbox"
                                :class="{ 'border-danger': productForm.invalid('taxable_supply') }"
                                :placeholder="t('views.product.fields.taxable_supply')" />
                        </FormSwitch>
                        <FormErrorMessages :messages="productForm.errors.taxable_supply" />
                    </div>
                    <!-- <div class="pb-4">
                        <FormLabel html-for="is_main" :class="{ 'text-danger': productForm.invalid('is_main') }"
                            class="pr-5">
                            {{ t('views.product.fields.is_main') }}
                        </FormLabel>
                        <FormSwitch>
                            <FormSwitch.Input id="is_main" v-model="productForm.is_main" name="is_main" type="checkbox"
                                :class="{ 'border-danger': productForm.invalid('is_main') }"
                                :placeholder="t('views.product.fields.is_main')"
                                @change="productForm.validate('is_main')" />
                        </FormSwitch>
                        <FormErrorMessages :messages="productForm.errors.is_main" />
                    </div> -->
                    <div class="pb-4">
                        <FormLabel html-for="standard_rated_supply">
                            {{ t('views.product.fields.standard_rated_supply') }}
                        </FormLabel>
                        <FormInput id="standard_rated_supply" v-model="productForm.standard_rated_supply" name="standard_rated_supply" type="number"
                            :placeholder="t('views.product.fields.standard_rated_supply')" />
                    </div>
                    <div class="pb-4">
                        <FormLabel html-for="price_include_vat" :class="{ 'text-danger': productForm.invalid('price_include_vat') }">
                            {{ t('views.product.fields.price_include_vat') }}
                        </FormLabel>
                        <FormSwitch class="mt-2">
                            <FormSwitch.Input id="price_include_vat" v-model="productForm.price_include_vat" name="price_include_vat" type="checkbox"
                                :class="{ 'border-danger': productForm.invalid('price_include_vat') }"
                                :placeholder="t('views.product.fields.price_include_vat')" />
                        </FormSwitch>
                        <FormErrorMessages :messages="productForm.errors.price_include_vat" />
                    </div>
                    <div class="pb-4">
                        <FormLabel html-for="point">
                            {{ t('views.product.fields.point') }}
                        </FormLabel>
                        <FormInput id="point" v-model="productForm.point" name="point" type="number"
                            :placeholder="t('views.product.fields.point')" />
                    </div>
                    <div class="pb-4">
                        <FormLabel html-for="use_serial_number" :class="{ 'text-danger': productForm.invalid('use_serial_number') }">
                            {{ t('views.product.fields.use_serial_number') }}
                        </FormLabel>
                        <FormSwitch class="mt-2">
                            <FormSwitch.Input id="use_serial_number" v-model="productForm.use_serial_number" name="use_serial_number" type="checkbox"
                                :class="{ 'border-danger': productForm.invalid('use_serial_number') }"
                                :placeholder="t('views.product.fields.use_serial_number')" />
                        </FormSwitch>
                        <FormErrorMessages :messages="productForm.errors.use_serial_number" />
                    </div>
                    <div class="pb-4">
                        <FormLabel html-for="has_expiry_date" :class="{ 'text-danger': productForm.invalid('has_expiry_date') }">
                            {{ t('views.product.fields.has_expiry_date') }}
                        </FormLabel>
                        <FormSwitch class="mt-2">
                            <FormSwitch.Input id="has_expiry_date" v-model="productForm.has_expiry_date" name="has_expiry_date" type="checkbox"
                                :class="{ 'border-danger': productForm.invalid('has_expiry_date') }"
                                :placeholder="t('views.product.fields.has_expiry_date')" />
                        </FormSwitch>
                        <FormErrorMessages :messages="productForm.errors.has_expiry_date" />
                    </div>
                    <div class="pb-4">
                        <FormLabel html-for="remarks">
                            {{ t('views.product.fields.remarks') }}
                        </FormLabel>
                        <FormTextarea id="remarks" v-model="productForm.remarks" name="remarks" type="text"
                            :placeholder="t('views.product.fields.remarks')" rows="3" />
                    </div>
                    <div class="pb-4">
                        <FormLabel html-for="status" :class="{ 'text-danger': productForm.invalid('status') }">
                            {{ t('views.product.fields.status') }}
                        </FormLabel>
                        <FormSelect id="status" v-model="productForm.status" name="status"
                            :class="{ 'border-danger': productForm.invalid('status') }"
                            @change="productForm.validate('status')">
                            <option value="">{{ t('components.dropdown.placeholder') }}</option>
                            <option v-for="c in statusDDL" :key="c.code" :value="c.code">{{ t(c.name) }}</option>
                        </FormSelect>
                        <FormErrorMessages :messages="productForm.errors.status" />
                    </div>
                </div>
            </template>
            <template #card-items-button>
                <div class="flex gap-4">
                    <Button type="submit" href="#" variant="primary" class="w-28 shadow-md"
                        :disabled="productForm.validating || productForm.hasErrors">
                        <Lucide v-if="productForm.validating" icon="Loader" class="animate-spin" />
                        <template v-else>
                            {{ t("components.buttons.submit") }}
                        </template>
                    </Button>
                    <Button type="button" href="#" variant="soft-secondary" class="w-28 shadow-md" @click="resetForm">
                        {{ t("components.buttons.reset") }}
                    </Button>
                </div>
            </template>
        </TwoColumnsLayout>
    </form>
</template>