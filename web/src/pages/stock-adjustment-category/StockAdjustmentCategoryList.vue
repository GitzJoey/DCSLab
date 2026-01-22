<script setup lang="ts">
import { computed, onMounted, ref } from "vue";
import DataList from "@/components/DataList";
import { useI18n } from "vue-i18n";
import Button from "@/components/Base/Button";
import Lucide from "@/components/Base/Lucide";
import Table from "@/components/Base/Table";
import StockAdjustmentCategoryService from "@/services/StockAdjustmentCategoryService";
import { StockAdjustmentCategory } from "@/types/models/StockAdjustmentCategory";
import { Collection } from "@/types/resources/Collection";
import { DataListEmittedData } from "@/components/DataList/DataList.vue";
import { ServiceResponse } from "@/types/services/ServiceResponse";
import { StockAdjustmentCategoryReadAnyPaginateRequest } from "@/types/services/stock-adjustment-category/StockAdjustmentCategoryRequest";
import { useRouter } from "vue-router";
import { Dialog } from "@/components/Base/Headless";
import { ViewMode } from "@/types/enums/ViewMode";
import { useSelectedUserLocationStore } from "@/stores/selected-user-location";
import { ErrorCode } from "@/types/enums/ErrorCode";
import { NotificationData } from "@/types/models/NotificationData";
import type { AlertPlaceholderProps } from "@/components/AlertPlaceholder/AlertPlaceholder.vue";

const { t } = useI18n();
const router = useRouter();
const stockAdjustmentCategoryService = new StockAdjustmentCategoryService();
const selectedUserLocationStore = useSelectedUserLocationStore();

const emits = defineEmits([
    "mode-state",
    "loading-state",
    "update-profile",
    "show-alertplaceholder",
    "show-notification",
]);

const deleteUlid = ref<string>("");
const deleteModalShow = ref<boolean>(false);
const expandDetail = ref<number | null>(null);
const stockAdjustmentCategoryLists = ref<Collection<Array<StockAdjustmentCategory>> | null>({
    data: [],
    meta: {
        current_page: 0,
        from: null,
        last_page: 0,
        path: "",
        per_page: 0,
        to: null,
        total: 0,
    },
    links: {
        first: "",
        last: "",
        prev: null,
        next: null,
    },
});

const isUserLocationSelected = computed(
    () => selectedUserLocationStore.isUserLocationSelected
);
const selectedUserLocation = computed(
    () => selectedUserLocationStore.selectedUserLocation
);

onMounted(async () => {
    emits("mode-state", ViewMode.LIST);

    if (!isUserLocationSelected.value) {
        router.push({
            name: "side-menu-error-code",
            params: { code: ErrorCode.USERLOCATION_REQUIRED },
        });
    }

    await getStockAdjustmentCategories("", true, 1, 10);
});

const getStockAdjustmentCategories = async (
    search: string,
    refresh: boolean,
    page: number,
    per_page: number
) => {
    emits("loading-state", true);

    const company_id = selectedUserLocation.value.company.id;

    const searchReq: StockAdjustmentCategoryReadAnyPaginateRequest = {
        with_trashed: false,

        company_id: company_id,
        search: search,
        include_id: undefined,

        refresh: refresh,
        page: page,
        per_page: per_page,
    };

    const result: ServiceResponse<Collection<Array<StockAdjustmentCategory>> | null> =
        await stockAdjustmentCategoryService.readAnyPaginate(searchReq);

    if (result.success && result.data) {
        stockAdjustmentCategoryLists.value = result.data;
    } else {
        showAlertPlaceholder(
            "danger",
            "",
            result.errors as Record<string, Array<string>>
        );
    }

    emits("loading-state", false);
};

const handleDataListChange = async (data: DataListEmittedData) => {
    await getStockAdjustmentCategories(
        data.search.text,
        false,
        data.pagination.page,
        data.pagination.per_page
    );
};

const viewSelected = (idx: number) => {
    if (expandDetail.value === idx) {
        expandDetail.value = null;
    } else {
        expandDetail.value = idx;
    }
};

const editSelected = (idx: number) => {
    if (!stockAdjustmentCategoryLists.value) return;
    const ulid = stockAdjustmentCategoryLists.value.data[idx].ulid;
    emits("mode-state", ViewMode.FORM_EDIT);
    router.push({
        name: "side-menu-stock-adjustment-stock-adjustment-category-edit",
        params: { ulid: ulid },
    });
};

const deleteSelected = (idx: number) => {
    if (!stockAdjustmentCategoryLists.value) return;
    const ulid = stockAdjustmentCategoryLists.value.data[idx].ulid;
    deleteUlid.value = ulid;
    deleteModalShow.value = true;
};

const confirmDelete = async () => {
    deleteModalShow.value = false;
    emits("loading-state", true);

    const result = await stockAdjustmentCategoryService.delete(deleteUlid.value);

    emits("loading-state", false);

    if (result.success) {
        await getStockAdjustmentCategories("", true, 1, 10);
        showNotification(
            t("views.stock_adjustment_category.alert.delete_stock_adjustment_category.title"),
            t("views.stock_adjustment_category.alert.delete_stock_adjustment_category.content")
        );
    } else {
        showAlertPlaceholder(
            "danger",
            "",
            result.errors as Record<string, Array<string>>
        );
    }
};

