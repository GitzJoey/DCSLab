<script setup lang="ts">
// #region Imports
import { computed, onMounted, ref, watch } from "vue";
import { useI18n } from "vue-i18n";
import { convertErrorTypeToAlertListType } from "@/utils/helper";
import ProductService from "@/services/ProductService";
import ProductCategoryService from "@/services/ProductCategoryService";
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
// #endregion

// #region Declarations
const { t } = useI18n();
const router = useRouter();
const selectedUserLocationStore = useSelectedUserLocationStore();

const productService = new ProductService();
const productCategoryService = new ProductCategoryService();
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
        title: "views.product_service.field_groups.company_info",
        state: CardState.Expanded,
    },
    {
        title: "views.product_service.field_groups.product_data",
        state: CardState.Expanded,
    },
    {
        title: "views.product_service.field_groups.price_tax_settings",
        state: CardState.Expanded,
    },
    {
        title: "views.product_service.field_groups.other_settings",
        state: CardState.Expanded,
    },
    { title: "", state: CardState.Hidden, id: "button" },
]);

const categoryDDL = ref<Array<DropDownOption> | null>(null);
const unitDDL = ref<Array<DropDownOption> | null>(null);
const statusDDL = ref<Array<DropDownOption> | null>(null);

const productServiceForm = productService.useProductServiceStoreForm();
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
    await Promise.all([getCategoryDDL(), getUnitDDL(), getStatusDDL()]);
    loadFromCache();
    setCompanyIdData();
});
// #endregion

// #region Methods
const setCompanyIdData = () => {
    productServiceForm.setData({
        company_id: selectedUserLocation.value.company.id,
    });
};

