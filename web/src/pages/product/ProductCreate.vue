<script setup lang="ts">
// #region Imports
import { computed, onMounted, ref, watch } from "vue";
import { useI18n } from "vue-i18n";
import ProductService from "@/services/ProductService";
import ProductCategoryService from "@/services/ProductCategoryService";
import BrandService from "@/services/BrandService";
import UnitService from "@/services/UnitService";
import DashboardService from "@/services/DashboardService";
import CacheService from "@/services/CacheService";
import { TwoColumnsLayout } from "@/components/Base/Form/FormLayout";
import {
    FormInput,
    FormLabel,
    FormSelect,
    FormInputCode,
    FormInputCurrency,
    FormErrorMessages,
    FormSwitch,
    FormTextarea,
    FormTomSelect,
} from "@/components/Base/Form";
import { TwoColumnsLayoutCards } from "@/components/Base/Form/FormLayout/TwoColumnsLayout.vue";
import { CardState } from "@/types/enums/CardState";
import Button from "@/components/Base/Button";
import { ViewMode } from "@/types/enums/ViewMode";
import { debounce } from "lodash";
import Lucide from "@/components/Base/Lucide";
import { useSelectedUserLocationStore } from "@/stores/selected-user-location";
import { useRouter } from "vue-router";
import { type AlertPlaceholderProps } from "@/components/AlertPlaceholder/AlertPlaceholder.vue";
import { ErrorCode } from "@/types/enums/ErrorCode";
import { DropDownOption } from "@/types/models/DropDownOption";
import { formatCurrency } from "@/utils/helper";
import { AxiosError, isAxiosError } from "axios";
// #endregion

// #region Declarations
const { t } = useI18n();
const router = useRouter();
const selectedUserLocationStore = useSelectedUserLocationStore();

const productService = new ProductService();
const productCategoryService = new ProductCategoryService();
const brandService = new BrandService();
const unitService = new UnitService();
const dashboardServices = new DashboardService();
const cacheServices = new CacheService();
// #endregion

// #region Props, Emits
const emits = defineEmits([
    "mode-state",
    "loading-state",
    "update-profile",
    "show-alertplaceholder",
]);
// #endregion

// #region Refs
const cards = ref<Array<TwoColumnsLayoutCards>>([
    {
        title: "views.product.field_groups.company_info",
        state: CardState.Expanded,
    },
    {
        title: "views.product.field_groups.product_data",
        state: CardState.Expanded,
    },
    {
        title: "views.product.field_groups.price_tax_settings",
        state: CardState.Expanded,
    },
    {
        title: "views.product.field_groups.unit_settings",
        state: CardState.Expanded,
    },
    {
        title: "views.product.field_groups.other_settings",
        state: CardState.Expanded,
    },
    { title: "", state: CardState.Hidden, id: "button" },
]);

const categoryDDL = ref<Array<DropDownOption> | null>(null);
const brandDDL = ref<Array<DropDownOption> | null>(null);
const unitDDL = ref<Array<DropDownOption> | null>(null);
const statusDDL = ref<Array<DropDownOption> | null>(null);

const productForm = productService.useProductPhysicalStoreForm();
// #endregion

// #region Computed
const isUserLocationSelected = computed(
    () => selectedUserLocationStore.isUserLocationSelected
);
const selectedUserLocation = computed(
    () => selectedUserLocationStore.selectedUserLocation
);
// #endregion

// #region Lifecycle Hooks
onMounted(async () => {
    emits("mode-state", ViewMode.FORM_CREATE);
    if (!isUserLocationSelected.value) {
        router.push({
            name: "side-menu-error-code",
            params: { code: ErrorCode.USERLOCATION_REQUIRED },
        });
    }

    // Initialize one empty unit for the base unit if not exists
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
            remarks: ''
        });
    }

    await Promise.all([getCategoryDDL(), getBrandDDL(), getUnitDDL(), getStatusDDL()]);
    loadFromCache();
    setCompanyIdData();
});
// #endregion

