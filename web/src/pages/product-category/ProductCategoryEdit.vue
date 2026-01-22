<script setup lang="ts">
// #region Imports
import { computed, onMounted, ref, watch, watchEffect } from "vue";
import { useI18n } from "vue-i18n";
import {
    convertErrorTypeToAlertListType
} from "@/utils/helper";
import { useRoute, useRouter } from "vue-router";
import ProductCategoryService from "@/services/ProductCategoryService";
import DashboardService from "@/services/DashboardService";
import CacheService from "@/services/CacheService";
import { TwoColumnsLayout } from "@/components/Base/Form/FormLayout";
import {
    FormInput,
    FormLabel,
    FormSelect,
    FormInputCode,
    FormErrorMessages,
} from "@/components/Base/Form";
import { TwoColumnsLayoutCards } from "@/components/Base/Form/FormLayout/TwoColumnsLayout.vue";
import { CardState } from "@/types/enums/CardState";
import { ServiceResponse } from "@/types/services/ServiceResponse";
import { ViewMode } from "@/types/enums/ViewMode";
import Button from "@/components/Base/Button";
import { debounce } from "lodash";
import Lucide from "@/components/Base/Lucide";
import { ProductCategory } from "@/types/models/ProductCategory";
import { type AlertPlaceholderProps } from "@/components/AlertPlaceholder/AlertPlaceholder.vue";
import { useSelectedUserLocationStore } from "@/stores/selected-user-location";
import { ErrorCode } from "@/types/enums/ErrorCode";
import { DropDownOption } from "@/types/models/DropDownOption";
// #endregion

// #region Interfaces
// #endregion

// #region Declarations
const { t } = useI18n();
const route = useRoute();
const router = useRouter();
const selectedUserLocationStore = useSelectedUserLocationStore();

const productCategoryService = new ProductCategoryService();
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
        title: "views.product_category.field_groups.company_info",
        state: CardState.Expanded,
    },
    {
        title: "views.product_category.field_groups.product_category_data",
        state: CardState.Expanded,
    },
    { title: "", state: CardState.Hidden, id: "button" },
]);

const typeDDL = ref<Array<DropDownOption> | null>(null);

const productCategoryForm = productCategoryService.useProductCategoryEditForm(route.params.ulid as string);
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
    emits("mode-state", ViewMode.FORM_EDIT);
    if (!isUserLocationSelected.value) {
        router.push({
            name: "side-menu-error-code",
            params: { code: ErrorCode.USERLOCATION_REQUIRED },
        });
    }
    await getDDL();
    await loadData(route.params.ulid as string);
});
// #endregion

// #region Methods
const getDDL = async (): Promise<void> => {
    typeDDL.value = await productCategoryService.getTypes();
};

const loadData = async (ulid: string) => {
    emits("loading-state", true);
    let response: ServiceResponse<ProductCategory | null> =
        await productCategoryService.read(ulid);

    if (response && response.data) {
        productCategoryForm.setData({
            company_id: response.data.company.id,
            code: response.data.code,
            name: response.data.name,
            type: response.data.type,
        });
    }
    emits("loading-state", false);
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
    if (productCategoryForm.hasErrors) {
        scrollToError(Object.keys(productCategoryForm.errors)[0]);
    }
    emits("loading-state", true);
    await productCategoryForm
        .submit()
        .then(() => {
            emits("update-profile");
            router.push({ name: "side-menu-product-product-category-list" });
        })
        .catch((error) => {
            let errorList: Record<
                string,
                Array<string>
            > = convertErrorTypeToAlertListType(error as Error);
            showAlertPlaceholder("danger", "", errorList);
        })
        .finally(() => {
            emits("loading-state", false);
        });
};

const resetForm = async () => {
    productCategoryForm.reset();
    productCategoryForm.setErrors({});
    await loadData(route.params.ulid as string);
};

const setCode = () => {
    productCategoryForm.forgetError("code");
    if (productCategoryForm.code == "_AUTO_") {
        productCategoryForm.setData({ code: "" });
    } else {
        productCategoryForm.setData({ code: "_AUTO_" });
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
    productCategoryForm,
    debounce((newValue): void => {
        cacheServices.setLastEntity("PRODUCT_CATEGORY_EDIT", newValue.data());
    }, 500),
    { deep: true }
);
// #endregion
</script>

<template>
    <form id="productCategoryForm" @submit.prevent="onSubmit">
        <TwoColumnsLayout
            :cards="cards"
            :using-side-tab="false"
            @handle-expand-card="handleExpandCard"
        >
            <template #card-items-0>
                <div class="p-5">
                    <FormLabel>
                        {{ selectedUserLocation.company.code }}
                        <br />
                        {{ selectedUserLocation.company.name }}
                    </FormLabel>
                    <FormInput type="hidden" v-model="productCategoryForm.company_id" />
                </div>
            </template>
            <template #card-items-1>
                <div class="p-5">
                    <div class="pb-4">
                        <FormLabel
                            :class="{ 'text-danger': productCategoryForm.invalid('code') }"
                        >
                            {{ t("views.product_category.fields.code") }}
                        </FormLabel>
                        <FormInputCode
                            v-model="productCategoryForm.code"
                            :class="{ 'border-danger': productCategoryForm.invalid('code') }"
                            :placeholder="t('views.product_category.fields.code')"
                            @set-auto="setCode"
                            @change="productCategoryForm.validate('code')"
                        />
                        <FormErrorMessages :messages="productCategoryForm.errors.code" />
                    </div>
                    <div class="pb-4">
                        <FormLabel
                            :class="{ 'text-danger': productCategoryForm.invalid('name') }"
                        >
                            {{ t("views.product_category.fields.name") }}
                        </FormLabel>
                        <FormInput
                            v-model="productCategoryForm.name"
                            type="text"
                            :class="{ 'border-danger': productCategoryForm.invalid('name') }"
                            :placeholder="t('views.product_category.fields.name')"
                            @change="productCategoryForm.validate('name')"
                        />
                        <FormErrorMessages :messages="productCategoryForm.errors.name" />
                    </div>
                    <div class="pb-4">
                        <FormLabel
                            :class="{ 'text-danger': productCategoryForm.invalid('type') }"
                        >
                            {{ t("views.product_category.fields.type") }}
                        </FormLabel>
                        <FormSelect
                            v-model="productCategoryForm.type"
                            :class="{ 'border-danger': productCategoryForm.invalid('type') }"
                            @change="productCategoryForm.validate('type')"
                        >
                            <option value="">{{ t("components.dropdown.placeholder") }}</option>
                            <option v-for="c in typeDDL" :key="c.code" :value="c.code">
                                {{ t(c.name) }}
                            </option>
                        </FormSelect>
                        <FormErrorMessages :messages="productCategoryForm.errors.type" />
                    </div>
                </div>
            </template>

            <template #card-items-button>
                <div class="flex gap-4">
                    <Button
                        type="submit"
                        href="#"
                        variant="primary"
                        class="w-28 shadow-md"
                        :disabled="
                            productCategoryForm.validating || productCategoryForm.hasErrors
                        "
                    >
                        <Lucide
                            v-if="productCategoryForm.validating"
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