const getCategoryDDL = async (search = ""): Promise<void> => {
    const result = await productCategoryService.readAnyGet({
        with_trashed: false,

        search: search,
        company_id: selectedUserLocation.value.company.id,
        type: 2,

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
    }
};

const getStatusDDL = async (): Promise<void> => {
    const result = await dashboardServices.getStatusDDL(false);
    if (result) {
        statusDDL.value = result;
    }
};

const loadFromCache = () => {
    let data = cacheServices.getLastEntity("PRODUCT_SERVICE_CREATE") as Record<
        string,
        unknown
    >;
    if (!data) return;
    productServiceForm.setData(data);
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
    if (productServiceForm.hasErrors) {
        scrollToError(Object.keys(productServiceForm.errors)[0]);
    }
    emits("loading-state", true);
    await productServiceForm
        .submit()
        .then(() => {
            resetForm();
            emits("update-profile");
            router.push({ name: "side-menu-product-product-service-list" }); // Make sure this route exists or is created
        })
        .catch((error) => {
            let errorList: Record<
                string,
                Array<string>
            > = convertErrorTypeToAlertListType(error);
            showAlertPlaceholder("danger", "", errorList);
        })
        .finally(() => {
            emits("loading-state", false);
        });
};

const resetForm = () => {
    productServiceForm.reset();
    productServiceForm.setErrors({});
};

const setCode = () => {
    productServiceForm.forgetError("code");
    if (productServiceForm.code == "_AUTO_") {
        productServiceForm.setData({ code: "" });
    } else {
        productServiceForm.setData({ code: "_AUTO_" });
    }
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

// #region Watchers
watch(
    productServiceForm,
    debounce((newValue): void => {
        cacheServices.setLastEntity("PRODUCT_SERVICE_CREATE", newValue.data());
        if (productServiceForm.hasErrors) {
        }
    }, 500),
    { deep: true }
);
// #endregion
</script>

<template>
    <form id="productServiceForm" @submit.prevent="onSubmit">
        <TwoColumnsLayout
            :cards="cards"
            :using-side-tab="false"
            @handle-expand-card="handleExpandCard"
        >
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
                    <div class="pb-4">
                        <FormLabel
                            :class="{ 'text-danger': productServiceForm.invalid('code') }"
                        >
                            {{ t("views.product_service.fields.code") }}
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

                    <div class="pb-4">
                        <FormLabel
                            :class="{ 'text-danger': productServiceForm.invalid('category_id') }"
                        >
                            {{ t("views.product_service.fields.category_id") }}
                        </FormLabel>
                        <FormTomSelect
                            v-model="productServiceForm.category_id"
                            :class="{ 'border-danger': productServiceForm.invalid('category_id') }"
                            @change="productServiceForm.validate('category_id')"
                            @search="getCategoryDDL"
                            :options="{
                                placeholder: t('components.dropdown.placeholder'),
                            }"
                        >
                            <option v-for="c in categoryDDL" :key="c.code" :value="c.code">
                                {{ c.name }}
                            </option>
                        </FormTomSelect>
                        <FormErrorMessages :messages="productServiceForm.errors.category_id" />
                    </div>

                    <div class="pb-4">
                        <FormLabel
                            :class="{ 'text-danger': productServiceForm.invalid('name') }"
                        >
                            {{ t("views.product_service.fields.name") }}
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

                    <div class="pb-4">
                        <FormLabel
                            :class="{ 'text-danger': productServiceForm.invalid('unit_id') }"
                        >
                            {{ t("views.product_service.fields.unit_id") }}
                        </FormLabel>
                        <FormTomSelect
                            v-model="productServiceForm.unit_id"
                            :class="{ 'border-danger': productServiceForm.invalid('unit_id') }"
                            @change="productServiceForm.validate('unit_id')"
                            @search="getUnitDDL"
                            :options="{
                                placeholder: t('components.dropdown.placeholder'),
                            }"
                        >
                            <option v-for="c in unitDDL" :key="c.code" :value="c.code">
                                {{ c.name }}
                            </option>
                        </FormTomSelect>
                        <FormErrorMessages :messages="productServiceForm.errors.unit_id" />
                    </div>
                </div>
            </template>

            <!-- Card 3: Price & Tax Settings -->
            <template #card-items-2>
                <div class="p-5">
                    <div class="pb-4">
                        <FormLabel
                            :class="{ 'text-danger': productServiceForm.invalid('price') }"
                        >
                            {{ t("views.product_service.fields.price") }}
                        </FormLabel>
                        <FormInputCurrency
                            v-model="productServiceForm.price"
                            :class="{ 'border-danger': productServiceForm.invalid('price') }"
                            :placeholder="t('views.product_service.fields.price')"
                            @change="productServiceForm.validate('price')"
                        />
                        <FormErrorMessages :messages="productServiceForm.errors.price" />
                    </div>

                    <div class="pb-4">
                        <FormLabel
                            :class="{ 'text-danger': productServiceForm.invalid('point') }"
                        >
                            {{ t("views.product_service.fields.point") }}
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

                    <div class="pb-4">
                        <FormLabel :class="{ 'text-danger': productServiceForm.invalid('is_taxable') }">
                            {{ t("views.product_service.fields.is_taxable") }}
                        </FormLabel>
                        <FormSwitch class="mt-2">
                            <FormSwitch.Input
                                v-model="productServiceForm.is_taxable"
                                type="checkbox"
                                :class="{ 'border-danger': productServiceForm.invalid('is_taxable') }"
                                @change="productServiceForm.validate('is_taxable')"
                            />
                        </FormSwitch>
                        <FormErrorMessages :messages="productServiceForm.errors.is_taxable" />
                    </div>

                    <div class="pb-4" v-if="productServiceForm.is_taxable">
                        <FormLabel
                            :class="{ 'text-danger': productServiceForm.invalid('vat_rate') }"
                        >
                            {{ t("views.product_service.fields.vat_rate") }}
                        </FormLabel>
                        <FormInputCurrency
                            v-model="productServiceForm.vat_rate"
                            :class="{ 'border-danger': productServiceForm.invalid('vat_rate') }"
                            :placeholder="t('views.product_service.fields.vat_rate')"
                            @change="productServiceForm.validate('vat_rate')"
                        />
                        <FormErrorMessages :messages="productServiceForm.errors.vat_rate" />
                    </div>

                    <div class="pb-4" v-if="productServiceForm.is_taxable">
                        <FormLabel
                            :class="{
                                'text-danger': productServiceForm.invalid('is_price_include_vat'),
                            }"
                        >
                            {{ t("views.product_service.fields.is_price_include_vat") }}
                        </FormLabel>
                        <FormSwitch class="mt-2">
                            <FormSwitch.Input
                                v-model="productServiceForm.is_price_include_vat"
                                type="checkbox"
                                :class="{
                                    'border-danger': productServiceForm.invalid(
                                        'is_price_include_vat'
                                    ),
                                }"
                                @change="productServiceForm.validate('is_price_include_vat')"
                            />
                        </FormSwitch>
                        <FormErrorMessages
                            :messages="productServiceForm.errors.is_price_include_vat"
                        />
                    </div>
                </div>
            </template>

            <!-- Card 4: Other Settings -->
            <template #card-items-3>
                <div class="p-5">
                    <div class="pb-4">
                        <FormLabel
                            :class="{ 'text-danger': productServiceForm.invalid('status') }"
                        >
                            {{ t("views.product_service.fields.status") }}
                        </FormLabel>
                        <FormSelect
                            v-model="productServiceForm.status"
                            :class="{ 'border-danger': productServiceForm.invalid('status') }"
                            @change="productServiceForm.validate('status')"
                        >
                            <option value="">{{ t("components.dropdown.placeholder") }}</option>
                            <option v-for="s in statusDDL" :key="s.code" :value="s.code">
                                {{ t(s.name) }}
                            </option>
                        </FormSelect>
                        <FormErrorMessages :messages="productServiceForm.errors.status" />
                    </div>

                    <div class="pb-4">
                        <FormLabel
                            :class="{ 'text-danger': productServiceForm.invalid('remarks') }"
                        >
                            {{ t("views.product_service.fields.remarks") }}
                        </FormLabel>
                        <FormTextarea
                            v-model="productServiceForm.remarks"
                            :class="{ 'border-danger': productServiceForm.invalid('remarks') }"
                            :placeholder="t('views.product_service.fields.remarks')"
                            @change="productServiceForm.validate('remarks')"
                        />
                        <FormErrorMessages :messages="productServiceForm.errors.remarks" />
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
                        <Lucide
                            v-if="productServiceForm.validating"
                            icon="Loader"
                            class="animate-spin"
                        />
                        <template v-else>
                            {{ t("components.buttons.submit") }}
                        </template>
                    </Button>
                    <Button
                        type="button"
                        href="#"
                        variant="soft-secondary"
                        class="w-28 shadow-md"
                        @click="resetForm"
                    >
                        {{ t("components.buttons.reset") }}
                    </Button>
                </div>
            </template>
        </TwoColumnsLayout>
    </form>
</template>