// #region Methods
const setCompanyIdData = () => {
    productForm.setData({
        company_id: selectedUserLocation.value.company.id,
    });
};

const getCategoryDDL = async (search = ""): Promise<void> => {
    const result = await productCategoryService.readAnyGet({
        with_trashed: false,
        search: search,
        company_id: selectedUserLocation.value.company.id,
        type: 1, // Product Type
        refresh: false,
        limit: 10
    });

    if (result.success && result.data) {
        categoryDDL.value = result.data.data.map((item: any) => ({
            code: item.id,
            name: item.name
        }));
    }
};

const getBrandDDL = async (search = ""): Promise<void> => {
    const result = await brandService.readAnyGet({
        with_trashed: false,
        search: search,
        company_id: selectedUserLocation.value.company.id,
        refresh: false,
        limit: 10
    });

    if (result.success && result.data) {
        brandDDL.value = result.data.data.map((item: any) => ({
            code: item.id,
            name: item.name
        }));
    }
};

const getUnitDDL = async (search = ""): Promise<void> => {
    const result = await unitService.readAnyGet({
        with_trashed: false,
        search: search,
        company_id: selectedUserLocation.value.company.id,
        refresh: false,
        limit: 10
    });

    if (result.success && result.data) {
        unitDDL.value = result.data.data.map((item: any) => ({
            code: item.id,
            name: item.name
        }));

        productForm.product_units.forEach((u: any, index: number) => {
            if (u.unit_id && !u.unit_name) {
                const match = unitDDL.value?.find(opt => opt.code === u.unit_id);
                if (match) u.unit_name = match.name;
            }
        });
    }
};

const getStatusDDL = async (): Promise<void> => {
    const result = await dashboardServices.getStatusDDL(false);
    if (result) {
        statusDDL.value = result;
    }
};

const loadFromCache = () => {
    let data = cacheServices.getLastEntity("PRODUCT_CREATE") as Record<
        string,
        unknown
    >;
    if (!data) return;
    if (!data.slug) data.slug = '_AUTO_';
    productForm.setData(data);
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
    el.scrollIntoView({ behavior: "smooth", block: "center" });
};

const onSubmit = async () => {
    if (productForm.hasErrors) {
        scrollToError(Object.keys(productForm.errors)[0]);
    }
    emits("loading-state", true);
    await productForm
        .submit()
        .then(() => {
            resetForm();
            emits("update-profile");
            router.push({ name: "side-menu-product-product-list" });
        })
        .catch((error) => {
            const errorList: Record<string, Array<string>> = convertErrorTypeToAlertListType(error);
            showAlertPlaceholder("danger", "", errorList);
        })
        .finally(() => {
            emits("loading-state", false);
        });
};

const resetForm = () => {
    productForm.reset();
    productForm.setErrors({});
    // Re-initialize unit
    productForm.setData({
        product_units: [{
            code: '_AUTO_',
            is_manufacturer_sku: false,
            unit_id: '',
            unit_name: '',
            price: 0,
            is_base: true,
            conversion_value: 1,
            is_primary_unit: true,
            point: 0,
            remarks: ''
        }]
    });
};

const setCode = () => {
    productForm.forgetError("code");
    if (productForm.code == "_AUTO_") {
        productForm.setData({ code: "" });
    } else {
        productForm.setData({ code: "_AUTO_" });
    }
};

const setUnitCode = (index: number) => {
    if (productForm.product_units[index].code == "_AUTO_") {
        productForm.product_units[index].code = "";
    } else {
        productForm.product_units[index].code = "_AUTO_";
    }
};

const setPrimaryUnit = (index: number) => {
    productForm.product_units.forEach((u: any, i: number) => {
        u.is_primary_unit = i === index;
    });
};

