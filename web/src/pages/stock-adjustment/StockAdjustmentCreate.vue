<script setup lang="ts">
import { computed, ref, onMounted } from "vue";
import { useI18n } from "vue-i18n";
import { useRouter } from "vue-router";
import { TwoColumnsLayout } from "@/components/Base/Form/FormLayout";
import { TwoColumnsLayoutCards } from "@/components/Base/Form/FormLayout/TwoColumnsLayout.vue";
import { CardState } from "@/types/enums/CardState";
import { useSelectedUserLocationStore } from "@/stores/selected-user-location";
import {
    FormInput,
    FormLabel,
    FormErrorMessages,
    FormInputCode,
    FormTextarea,
    FormTomSelect,
    FormSwitch,
} from "@/components/Base/Form";
import WarehouseService from "@/services/WarehouseService";
import Button from "@/components/Base/Button";
import Lucide from "@/components/Base/Lucide";
import { formatDate } from "@/utils/helper";
import StockAdjustmentService from "@/services/StockAdjustmentService";
import StockAdjustmentCategoryService from "@/services/StockAdjustmentCategoryService";
import { ErrorCode } from "@/types/enums/ErrorCode";
import { type DropDownOption } from "@/types/models/DropDownOption";
import { type StockAdjustmentInProductNestedStoreRequest } from "@/types/services/stock-adjustment/StockAdjustmentRequest";

const { t } = useI18n();
const router = useRouter();
const selectedUserLocationStore = useSelectedUserLocationStore();

const isUserLocationSelected = computed(
    () => selectedUserLocationStore.isUserLocationSelected
);
const selectedUserLocation = computed(
    () => selectedUserLocationStore.selectedUserLocation
);

const stockAdjustmentService = new StockAdjustmentService();
const stockAdjustmentForm = stockAdjustmentService.useStockAdjustmentCreateForm();
const warehouseService = new WarehouseService();
const stockAdjustmentCategoryService = new StockAdjustmentCategoryService();

const dateTimeDisplay = ref<string>("");
const inWarehouseDDL = ref<Array<DropDownOption> | null>(null);
const outWarehouseDDL = ref<Array<DropDownOption> | null>(null);
const categoryDDL = ref<Array<DropDownOption> | null>(null);

const cards = ref<Array<TwoColumnsLayoutCards>>([
    {
        title: "views.stock_adjustment.field_groups.company_info",
        state: CardState.Expanded,
    },
    {
        title: "views.stock_adjustment.field_groups.stock_adjustment_data",
        state: CardState.Expanded,
    },
    {
        title: "views.stock_adjustment.field_groups.in_products",
        state: CardState.Collapsed,
    },
    { title: "", state: CardState.Hidden, id: "button" },
]);

const handleExpandCard = (index: number) => {
    if (cards.value[index].state === CardState.Collapsed) {
        cards.value[index].state = CardState.Expanded;
    } else if (cards.value[index].state === CardState.Expanded) {
        cards.value[index].state = CardState.Collapsed;
    }
};

const setCode = () => {
    stockAdjustmentForm.forgetError("code");
    if (stockAdjustmentForm.code === "_AUTO_") {
        stockAdjustmentForm.setData({ code: "" });
    } else {
        stockAdjustmentForm.setData({ code: "_AUTO_" });
    }
};

const scrollToError = (id: string): void => {
    const el = document.getElementById(id);
    if (!el) return;
    el.scrollIntoView({ behavior: "smooth", block: "center" });
};

onMounted(() => {
    if (!isUserLocationSelected.value) {
        router.push({
            name: "side-menu-error-code",
            params: { code: ErrorCode.USERLOCATION_REQUIRED },
        });
        return;
    }

    const now = new Date().toString();
    dateTimeDisplay.value = formatDate(now, "YYYY-MM-DDTHH:mm");
    stockAdjustmentForm.setData({
        company_id: selectedUserLocation.value.company.id,
        branch_id: selectedUserLocation.value.branch.id,
        date: formatDate(now, "YYYY-MM-DD HH:mm:ss"),
    });

    loadCategoryDDL();
    loadInWarehouseDDL();
    loadOutWarehouseDDL();
});