const showNotification = (pTitle: string, pContent: string) => {
    const n: NotificationData = {
        title: pTitle,
        content: pContent,
    };
    emits("show-notification", n);
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
</script>

<template>
    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="col-span-12 intro-y lg:col-span-12">
            <DataList
                :title="t('views.stock_adjustment_category.table.title')"
                :data="stockAdjustmentCategoryLists"
                :enable-search="true"
                :can-print="true"
                :can-export="true"
                :pagination="stockAdjustmentCategoryLists ? stockAdjustmentCategoryLists.meta : null"
                @dataListChanged="handleDataListChange"
            >
                <template #content>
                    <Table class="mt-5" :hover="true">
                        <Table.Thead variant="light">
                            <Table.Tr>
                                <Table.Th class="whitespace-nowrap">
                                    {{ t("views.stock_adjustment_category.fields.code") }}
                                </Table.Th>
                                <Table.Th class="whitespace-nowrap">
                                    {{ t("views.stock_adjustment_category.fields.name") }}
                                </Table.Th>
                                <Table.Th class="whitespace-nowrap"></Table.Th>
                            </Table.Tr>
                        </Table.Thead>
                        <Table.Tbody v-if="stockAdjustmentCategoryLists !== null">
                            <template v-if="stockAdjustmentCategoryLists.data.length === 0">
                                <Table.Tr class="intro-x">
                                    <Table.Td colspan="3">
                                        <div class="flex justify-center italic">
                                            {{ t("components.data-list.data_not_found") }}
                                        </div>
                                    </Table.Td>
                                </Table.Tr>
                            </template>
                            <template
                                v-for="(item, itemIdx) in stockAdjustmentCategoryLists.data"
                                :key="item.ulid"
                            >
                                <Table.Tr class="intro-x">
                                    <Table.Td>
                                        <div class="font-medium whitespace-nowrap">
                                            {{ item.code }}
                                        </div>
                                    </Table.Td>
                                    <Table.Td>
                                        <div class="font-medium">
                                            {{ item.name }}
                                        </div>
                                    </Table.Td>
                                    <Table.Td>
                                        <div class="flex justify-end gap-1">
                                            <Button
                                                variant="outline-secondary"
                                                @click="viewSelected(itemIdx)"
                                            >
                                                <Lucide icon="Info" class="w-4 h-4" />
                                            </Button>
                                            <Button
                                                variant="outline-secondary"
                                                @click="editSelected(itemIdx)"
                                            >
                                                <Lucide icon="Pen" class="w-4 h-4" />
                                            </Button>
                                            <Button
                                                variant="outline-secondary"
                                                @click="deleteSelected(itemIdx)"
                                            >
                                                <Lucide
                                                    icon="Trash2"
                                                    class="w-4 h-4 text-danger"
                                                />
                                            </Button>
                                        </div>
                                    </Table.Td>
                                </Table.Tr>
                                <Table.Tr
                                    :class="{
                                        'intro-x': true,
                                        'hidden transition-all': expandDetail !== itemIdx,
                                    }"
                                >
                                    <Table.Td colspan="3" class="p-5">
                                        <div class="grid grid-cols-12 gap-6">
                                            <div class="col-span-12">
                                                <div class="font-medium text-base mb-3 border-b pb-2">
                                                    {{ t("views.stock_adjustment_category.page_title") }}
                                                </div>
                                                <div class="grid grid-cols-1 gap-y-2">
                                                    <div class="flex flex-row">
                                                        <div class="w-48 text-slate-500">
                                                            {{ t("views.stock_adjustment_category.fields.code") }}
                                                        </div>
                                                        <div class="flex-1 font-medium">
                                                            {{ item.code }}
                                                        </div>
                                                    </div>
                                                    <div class="flex flex-row">
                                                        <div class="w-48 text-slate-500">
                                                            {{ t("views.stock_adjustment_category.fields.name") }}
                                                        </div>
                                                        <div class="flex-1 font-medium">
                                                            {{ item.name }}
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
    <Dialog
        :open="deleteModalShow"
        @close="
            () => {
                deleteModalShow = false;
            }
        "
    >
        <Dialog.Panel>
            <div class="p-5 text-center">
                <Lucide icon="XCircle" class="w-16 h-16 mx-auto mt-3 text-danger" />
                <div class="mt-5 text-3xl">
                    {{ t("components.delete-modal.title") }}
                </div>
                <div class="mt-2 text-slate-500">
                    {{ t("components.delete-modal.desc_1") }}
                    <br />
                    {{ t("components.delete-modal.desc_2") }}
                </div>
            </div>
            <div class="px-5 pb-8 text-center">
                <Button
                    type="button"
                    variant="outline-secondary"
                    @click="
                        () => {
                            deleteModalShow = false;
                        }
                    "
                    class="w-24 mr-1"
                >
                    {{ t("components.buttons.cancel") }}
                </Button>
                <Button
                    type="button"
                    variant="danger"
                    class="w-24"
                    @click="confirmDelete"
                >
                    {{ t("components.buttons.delete") }}
                </Button>
            </div>
        </Dialog.Panel>
    </Dialog>
</template>