const addUnit = () => {
    productForm.product_units.push({
        code: "_AUTO_",
        is_manufacturer_sku: false,
        unit_id: "",
        unit_name: "",
        price: 0,
        is_base: false,
        conversion_value: 1,
        is_primary_unit: false,
        point: 0,
        remarks: "",
    } as any);
};

const updateUnitName = (index: number, newUnitId?: string) => {
    const unitId = newUnitId ?? productForm.product_units[index].unit_id;
    if (!unitId) {
        productForm.product_units[index].unit_name = "";
        return;
    }
    const unit = unitDDL.value?.find(u => u.code === unitId);
    if (unit) {
        productForm.product_units[index].unit_name = unit.name;
    }
};

const clearUnit = (index: number) => {
    productForm.product_units[index].unit_id = "";
    productForm.product_units[index].unit_name = "";
};

const removeUnit = (index: number) => {
    productForm.product_units.splice(index, 1);
};

const showAlertPlaceholder = (
    pAlertType: "hidden" | "danger" | "success" | "warning" | "pending" | "dark",
    pTitle: string,
    pAlertList: Record<string, Array<string>> | null
) => {
    let ap: AlertPlaceholderProps = {
        alertType: pAlertType,
        title: pTitle,
        alertList: pAlertList,
    };
    emits("show-alertplaceholder", ap);
};

const convertErrorTypeToAlertListType = (error: unknown) => {
    const record: Record<string, Array<string>> = {};

    const anyError = error as any;
    const response = isAxiosError(error)
        ? (error as AxiosError).response
        : anyError?.response;

    if (response && response.data) {
        const data = response.data as any;

        if (data.errors && typeof data.errors === "object") {
            for (const key of Object.keys(data.errors)) {
                const value = data.errors[key];

                if (Array.isArray(value)) {
                    record[key] = value;
                } else if (value !== undefined && value !== null) {
                    record[key] = [String(value)];
                }
            }

            return record;
        }

        if (data.message) {
            record.error = [String(data.message)];
            return record;
        }
    }

    if (error instanceof Error && error.message) {
        record.error = [error.message];
    } else {
        record.error = ["Unknown error"];
    }

    return record;
};
// #endregion

