<script setup lang="ts">
import { computed, onMounted, ref, watch } from "vue";
import { useI18n } from "vue-i18n";
import {
    convertErrorTypeToAlertListType
} from "@/utils/helper";
import { isAxiosError, AxiosError } from "axios";
import StockAdjustmentCategoryService from "@/services/StockAdjustmentCategoryService";
import CacheService from "@/services/CacheService";
import { TwoColumnsLayout } from "@/components/Base/Form/FormLayout";
import {
    FormInput,
    FormLabel,
    FormErrorMessages,
    FormTextarea,
    FormInputCode,
} from "@/components/Base/Form";
import { TwoColumnsLayoutCards } from "@/components/Base/Form/FormLayout/TwoColumnsLayout.vue";
import { CardState } from "@/types/enums/CardState";
import Button from "@/components/Base/Button";
import { ViewMode } from "@/types/enums/ViewMode";
import { debounce } from "lodash";
import Lucide from "@/components/Base/Lucide";
import { useSelectedUserLocationStore } from "@/stores/selected-user-location";
import { useRouter } from "vue-router";
import type { AlertPlaceholderProps } from "@/components/AlertPlaceholder/AlertPlaceholder.vue";
import { ErrorCode } from "@/types/enums/ErrorCode";

const { t } = useI18n();
const router = useRouter();
const selectedUserLocationStore = useSelectedUserLocationStore();

const stockAdjustmentCategoryService = new StockAdjustmentCategoryService();
const cacheServices = new CacheService();

const emits = defineEmits([
    "mode-state",
    "loading-state",
    "update-profile",
    "show-alertplaceholder",
]);

const cards = ref<Array<TwoColumnsLayoutCards>>([
    {
        title: "views.stock_adjustment_category.field_groups.company_info",
        state: CardState.Expanded,
    },
    {
        title: "views.stock_adjustment_category.field_groups.stock_adjustment_category_data",
        state: CardState.Expanded,
    },
    { title: "", state: CardState.Hidden, id: "button" },
]);

const stockAdjustmentCategoryForm = stockAdjustmentCategoryService.useStockAdjustmentCategoryCreateForm();

const isUserLocationSelected = computed(
    () => selectedUserLocationStore.isUserLocationSelected
);
const selectedUserLocation = computed(
    () => selectedUserLocationStore.selectedUserLocation
);

onMounted(async () => {
    emits("mode-state", ViewMode.FORM_CREATE);
    loadFromCache();
    if (!isUserLocationSelected.value) {
        router.push({
            name: "side-menu-error-code",
            params: { code: ErrorCode.USERLOCATION_REQUIRED },
        });
    }

    setCompanyIdData();
});

const setCompanyIdData = () => {
    stockAdjustmentCategoryForm.setData({
        company_id: selectedUserLocation.value.company.id,
    });
};

const loadFromCache = () => {
    const data = cacheServices.getLastEntity("STOCK_ADJUSTMENT_CATEGORY_CREATE") as Record<
        string,
        unknown
    >;
    if (!data) return;
    stockAdjustmentCategoryForm.setData(data);
};

const handleExpandCard = (index: number) => {
    if (cards.value[index].state === CardState.Collapsed) {
        cards.value[index].state = CardState.Expanded;
    } else if (cards.value[index].state === CardState.Expanded) {
        cards.value[index].state = CardState.Collapsed;
    }
};

const scrollToError = (id: string): void => {
    const el = document.getElementById(id);
    if (!el) return;
    el.scrollIntoView({ behavior: "smooth", block: "center" });
};

const onSubmit = async () => {
    if (stockAdjustmentCategoryForm.hasErrors) {
        scrollToError(Object.keys(stockAdjustmentCategoryForm.errors)[0]);
    }
    emits("loading-state", true);
    await stockAdjustmentCategoryForm
        .submit()
        .then(() => {
            resetForm();
            emits("update-profile");
            router.push({ name: "side-menu-stock-adjustment-stock-adjustment-category-list" });
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
    stockAdjustmentCategoryForm.reset();
    stockAdjustmentCategoryForm.setErrors({});
};

const setCode = () => {
    stockAdjustmentCategoryForm.forgetError("code");
    if (stockAdjustmentCategoryForm.code == "_AUTO_") {
        stockAdjustmentCategoryForm.setData({ code: "" });
    } else {
        stockAdjustmentCategoryForm.setData({ code: "_AUTO_" });
    }
};

const showAlertPlaceholder = (
    pAlertType: "hidden" | "danger" | "success" | "warning" | "pending" | "dark",
    pTitle: string,
    pAlertList: Record<string, Array<string>> | null
) => {
    const ap: AlertPlaceholderProps = {
        alertType: pAlertType,
        title: pTitle,
        alertList: pAlertList,
    };
    emits("show-alertplaceholder", ap);
};

watch(
    stockAdjustmentCategoryForm,
    debounce((newValue): void => {
        cacheServices.setLastEntity("STOCK_ADJUSTMENT_CATEGORY_CREATE", newValue.data());
    }, 500),
    { deep: true }
);
</script>

<template>
    <form id="stockAdjustmentCategoryForm" @submit.prevent="onSubmit">
        <TwoColumnsLayout :cards="cards" :using-side-tab="false" @handle-expand-card="handleExpandCard">
            <template #card-items-0>
                <div class="p-5">
                    <FormLabel>
                        {{ selectedUserLocation.company.code }}
                        <br />
                        {{ selectedUserLocation.company.name }}
                    </FormLabel>
                    <FormInput type="hidden" v-model="stockAdjustmentCategoryForm.company_id" />
                </div>
            </template>

            <template #card-items-1>
                <div class="p-5">
                    <div class="grid grid-cols-12 gap-4 gap-y-3">
                        <div class="col-span-12 sm:col-span-6">
                            <FormLabel :class="{ 'text-danger': stockAdjustmentCategoryForm.invalid('code') }">
                                {{ t("views.stock_adjustment_category.fields.code") }}
                            </FormLabel>
                            <FormInputCode
                                v-model="stockAdjustmentCategoryForm.code"
                                :class="{ 'border-danger': stockAdjustmentCategoryForm.invalid('code') }"
                                :placeholder="t('views.stock_adjustment_category.fields.code')"
                                @set-auto="setCode"
                                @change="stockAdjustmentCategoryForm.validate('code')"
                            />
                            <FormErrorMessages :messages="stockAdjustmentCategoryForm.errors.code" />
                        </div>

                        <div class="col-span-12 sm:col-span-6">
                            <FormLabel :class="{ 'text-danger': stockAdjustmentCategoryForm.invalid('name') }">
                                {{ t("views.stock_adjustment_category.fields.name") }}
                            </FormLabel>
                            <FormInput
                                v-model="stockAdjustmentCategoryForm.name"
                                type="text"
                                :class="{ 'border-danger': stockAdjustmentCategoryForm.invalid('name') }"
                                :placeholder="t('views.stock_adjustment_category.fields.name')"
                                @change="stockAdjustmentCategoryForm.validate('name')"
                            />
                            <FormErrorMessages :messages="stockAdjustmentCategoryForm.errors.name" />
                        </div>

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
                        :disabled="stockAdjustmentCategoryForm.validating || stockAdjustmentCategoryForm.hasErrors"
                    >
                        <Lucide v-if="stockAdjustmentCategoryForm.validating" icon="Loader" class="animate-spin" />
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