const handleDateTimeChange = () => {
    const value = dateTimeDisplay.value;
    if (!value) {
        stockAdjustmentForm.setData({ date: "" });
        stockAdjustmentForm.validate("date");
        return;
    }

    const [datePart, timePartRaw] = value.split("T");
    const timePart = timePartRaw ?? "";
    const normalized = `${datePart} ${timePart}:00`;

    stockAdjustmentForm.setData({ date: normalized });
    stockAdjustmentForm.forgetError("date");
    stockAdjustmentForm.validate("date");
};

const loadInWarehouseDDL = async (search = "") => {
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

const loadOutWarehouseDDL = async (search = "") => {
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

const loadCategoryDDL = async (search = "") => {
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

const clearCategory = () => {
    stockAdjustmentForm.setData({ category_id: "" });
    loadCategoryDDL("");
    stockAdjustmentForm.forgetError("category_id");
    stockAdjustmentForm.validate("category_id");
};

const clearInWarehouse = () => {
    stockAdjustmentForm.setData({ in_warehouse_id: "" });
    loadInWarehouseDDL("");
    stockAdjustmentForm.forgetError("in_warehouse_id");
    stockAdjustmentForm.validate("in_warehouse_id");
};

const clearOutWarehouse = () => {
    stockAdjustmentForm.setData({ out_warehouse_id: "" });
    loadOutWarehouseDDL("");
    stockAdjustmentForm.forgetError("out_warehouse_id");
    stockAdjustmentForm.validate("out_warehouse_id");
};

const resetForm = () => {
    stockAdjustmentForm.reset();
    stockAdjustmentForm.setErrors({});
    dateTimeDisplay.value = "";
};

const addInProduct = () => {
    const item: StockAdjustmentInProductNestedStoreRequest = {
        qty: 0,
        product_unit_id: "",
        product_unit_conversion_value: 1,
        product_unit_cogs: 0,
        remarks: "",
    };

    stockAdjustmentForm.in_products.push(item);
};

const removeInProduct = (index: number) => {
    stockAdjustmentForm.in_products.splice(index, 1);
};

const onSubmit = async () => {
    if (stockAdjustmentForm.hasErrors) {
        const firstErrorKey = Object.keys(stockAdjustmentForm.errors)[0];
        if (firstErrorKey) {
            scrollToError(firstErrorKey);
        }
        return;
    }

    try {
        await stockAdjustmentForm.submit();
        resetForm();
    } catch (error) {
        console.error(error);
    }
};
</script>

<template>
    <form id="stockAdjustmentForm" @submit.prevent="onSubmit">
        <TwoColumnsLayout :cards="cards" :using-side-tab="false" @handle-expand-card="handleExpandCard">
            <template #card-items-0>
                <div class="p-5">
                    <FormLabel>
                        {{ selectedUserLocation.company.code }}
                        <br />
                        {{ selectedUserLocation.company.name }}
                    </FormLabel>
                    <FormInput type="hidden" v-model="stockAdjustmentForm.company_id" />

                    <FormLabel class="mt-5">
                        {{ selectedUserLocation.branch.code }}
                        <br />
                        {{ selectedUserLocation.branch.name }}
                    </FormLabel>
                    <FormInput type="hidden" v-model="stockAdjustmentForm.branch_id" />
                </div>
            </template>
            <template #card-items-1>
                <div class="p-5">
                    <div class="grid grid-cols-12 gap-4 gap-y-3">
                        <!-- code -->
                        <div class="col-span-12 lg:col-span-4 md:col-span-6">
                            <FormLabel :class="{ 'text-danger': stockAdjustmentForm.invalid('code') }">
                                {{ t("views.stock_adjustment.fields.code") }}
                            </FormLabel>
                            <FormInputCode v-model="stockAdjustmentForm.code"
                                :class="{ 'border-danger': stockAdjustmentForm.invalid('code') }"
                                :placeholder="t('views.stock_adjustment.fields.code')" @set-auto="setCode"
                                @change="stockAdjustmentForm.validate('code')" />
                            <FormErrorMessages :messages="stockAdjustmentForm.errors.code" />
                        </div>
                        <!-- date -->
                        <div class="col-span-12 lg:col-span-4 md:col-span-6">
                            <FormLabel :class="{ 'text-danger': stockAdjustmentForm.invalid('date') }">
                                {{ t("views.stock_adjustment.fields.date") }}
                            </FormLabel>
                            <FormInput v-model="dateTimeDisplay" type="datetime-local"
                                :class="{ 'border-danger': stockAdjustmentForm.invalid('date') }"
                                :placeholder="t('views.stock_adjustment.fields.date')" @change="handleDateTimeChange" />
                            <FormErrorMessages :messages="stockAdjustmentForm.errors.date" />
                        </div>
                        <!-- category -->
                        <div class="col-span-12 lg:col-span-4">
                            <FormLabel :class="{ 'text-danger': stockAdjustmentForm.invalid('category_id') }">
                                {{ t("views.stock_adjustment.fields.category_id") }}
                            </FormLabel>
                            <div class="flex items-center gap-2">
                                <div class="flex-1">
                                    <FormTomSelect v-model="stockAdjustmentForm.category_id"
                                        :class="{ 'border-danger': stockAdjustmentForm.invalid('category_id') }"
                                        @change="stockAdjustmentForm.validate('category_id')" @search="loadCategoryDDL"
                                        :options="{ placeholder: t('components.dropdown.placeholder') }">
                                        <option v-for="c in categoryDDL" :key="c.code" :value="c.code">
                                            {{ c.name }}
                                        </option>
                                    </FormTomSelect>
                                </div>
                                <button v-if="stockAdjustmentForm.category_id" type="button"
                                    class="text-slate-500 hover:text-danger" @click="clearCategory">
                                    <Lucide icon="X" class="w-4 h-4" />
                                </button>
                            </div>
                            <FormErrorMessages :messages="stockAdjustmentForm.errors.category_id" />
                        </div>
                        <!-- in_warehouse -->
                        <div class="col-span-12 lg:col-span-6">
                            <FormLabel :class="{ 'text-danger': stockAdjustmentForm.invalid('in_warehouse_id') }">
                                {{ t("views.stock_adjustment.fields.in_warehouse_id") }}
                            </FormLabel>
                            <div class="flex items-center gap-2">
                                <div class="flex-1">
                                    <FormTomSelect v-model="stockAdjustmentForm.in_warehouse_id"
                                        :class="{ 'border-danger': stockAdjustmentForm.invalid('in_warehouse_id') }"
                                        @change="stockAdjustmentForm.validate('in_warehouse_id')"
                                        @search="loadInWarehouseDDL"
                                        :options="{ placeholder: t('components.dropdown.placeholder') }">
                                        <option v-for="w in inWarehouseDDL" :key="w.code" :value="w.code">
                                            {{ w.name }}
                                        </option>
                                    </FormTomSelect>
                                </div>
                                <button v-if="stockAdjustmentForm.in_warehouse_id" type="button"
                                    class="text-slate-500 hover:text-danger" @click="clearInWarehouse">
                                    <Lucide icon="X" class="w-4 h-4" />
                                </button>
                            </div>
                            <FormErrorMessages :messages="stockAdjustmentForm.errors.in_warehouse_id" />
                        </div>
                        <!-- out_warehouse -->
                        <div class="col-span-12 lg:col-span-6">
                            <FormLabel :class="{ 'text-danger': stockAdjustmentForm.invalid('out_warehouse_id') }">
                                {{ t("views.stock_adjustment.fields.out_warehouse_id") }}
                            </FormLabel>
                            <div class="flex items-center gap-2">
                                <div class="flex-1">
                                    <FormTomSelect v-model="stockAdjustmentForm.out_warehouse_id"
                                        :class="{ 'border-danger': stockAdjustmentForm.invalid('out_warehouse_id') }"
                                        @change="stockAdjustmentForm.validate('out_warehouse_id')"
                                        @search="loadOutWarehouseDDL"
                                        :options="{ placeholder: t('components.dropdown.placeholder') }">
                                        <option v-for="w in outWarehouseDDL" :key="w.code" :value="w.code">
                                            {{ w.name }}
                                        </option>
                                    </FormTomSelect>
                                </div>
                                <button v-if="stockAdjustmentForm.out_warehouse_id" type="button"
                                    class="text-slate-500 hover:text-danger" @click="clearOutWarehouse">
                                    <Lucide icon="X" class="w-4 h-4" />
                                </button>
                            </div>
                            <FormErrorMessages :messages="stockAdjustmentForm.errors.out_warehouse_id" />
                        </div>
                        <!-- remarks -->
                        <div class="col-span-12">
                            <FormLabel :class="{ 'text-danger': stockAdjustmentForm.invalid('remarks') }">
                                {{ t("views.stock_adjustment.fields.remarks") }}
                            </FormLabel>
                            <FormTextarea v-model="stockAdjustmentForm.remarks" rows="3"
                                :class="{ 'border-danger': stockAdjustmentForm.invalid('remarks') }"
                                :placeholder="t('views.stock_adjustment.fields.remarks')"
                                @change="stockAdjustmentForm.validate('remarks')" />
                            <FormErrorMessages :messages="stockAdjustmentForm.errors.remarks" />
                        </div>
                        <!-- is_posted -->
                        <div class="col-span-12">
                            <FormLabel class="pr-5">
                                {{ t("views.stock_adjustment.fields.is_posted") }}
                            </FormLabel>
                            <FormSwitch>
                                <FormSwitch.Input v-model="stockAdjustmentForm.is_posted" type="checkbox" />
                            </FormSwitch>
                        </div>
                    </div>
                </div>
            </template>
            <template #card-items-2>
                <div class="p-5">
                    <div class="flex items-center justify-between mb-4">
                        <FormLabel>
                            {{ t("views.stock_adjustment.field_groups.in_products") }}
                        </FormLabel>
                        <Button type="button" variant="primary" class="shadow-md" @click="addInProduct">
                            {{ t("views.stock_adjustment_in_product.actions.create") }}
                        </Button>
                    </div>

                    <div v-if="stockAdjustmentForm.in_products.length === 0" class="text-slate-500 text-sm">
                        {{ t("components.data-list.data_not_found") }}
                    </div>

                    <div v-else class="space-y-5">
                        <div v-for="(item, index) in stockAdjustmentForm.in_products" :key="index"
                            class="border border-slate-200/60 dark:border-darkmode-400 rounded-md p-4">
                            <div class="flex items-center justify-between mb-3">
                                <div class="font-medium text-sm">
                                    {{ t("views.stock_adjustment_in_product.page_title") }} #{{ index + 1 }}
                                </div>
                                <Button type="button" variant="outline-secondary" class="px-2 py-1"
                                    @click="removeInProduct(index)">
                                    <Lucide icon="Trash2" class="w-4 h-4 text-danger" />
                                </Button>
                            </div>

                            <div class="grid grid-cols-12 gap-4 gap-y-3">
                                <div class="col-span-12 lg:col-span-3">
                                    <FormLabel
                                        :class="{ 'text-danger': stockAdjustmentForm.invalid(`in_products.${index}.qty` as any) }">
                                        {{ t("views.stock_adjustment_in_product.fields.qty") }}
                                    </FormLabel>
                                    <FormInput type="number" min="0"
                                        v-model.number="stockAdjustmentForm.in_products[index].qty"
                                        :class="{ 'border-danger': stockAdjustmentForm.invalid(`in_products.${index}.qty` as any) }"
                                        @change="stockAdjustmentForm.validate(`in_products.${index}.qty` as any)" />
                                    <FormErrorMessages
                                        :messages="(stockAdjustmentForm.errors as any)[`in_products.${index}.qty`]" />
                                </div>

                                <div class="col-span-12 lg:col-span-3">
                                    <FormLabel
                                        :class="{ 'text-danger': stockAdjustmentForm.invalid(`in_products.${index}.product_unit_id` as any) }">
                                        {{ t("views.stock_adjustment_in_product.fields.product_unit_id") }}
                                    </FormLabel>
                                    <FormInput
                                        v-model="stockAdjustmentForm.in_products[index].product_unit_id"
                                        :class="{ 'border-danger': stockAdjustmentForm.invalid(`in_products.${index}.product_unit_id` as any) }"
                                        @change="stockAdjustmentForm.validate(`in_products.${index}.product_unit_id` as any)" />
                                    <FormErrorMessages
                                        :messages="(stockAdjustmentForm.errors as any)[`in_products.${index}.product_unit_id`]" />
                                </div>

                                <div class="col-span-12 lg:col-span-3">
                                    <FormLabel
                                        :class="{ 'text-danger': stockAdjustmentForm.invalid(`in_products.${index}.product_unit_conversion_value` as any) }">
                                        {{ t("views.stock_adjustment_in_product.fields.product_unit_conversion_value") }}
                                    </FormLabel>
                                    <FormInput type="number" min="0" step="0.0001"
                                        v-model.number="stockAdjustmentForm.in_products[index].product_unit_conversion_value"
                                        :class="{ 'border-danger': stockAdjustmentForm.invalid(`in_products.${index}.product_unit_conversion_value` as any) }"
                                        @change="stockAdjustmentForm.validate(`in_products.${index}.product_unit_conversion_value` as any)" />
                                    <FormErrorMessages
                                        :messages="(stockAdjustmentForm.errors as any)[`in_products.${index}.product_unit_conversion_value`]" />
                                </div>

                                <div class="col-span-12 lg:col-span-3">
                                    <FormLabel
                                        :class="{ 'text-danger': stockAdjustmentForm.invalid(`in_products.${index}.product_unit_cogs` as any) }">
                                        {{ t("views.stock_adjustment_in_product.fields.product_unit_cogs") }}
                                    </FormLabel>
                                    <FormInput type="number" min="0" step="0.01"
                                        v-model.number="stockAdjustmentForm.in_products[index].product_unit_cogs"
                                        :class="{ 'border-danger': stockAdjustmentForm.invalid(`in_products.${index}.product_unit_cogs` as any) }"
                                        @change="stockAdjustmentForm.validate(`in_products.${index}.product_unit_cogs` as any)" />
                                    <FormErrorMessages
                                        :messages="(stockAdjustmentForm.errors as any)[`in_products.${index}.product_unit_cogs`]" />
                                </div>

                                <div class="col-span-12">
                                    <FormLabel
                                        :class="{ 'text-danger': stockAdjustmentForm.invalid(`in_products.${index}.remarks` as any) }">
                                        {{ t("views.stock_adjustment_in_product.fields.remarks") }}
                                    </FormLabel>
                                    <FormTextarea rows="2"
                                        v-model="stockAdjustmentForm.in_products[index].remarks"
                                        :class="{ 'border-danger': stockAdjustmentForm.invalid(`in_products.${index}.remarks` as any) }"
                                        @change="stockAdjustmentForm.validate(`in_products.${index}.remarks` as any)" />
                                    <FormErrorMessages
                                        :messages="(stockAdjustmentForm.errors as any)[`in_products.${index}.remarks`]" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <template #card-items-button>
                <div class="flex gap-4 p-5">
                    <Button type="submit" href="#" variant="primary" class="w-28 shadow-md"
                        :disabled="stockAdjustmentForm.validating || stockAdjustmentForm.hasErrors">
                        <Lucide v-if="stockAdjustmentForm.validating" icon="Loader" class="animate-spin" />
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