// #region Watchers
watch(
    productForm,
    debounce((newValue): void => {
        cacheServices.setLastEntity("PRODUCT_CREATE", newValue.data());
    }, 500),
    { deep: true }
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
                        <div class="col-span-12 sm:col-span-6">
                            <FormLabel :class="{ 'text-danger': productForm.invalid('code') }">
                                {{ t("views.product.fields.code") }}
                            </FormLabel>
                            <FormInputCode v-model="productForm.code"
                                :class="{ 'border-danger': productForm.invalid('code') }"
                                :placeholder="t('views.product.fields.code')" @set-auto="setCode"
                                @change="productForm.validate('code')" />
                            <FormErrorMessages :messages="productForm.errors.code" />
                        </div>

                        <div class="col-span-12 sm:col-span-6">
                            <FormLabel :class="{ 'text-danger': productForm.invalid('category_id') }">
                                {{ t("views.product.fields.category_id") }}
                            </FormLabel>
                            <FormTomSelect v-model="productForm.category_id"
                                :class="{ 'border-danger': productForm.invalid('category_id') }"
                                @change="productForm.validate('category_id')" @search="getCategoryDDL" :options="{
                                    placeholder: t('components.dropdown.placeholder'),
                                }">
                                <option v-for="c in categoryDDL" :key="c.code" :value="c.code">
                                    {{ c.name }}
                                </option>
                            </FormTomSelect>
                            <FormErrorMessages :messages="productForm.errors.category_id" />
                        </div>

                        <div class="col-span-12 sm:col-span-6">
                            <FormLabel :class="{ 'text-danger': productForm.invalid('brand_id') }">
                                {{ t("views.product.fields.brand_id") }}
                            </FormLabel>
                            <FormTomSelect v-model="productForm.brand_id"
                                :class="{ 'border-danger': productForm.invalid('brand_id') }"
                                @change="productForm.validate('brand_id')" @search="getBrandDDL" :options="{
                                    placeholder: t('components.dropdown.placeholder'),
                                }">
                                <option v-for="c in brandDDL" :key="c.code" :value="c.code">
                                    {{ c.name }}
                                </option>
                            </FormTomSelect>
                            <FormErrorMessages :messages="productForm.errors.brand_id" />
                        </div>

                        <div class="col-span-12 sm:col-span-6">
                            <FormLabel :class="{ 'text-danger': productForm.invalid('name') }">
                                {{ t("views.product.fields.name") }}
                            </FormLabel>
                            <FormInput v-model="productForm.name" type="text"
                                :class="{ 'border-danger': productForm.invalid('name') }"
                                :placeholder="t('views.product.fields.name')" @change="productForm.validate('name')" />
                            <FormErrorMessages :messages="productForm.errors.name" />
                        </div>

                        <div class="col-span-12 sm:col-span-6">
                            <FormLabel :class="{ 'text-danger': productForm.invalid('is_use_serial_number') }">
                                {{ t("views.product.fields.is_use_serial_number") }}
                            </FormLabel>
                            <FormSwitch class="mt-2">
                                <FormSwitch.Input v-model="productForm.is_use_serial_number" type="checkbox"
                                    :class="{ 'border-danger': productForm.invalid('is_use_serial_number') }"
                                    @change="productForm.validate('is_use_serial_number')" />
                            </FormSwitch>
                            <FormErrorMessages :messages="productForm.errors.is_use_serial_number" />
                        </div>

                        <div class="col-span-12 sm:col-span-6">
                            <FormLabel :class="{ 'text-danger': productForm.invalid('is_expirable') }">
                                {{ t("views.product.fields.is_expirable") }}
                            </FormLabel>
                            <FormSwitch class="mt-2">
                                <FormSwitch.Input v-model="productForm.is_expirable" type="checkbox"
                                    :class="{ 'border-danger': productForm.invalid('is_expirable') }"
                                    @change="productForm.validate('is_expirable')" />
                            </FormSwitch>
                            <FormErrorMessages :messages="productForm.errors.is_expirable" />
                        </div>
                    </div>
                </div>
            </template>

            <!-- Card 3: Price & Tax Settings -->
            <template #card-items-2>
                <div class="p-5">
                    <div class="grid grid-cols-12 gap-4 gap-y-3">
                        <div class="col-span-12">
                            <FormLabel :class="{ 'text-danger': productForm.invalid('is_taxable') }">
                                {{ t("views.product.fields.is_taxable") }}
                            </FormLabel>
                            <FormSwitch class="mt-2">
                                <FormSwitch.Input v-model="productForm.is_taxable" type="checkbox"
                                    :class="{ 'border-danger': productForm.invalid('is_taxable') }"
                                    @change="productForm.validate('is_taxable')" />
                            </FormSwitch>
                            <FormErrorMessages :messages="productForm.errors.is_taxable" />
                        </div>

                        <div class="col-span-12 sm:col-span-6" v-if="productForm.is_taxable">
                            <FormLabel :class="{ 'text-danger': productForm.invalid('vat_rate') }">
                                {{ t("views.product.fields.vat_rate") }}
                            </FormLabel>
                            <FormInputCurrency v-model="productForm.vat_rate"
                                :class="{ 'border-danger': productForm.invalid('vat_rate') }"
                                :placeholder="t('views.product.fields.vat_rate')"
                                @change="productForm.validate('vat_rate')" />
                            <FormErrorMessages :messages="productForm.errors.vat_rate" />
                        </div>

                        <div class="col-span-12 sm:col-span-6" v-if="productForm.is_taxable">
                            <FormLabel :class="{
                                'text-danger': productForm.invalid('is_price_include_vat'),
                            }">
                                {{ t("views.product.fields.is_price_include_vat") }}
                            </FormLabel>
                            <FormSwitch class="mt-2">
                                <FormSwitch.Input v-model="productForm.is_price_include_vat" type="checkbox" :class="{
                                    'border-danger': productForm.invalid(
                                        'is_price_include_vat'
                                    ),
                                }" @change="productForm.validate('is_price_include_vat')" />
                            </FormSwitch>
                            <FormErrorMessages :messages="productForm.errors.is_price_include_vat" />
                        </div>
                    </div>
                </div>
            </template>

            <!-- Card 4: Unit Settings (Base Unit) -->
            <template #card-items-3>
                <div class="p-5" v-if="productForm.product_units.length > 0">
                    <div v-for="(unit, index) in productForm.product_units" :key="index"
                        class="border-b border-slate-200/60 dark:border-darkmode-400 last:border-0 pb-5 mb-5 last:pb-0 last:mb-0">
                        <div class="font-medium text-base mb-5 flex items-center justify-between">
                            <span>
                                {{ index === 0 ? t("views.product.fields.base_unit") :
                                    t("views.product.fields.other_unit") + " #" + index }}
                            </span>
                            <Button v-if="index > 0" variant="danger" size="sm" @click="removeUnit(index)">
                                <Lucide icon="Trash2" class="w-4 h-4" />
                            </Button>
                        </div>

                        <div class="grid grid-cols-12 gap-4 gap-y-3">
                            <div class="col-span-12 sm:col-span-6">
                                <FormLabel
                                    :class="{ 'text-danger': productForm.invalid(`product_units.${index}.code` as any) }">
                                    {{ t("views.product.fields.unit_code") }}
                                </FormLabel>
                                <FormInputCode v-model="productForm.product_units[index].code"
                                    :class="{ 'border-danger': productForm.invalid(`product_units.${index}.code` as any) }"
                                    :placeholder="t('views.product.fields.unit_code')" @set-auto="setUnitCode(index)"
                                    @change="productForm.validate(`product_units.${index}.code` as any)" />
                                <FormErrorMessages
                                    :messages="(productForm.errors as any)[`product_units.${index}.code`]" />
                            </div>

                            <div class="col-span-12 sm:col-span-6">
                                <FormLabel
                                    :class="{ 'text-danger': productForm.invalid(`product_units.${index}.unit_id` as any) }">
                                    {{ t("views.product.fields.unit_id") }}
                                </FormLabel>
                                <div v-if="productForm.product_units[index].unit_id && productForm.product_units[index].unit_name" class="relative">
                                    <div class="form-control border rounded-md px-3 py-2 bg-slate-50 dark:bg-darkmode-800 text-slate-700 dark:text-slate-300">
                                        {{ productForm.product_units[index].unit_name }}
                                    </div>
                                    <button type="button" class="absolute right-3 top-2.5 text-slate-500 hover:text-danger" @click="clearUnit(index)">
                                        <Lucide icon="X" class="w-4 h-4" />
                                    </button>
                                </div>
                                <FormTomSelect v-else v-model="productForm.product_units[index].unit_id"
                                    :class="{ 'border-danger': productForm.invalid(`product_units.${index}.unit_id` as any) }"
                                    @update:model-value="(val) => {
                                        updateUnitName(index, val as string);
                                        productForm.validate(`product_units.${index}.unit_id` as any);
                                    }"
                                    @search="getUnitDDL" :options="{
                                    placeholder: t('components.dropdown.placeholder'),
                                }">
                                <option v-for="c in unitDDL" :key="c.code" :value="c.code">
                                    {{ c.name }}
                                </option>
                            </FormTomSelect>
                                <FormErrorMessages
                                    :messages="(productForm.errors as any)[`product_units.${index}.unit_id`]" />
                            </div>

                            <div class="col-span-12 sm:col-span-6" v-if="index > 0">
                                <FormLabel
                                    :class="{ 'text-danger': productForm.invalid(`product_units.${index}.conversion_value` as any) }">
                                    {{ t("views.product.fields.conversion_value") }}
                                </FormLabel>
                                <FormInput type="number" v-model="productForm.product_units[index].conversion_value"
                                    :class="{ 'border-danger': productForm.invalid(`product_units.${index}.conversion_value` as any) }"
                                    :placeholder="t('views.product.fields.conversion_value')"
                                    @change="productForm.validate(`product_units.${index}.conversion_value` as any)" />
                                <FormErrorMessages
                                    :messages="(productForm.errors as any)[`product_units.${index}.conversion_value`]" />
                            </div>

                            <div
                                class="col-span-12 sm:col-span-6"
                                v-if="index > 0"
                            >
                                <FormLabel
                                    :class="{ 'text-danger': productForm.invalid(`product_units.${index}.price` as any) }">
                                    {{ t("views.product.fields.price") }}
                                </FormLabel>
                                <FormInputCurrency
                                    v-model="productForm.product_units[index].price"
                                    :class="{ 'border-danger': productForm.invalid(`product_units.${index}.price` as any) }"
                                    :placeholder="t('views.product.fields.price')"
                                    @change="productForm.validate(`product_units.${index}.price` as any)"
                                />
                                <div
                                    v-if="!productForm.product_units[index].is_base && productForm.product_units[index].conversion_value > 0 && productForm.product_units[index].price > 0"
                                    class="text-xs text-slate-500 mt-1 text-right"
                                >
                                    {{ t("views.product.fields.base_unit_price") }}:
                                    {{ formatCurrency((productForm.product_units[index].price / productForm.product_units[index].conversion_value).toFixed(2)) }}
                                </div>
                                <FormErrorMessages
                                    :messages="(productForm.errors as any)[`product_units.${index}.price`]"
                                />
                            </div>

                            <div
                                class="col-span-12 sm:col-span-4"
                                v-else
                            >
                                <FormLabel
                                    :class="{ 'text-danger': productForm.invalid(`product_units.${index}.price` as any) }">
                                    {{ t("views.product.fields.price") }}
                                </FormLabel>
                                <FormInputCurrency
                                    v-model="productForm.product_units[index].price"
                                    :class="{ 'border-danger': productForm.invalid(`product_units.${index}.price` as any) }"
                                    :placeholder="t('views.product.fields.price')"
                                    @change="productForm.validate(`product_units.${index}.price` as any)"
                                />
                                <FormErrorMessages
                                    :messages="(productForm.errors as any)[`product_units.${index}.price`]"
                                />
                            </div>

                            <div
                                class="col-span-12 sm:col-span-4"
                                v-if="index === 0"
                            >
                                <FormLabel
                                    :class="{ 'text-danger': productForm.invalid(`product_units.${index}.point` as any) }">
                                    {{ t("views.product.fields.point") }}
                                </FormLabel>
                                <FormInput
                                    v-model="productForm.product_units[index].point"
                                    type="number"
                                    :class="{
                                        'border-danger': productForm.invalid(`product_units.${index}.point` as any),
                                    }"
                                    :placeholder="t('views.product.fields.point')"
                                    @change="productForm.validate(`product_units.${index}.point` as any)"
                                />
                                <FormErrorMessages
                                    :messages="(productForm.errors as any)[`product_units.${index}.point`]"
                                />
                            </div>

                            <div
                                class="col-span-12 sm:col-span-4"
                                v-if="index === 0"
                            >
                                <FormLabel class="opacity-0 select-none">
                                    {{ t("views.product.fields.is_primary_unit") }}
                                </FormLabel>
                                <div class="mt-2 flex items-center">
                                    <input
                                        type="radio"
                                        name="primary_unit"
                                        class="form-check-input border-slate-300"
                                        :checked="productForm.product_units[index].is_primary_unit"
                                        @change="setPrimaryUnit(index)"
                                    />
                                    <span
                                        class="ml-2 text-sm"
                                        :class="{
                                            'text-danger': (productForm.errors as any)['product_units.is_primary_unit'],
                                            'text-slate-700': !(productForm.errors as any)['product_units.is_primary_unit'],
                                        }"
                                    >
                                        {{ t("views.product.fields.is_primary_unit") }}
                                    </span>
                                </div>
                                <FormErrorMessages
                                    :messages="(productForm.errors as any)['product_units.is_primary_unit']"
                                />
                            </div>

                            <div
                                class="col-span-12 sm:col-span-6"
                                v-if="index > 0"
                            >
                                <FormLabel
                                    :class="{ 'text-danger': productForm.invalid(`product_units.${index}.point` as any) }">
                                    {{ t("views.product.fields.point") }}
                                </FormLabel>
                                <div class="mt-2 flex items-center">
                                    <FormInput
                                        v-model="productForm.product_units[index].point"
                                        type="number"
                                        :class="{
                                            'border-danger': productForm.invalid(`product_units.${index}.point` as any),
                                        }"
                                        :placeholder="t('views.product.fields.point')"
                                        @change="productForm.validate(`product_units.${index}.point` as any)"
                                    />
                                    <div class="ml-4 flex items-center">
                                        <input
                                            type="radio"
                                            name="primary_unit"
                                            class="form-check-input border-slate-300"
                                            :checked="productForm.product_units[index].is_primary_unit"
                                            @change="setPrimaryUnit(index)"
                                        />
                                        <span
                                            class="ml-2 text-sm"
                                            :class="{
                                                'text-danger': (productForm.errors as any)['product_units.is_primary_unit'],
                                                'text-slate-700': !(productForm.errors as any)['product_units.is_primary_unit'],
                                            }"
                                        >
                                            {{ t("views.product.fields.is_primary_unit") }}
                                        </span>
                                    </div>
                                </div>
                                <FormErrorMessages
                                    :messages="(productForm.errors as any)[`product_units.${index}.point`]"
                                />
                                <FormErrorMessages
                                    :messages="(productForm.errors as any)['product_units.is_primary_unit']"
                                />
                            </div>
                        </div>
                    </div>

                    <Button variant="outline-primary" class="w-full mt-5" @click="addUnit">
                        <Lucide icon="Plus" class="w-4 h-4 mr-2" />
                        {{ t("views.product.actions.add_unit") }}
                    </Button>
                </div>
            </template>

            <!-- Card 5: Other Settings -->
            <template #card-items-4>
                <div class="p-5">
                    <div class="grid grid-cols-12 gap-4 gap-y-3">
                        <div class="col-span-12 sm:col-span-6">
                            <FormLabel :class="{ 'text-danger': productForm.invalid('status') }">
                                {{ t("views.product.fields.status") }}
                            </FormLabel>
                            <FormSelect v-model="productForm.status"
                                :class="{ 'border-danger': productForm.invalid('status') }"
                                @change="productForm.validate('status')">
                                <option value="">{{ t("components.dropdown.placeholder") }}</option>
                                <option v-for="s in statusDDL" :key="s.code" :value="s.code">
                                    {{ t(s.name) }}
                                </option>
                            </FormSelect>
                            <FormErrorMessages :messages="productForm.errors.status" />
                        </div>

                        <div class="col-span-12">
                            <FormLabel :class="{ 'text-danger': productForm.invalid('remarks') }">
                                {{ t("views.product.fields.remarks") }}
                            </FormLabel>
                            <FormTextarea v-model="productForm.remarks"
                                :class="{ 'border-danger': productForm.invalid('remarks') }"
                                :placeholder="t('views.product.fields.remarks')"
                                @change="productForm.validate('remarks')" />
                            <FormErrorMessages :messages="productForm.errors.remarks" />
                        </div>
                    </div>
                </div>
            </template>

            <!-- Buttons -->
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
