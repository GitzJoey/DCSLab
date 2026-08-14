<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { DataListFlex } from '@/components/DataList';
import Button from '@/components/Base/Button';
import Lucide from '@/components/Base/Lucide';
import { TitleLayout } from '@/components/Base/Form/FormLayout';
import LoadingOverlay from '@/components/LoadingOverlay';
import AlertPlaceholder from '@/components/AlertPlaceholder/AlertPlaceholder.vue';
import StockTransferItemSerialService from '@/services/StockTransferItemSerialService';
import WarehouseService from '@/services/WarehouseService';
import ProductCategoryService from '@/services/ProductCategoryService';
import BrandService from '@/services/BrandService';
import { StockTransferItemSerial } from '@/types/models/StockTransferItemSerial';
import { Collection } from '@/types/resources/Collection';
import { DataListEmittedData } from '@/components/DataList/DataList.vue';
import { ServiceResponse } from '@/types/services/ServiceResponse';
import { StockTransferItemSerialReadAnyPaginateRequest } from '@/types/services/stock-transfer-item-serial/StockTransferItemSerialRequest';
import { useRouter } from 'vue-router';
import { ViewMode } from '@/types/enums/ViewMode';
import { useSelectedUserLocationStore } from '@/stores/selected-user-location';
import { ErrorCode } from '@/types/enums/ErrorCode';
import { formatDate } from '@/utils/helper';
import type { AlertPlaceholderProps } from '@/components/AlertPlaceholder/AlertPlaceholder.vue';
import ProductImagePreview from '@/components/Product/ProductImagePreview.vue';
import { FormInput, FormInputDateTime, FormLabel, FormSelectSearch } from '@/components/Base/Form';
import { DropDownOption } from '@/types/models/DropDownOption';

const { t } = useI18n();
const router = useRouter();
const stockTransferItemSerialService = new StockTransferItemSerialService();
const warehouseService = new WarehouseService();
const productCategoryService = new ProductCategoryService();
const brandService = new BrandService();
const selectedUserLocationStore = useSelectedUserLocationStore();

const emits = defineEmits([
    'mode-state',
    'loading-state',
    'update-profile',
    'show-alertplaceholder',
    'show-notification',
]);

const loading = ref<boolean>(false);
const alertType = ref<'danger' | 'success' | 'warning' | 'pending' | 'dark' | 'hidden'>('hidden');
const title = ref<string>('');
const alertList = ref<Record<string, Array<string>> | null>(null);
const startDate = ref<string | null>(null);
const endDate = ref<string | null>(null);
const searchText = ref<string>('');
const showAdvancedFilters = ref<boolean>(false);
const stockTransferCode = ref<string>('');
const productUnitCode = ref<string>('');
const productName = ref<string>('');
const selectedSourceWarehouseId = ref<string | null>(null);
const selectedDestinationWarehouseId = ref<string | null>(null);
const selectedProductCategoryId = ref<string | null>(null);
const selectedProductBrandId = ref<string | null>(null);

const stockTransferItemSerialLists = ref<Collection<Array<StockTransferItemSerial>> | null>({
    data: [],
    meta: {
        current_page: 1,
        from: null,
        last_page: 0,
        path: '',
        per_page: 10,
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

const isUserLocationSelected = computed(() => selectedUserLocationStore.isUserLocationSelected);
const selectedUserLocation = computed(() => selectedUserLocationStore.selectedUserLocation);

const sourceWarehouseDDL = ref<Array<DropDownOption> | null>(null);
const sourceWarehouseSearch = ref<string>('');
const sourceWarehouseOptions = computed(() =>
    (sourceWarehouseDDL.value ?? []).map((item) => ({
        value: item.code,
        label: item.name,
    })),
);

const destinationWarehouseDDL = ref<Array<DropDownOption> | null>(null);
const destinationWarehouseSearch = ref<string>('');
const destinationWarehouseOptions = computed(() =>
    (destinationWarehouseDDL.value ?? []).map((item) => ({
        value: item.code,
        label: item.name,
    })),
);

const productCategoryDDL = ref<Array<DropDownOption> | null>(null);
const productCategorySearch = ref<string>('');
const productCategoryOptions = computed(() =>
    (productCategoryDDL.value ?? []).map((item) => ({
        value: item.code,
        label: item.name,
    })),
);

const productBrandDDL = ref<Array<DropDownOption> | null>(null);
const productBrandSearch = ref<string>('');
const productBrandOptions = computed(() =>
    (productBrandDDL.value ?? []).map((item) => ({
        value: item.code,
        label: item.name,
    })),
);

onMounted(async () => {
    emits('mode-state', ViewMode.LIST);

    if (!isUserLocationSelected.value) {
        router.push({
            name: 'side-menu-error-code',
            params: { code: ErrorCode.USERLOCATION_REQUIRED },
        });
        return;
    }

    const now = new Date();
    const startOfMonth = new Date(now.getFullYear(), now.getMonth(), 1, 0, 0, 0);
    const endOfMonth = new Date(now.getFullYear(), now.getMonth() + 1, 0, 23, 59, 59);
    startDate.value = formatDate(startOfMonth.toString(), 'YYYY-MM-DD HH:mm:ss');
    endDate.value = formatDate(endOfMonth.toString(), 'YYYY-MM-DD HH:mm:ss');

    await getStockTransferItemSerials('', true, 1, 10);
});

const getStockTransferItemSerials = async (
    search: string,
    refresh: boolean,
    page: number,
    per_page: number,
) => {
    setLoadingState(true);
    searchText.value = search;
    const safePage = page > 0 ? page : 1;
    const safePerPage = per_page > 0 ? per_page : 10;

    const request: StockTransferItemSerialReadAnyPaginateRequest = {
        with_trashed: false,
        company_id: selectedUserLocation.value.company.id,
        branch_id: selectedUserLocation.value.branch.id,
        search,
        stock_transfer_code: stockTransferCode.value || null,
        stock_transfer_start_date: startDate.value || null,
        stock_transfer_end_date: endDate.value || null,
        stock_transfer_source_warehouse_id: selectedSourceWarehouseId.value,
        stock_transfer_destination_warehouse_id: selectedDestinationWarehouseId.value,
        product_unit_code: productUnitCode.value || null,
        product_unit_product_name: productName.value || null,
        product_unit_product_category_id: selectedProductCategoryId.value,
        product_unit_product_brand_id: selectedProductBrandId.value,
        refresh,
        page: safePage,
        per_page: safePerPage,
    };

    const result: ServiceResponse<Collection<Array<StockTransferItemSerial>> | null> =
        await stockTransferItemSerialService.readAnyPaginate(request);

    if (result.success && result.data) {
        stockTransferItemSerialLists.value = result.data;
        showAlertPlaceholder('hidden', '', null);
    } else {
        showAlertPlaceholder('danger', '', result.errors as Record<string, Array<string>>);
    }

    setLoadingState(false);
};

const handleDataListChange = async (data: DataListEmittedData) => {
    await getStockTransferItemSerials(data.search.text, true, data.pagination.page, data.pagination.per_page);
};

const handleDateFilterChange = async () => {
    const perPage = stockTransferItemSerialLists.value?.meta.per_page || 10;
    await getStockTransferItemSerials(searchText.value, true, 1, perPage);
};

const handleTextFilterChange = async () => {
    const perPage = stockTransferItemSerialLists.value?.meta.per_page || 10;
    await getStockTransferItemSerials(searchText.value, true, 1, perPage);
};

const handleSourceWarehouseFilterChange = async () => {
    const perPage = stockTransferItemSerialLists.value?.meta.per_page || 10;
    await getStockTransferItemSerials(searchText.value, true, 1, perPage);
};

const handleDestinationWarehouseFilterChange = async () => {
    const perPage = stockTransferItemSerialLists.value?.meta.per_page || 10;
    await getStockTransferItemSerials(searchText.value, true, 1, perPage);
};

const handleProductCategoryFilterChange = async () => {
    const perPage = stockTransferItemSerialLists.value?.meta.per_page || 10;
    await getStockTransferItemSerials(searchText.value, true, 1, perPage);
};

const handleProductBrandFilterChange = async () => {
    const perPage = stockTransferItemSerialLists.value?.meta.per_page || 10;
    await getStockTransferItemSerials(searchText.value, true, 1, perPage);
};

const loadSourceWarehouseDDL = async (search = '') => {
    if (!selectedUserLocation.value) return;

    const result = await warehouseService.readAnyGet({
        with_trashed: false,
        company_id: selectedUserLocation.value.company.id,
        branch_id: selectedUserLocation.value.branch.id,
        search,
        include_id: undefined,
        status: undefined,
        refresh: false,
        limit: 20,
    });

    if (result.success && result.data) {
        sourceWarehouseDDL.value = result.data.data.map((item: any) => ({
            code: item.id,
            name: item.name,
        }));
    }
};

const loadDestinationWarehouseDDL = async (search = '') => {
    if (!selectedUserLocation.value) return;

    const result = await warehouseService.readAnyGet({
        with_trashed: false,
        company_id: selectedUserLocation.value.company.id,
        branch_id: selectedUserLocation.value.branch.id,
        search,
        include_id: undefined,
        status: undefined,
        refresh: false,
        limit: 20,
    });

    if (result.success && result.data) {
        destinationWarehouseDDL.value = result.data.data.map((item: any) => ({
            code: item.id,
            name: item.name,
        }));
    }
};

const loadProductCategoryDDL = async (search = '') => {
    if (!selectedUserLocation.value) return;

    const result = await productCategoryService.readAnyGet({
        with_trashed: false,
        company_id: selectedUserLocation.value.company.id,
        search,
        type: 1,
        refresh: false,
        limit: 20,
    });

    if (result.success && result.data) {
        productCategoryDDL.value = result.data.data.map((item: any) => ({
            code: item.id,
            name: item.name,
        }));
    }
};

const loadProductBrandDDL = async (search = '') => {
    if (!selectedUserLocation.value) return;

    const result = await brandService.readAnyGet({
        with_trashed: false,
        company_id: selectedUserLocation.value.company.id,
        search,
        refresh: false,
        limit: 20,
    });

    if (result.success && result.data) {
        productBrandDDL.value = result.data.data.map((item: any) => ({
            code: item.id,
            name: item.name,
        }));
    }
};

const clearSourceWarehouseFilter = async () => {
    selectedSourceWarehouseId.value = null;
    await handleSourceWarehouseFilterChange();
};

const clearDestinationWarehouseFilter = async () => {
    selectedDestinationWarehouseId.value = null;
    await handleDestinationWarehouseFilterChange();
};

const clearProductCategoryFilter = async () => {
    selectedProductCategoryId.value = null;
    productCategorySearch.value = '';
    await handleProductCategoryFilterChange();
};

const clearProductBrandFilter = async () => {
    selectedProductBrandId.value = null;
    productBrandSearch.value = '';
    await handleProductBrandFilterChange();
};

const toggleAdvancedFilters = async () => {
    showAdvancedFilters.value = !showAdvancedFilters.value;
    if (showAdvancedFilters.value) {
        await Promise.all([
            loadSourceWarehouseDDL(sourceWarehouseSearch.value),
            loadDestinationWarehouseDDL(destinationWarehouseSearch.value),
            loadProductCategoryDDL(productCategorySearch.value),
            loadProductBrandDDL(productBrandSearch.value),
        ]);
    }
};

const editSelected = (idx: number) => {
    if (!stockTransferItemSerialLists.value) return;

    const ulid = stockTransferItemSerialLists.value.data[idx].stock_transfer?.ulid;
    if (!ulid) return;

    router.push({
        name: 'side-menu-stock-transfer-edit',
        params: { ulid },
    });
};

const formatStockTransferDate = (item: StockTransferItemSerial) => {
    const date = item.stock_transfer?.date;
    if (!date) return '-';

    return formatDate(date, 'DD-MMM-YYYY HH:mm:ss');
};

const getProductMainImageUrl = (item: StockTransferItemSerial): string | null => {
    const images = item.stock_transfer_item?.product_unit?.product?.product_images ?? [];
    if (images.length === 0) return null;

    const mainImage = images.find((img) => img.is_main);
    if (mainImage?.url) return mainImage.url;

    return images[0]?.url ?? null;
};

const setLoadingState = (state: boolean) => {
    loading.value = state;
    emits('loading-state', state);
};

const showAlertPlaceholder = (
    pAlertType: 'hidden' | 'danger' | 'success' | 'warning' | 'pending' | 'dark',
    pTitle: string,
    pAlertList: Record<string, Array<string>> | null,
) => {
    alertType.value = pAlertType;
    title.value = pTitle;
    alertList.value = pAlertList;

    const ap: AlertPlaceholderProps = {
        alertType: pAlertType,
        title: pTitle,
        alertList: pAlertList,
    };

    emits('show-alertplaceholder', ap);
};

const resetAlertPlaceholder = () => {
    title.value = '';
    alertList.value = null;
    alertType.value = 'hidden';
};
</script>

<template>
    <div>
        <LoadingOverlay :visible="loading">
            <TitleLayout>
                <template #title>
                    {{ t('views.stock_transfer_item_serial.page_title') }}
                </template>
            </TitleLayout>

            <div class="grid grid-cols-12 gap-6 mt-5">
                <div class="col-span-12 intro-y lg:col-span-12">
                    <AlertPlaceholder :alert-type="alertType" :title="title" :alert-list="alertList"
                        @dismiss="resetAlertPlaceholder" />

                    <div class="grid grid-cols-12 gap-4 gap-y-3 mb-3">
                        <div class="col-span-12 md:col-span-6 lg:col-span-3">
                            <FormLabel>{{ t('views.stock_transfer.fields.start_date') }}</FormLabel>
                            <FormInputDateTime v-model="startDate" @change="handleDateFilterChange" />
                        </div>
                        <div class="col-span-12 md:col-span-6 lg:col-span-3">
                            <FormLabel>{{ t('views.stock_transfer.fields.end_date') }}</FormLabel>
                            <FormInputDateTime v-model="endDate" @change="handleDateFilterChange" />
                        </div>
                        <div class="col-span-12 md:col-span-12 lg:col-span-2 flex items-end">
                            <Button variant="soft-secondary"
                                class="shadow-sm border-slate-300 bg-slate-100/80 hover:bg-slate-200 hover:border-slate-400 dark:border-darkmode-300 dark:bg-darkmode-300/40 dark:hover:bg-darkmode-300"
                                @click="toggleAdvancedFilters">
                                <Lucide icon="Filter" class="w-4 h-5" />
                            </Button>
                        </div>
                    </div>

                    <div v-if="showAdvancedFilters" class="grid grid-cols-12 gap-4 gap-y-3 mb-3">
                        <div class="col-span-12 md:col-span-6 lg:col-span-2">
                            <FormLabel>{{ t('views.stock_transfer.fields.stock_transfer_code') }}</FormLabel>
                            <FormInput v-model="stockTransferCode" type="text" @change="handleTextFilterChange" />
                        </div>
                        <div class="col-span-12 md:col-span-6 lg:col-span-2">
                            <FormLabel>{{ t('views.stock_transfer.fields.source_warehouse_id') }}</FormLabel>
                            <FormSelectSearch v-model="selectedSourceWarehouseId" v-model:search="sourceWarehouseSearch"
                                :options="sourceWarehouseOptions" :placeholder="t('components.dropdown.placeholder')"
                                @change="handleSourceWarehouseFilterChange" @search="loadSourceWarehouseDDL"
                                @clear="clearSourceWarehouseFilter" />
                        </div>
                        <div class="col-span-12 md:col-span-6 lg:col-span-2">
                            <FormLabel>{{ t('views.stock_transfer.fields.destination_warehouse_id') }}</FormLabel>
                            <FormSelectSearch v-model="selectedDestinationWarehouseId"
                                v-model:search="destinationWarehouseSearch" :options="destinationWarehouseOptions"
                                :placeholder="t('components.dropdown.placeholder')"
                                @change="handleDestinationWarehouseFilterChange" @search="loadDestinationWarehouseDDL"
                                @clear="clearDestinationWarehouseFilter" />
                        </div>
                        <div class="col-span-12 md:col-span-6 lg:col-span-2">
                            <FormLabel>{{ t('views.stock_transfer.fields.product_unit_code') }}</FormLabel>
                            <FormInput v-model="productUnitCode" type="text" @change="handleTextFilterChange" />
                        </div>
                        <div class="col-span-12 md:col-span-6 lg:col-span-2">
                            <FormLabel>{{ t('views.stock_transfer.fields.product_name') }}</FormLabel>
                            <FormInput v-model="productName" type="text" @change="handleTextFilterChange" />
                        </div>
                        <div class="col-span-12 md:col-span-6 lg:col-span-2">
                            <FormLabel>{{ t('views.product.fields.category_id') }}</FormLabel>
                            <FormSelectSearch v-model="selectedProductCategoryId" v-model:search="productCategorySearch"
                                :options="productCategoryOptions" :placeholder="t('components.dropdown.placeholder')"
                                @change="handleProductCategoryFilterChange" @search="loadProductCategoryDDL"
                                @clear="clearProductCategoryFilter" />
                        </div>
                        <div class="col-span-12 md:col-span-6 lg:col-span-2">
                            <FormLabel>{{ t('views.product.fields.brand_id') }}</FormLabel>
                            <FormSelectSearch v-model="selectedProductBrandId" v-model:search="productBrandSearch"
                                :options="productBrandOptions" :placeholder="t('components.dropdown.placeholder')"
                                @change="handleProductBrandFilterChange" @search="loadProductBrandDDL"
                                @clear="clearProductBrandFilter" />
                        </div>
                    </div>

                    <DataListFlex :data="stockTransferItemSerialLists" :enable-search="true"
                            :can-print="true" :can-export="true" :rows="stockTransferItemSerialLists?.data ?? []"
                            row-class="bg-white dark:bg-darkmode-600"
                            :pagination="stockTransferItemSerialLists ? stockTransferItemSerialLists.meta : null"
                            @dataListChanged="handleDataListChange">
                            <template #row="{ item, index }">
                                <div
                                    class="col-span-12 lg:col-span-1 md:col-span-12 flex items-center justify-center md:justify-start">
                                    <ProductImagePreview
                                        :image-url="getProductMainImageUrl(item as StockTransferItemSerial)"
                                        wrapper-class="w-12 h-12 rounded-md overflow-hidden bg-slate-100 dark:bg-darkmode-600 flex items-center justify-center cursor-zoom-in"
                                        icon-class="w-4 h-4 text-slate-400" />
                                </div>

                                <div class="col-span-12 lg:col-span-4 md:col-span-5 self-start">
                                    <div class="text-primary text-xs font-semibold uppercase tracking-wide mb-1">
                                        {{ t('views.stock_transfer.page_title') }}
                                    </div>
                                    <div class="text-slate-500 text-xs whitespace-nowrap">
                                        {{ t('views.stock_transfer.fields.code') }}:
                                        {{ (item as StockTransferItemSerial).stock_transfer?.code ?? '-' }}
                                    </div>
                                    <div class="text-slate-500 text-xs whitespace-nowrap">
                                        {{ t('views.stock_transfer.fields.date') }}:
                                        {{ formatStockTransferDate(item as StockTransferItemSerial) }}
                                    </div>
                                    <div class="text-slate-500 text-xs whitespace-nowrap">
                                        {{ t('views.stock_transfer.fields.source_warehouse_id') }}:
                                        {{ (item as
                                            StockTransferItemSerial).stock_transfer?.source_warehouse?.name ?? '-' }}
                                    </div>
                                    <div class="text-slate-500 text-xs whitespace-nowrap">
                                        {{ t('views.stock_transfer.fields.destination_warehouse_id') }}:
                                        {{ (item as
                                            StockTransferItemSerial).stock_transfer?.destination_warehouse?.name ??
                                        '-' }}
                                    </div>
                                    <div v-if="(item as StockTransferItemSerial).stock_transfer?.remarks?.trim()"
                                        class="text-slate-500 text-xs whitespace-nowrap">
                                        {{ t('views.stock_transfer.fields.remarks') }}:
                                        {{ (item as StockTransferItemSerial).stock_transfer?.remarks }}
                                    </div>
                                </div>

                                <div class="col-span-12 lg:col-span-4 md:col-span-4 self-start">
                                    <div class="text-primary text-xs font-semibold uppercase tracking-wide mb-1">
                                        {{ t('views.product.page_title') }}
                                    </div>
                                    <div class="text-slate-500 text-xs whitespace-nowrap">
                                        {{ t('views.product.fields.code') }}:
                                        {{ (item as
                                            StockTransferItemSerial).stock_transfer_item?.product_unit?.product?.code
                                        ?? '-' }}
                                    </div>
                                    <div class="text-slate-500 text-xs whitespace-nowrap">
                                        {{ t('views.product.fields.name') }}:
                                        {{ (item as
                                            StockTransferItemSerial).stock_transfer_item?.product_unit?.product?.name
                                        ?? '-' }}
                                    </div>
                                    <div class="text-slate-500 text-xs whitespace-nowrap">
                                        {{ t('views.product.fields.category_id') }}:
                                        {{ (item as
                                            StockTransferItemSerial).stock_transfer_item?.product_unit?.product?.category?.name
                                        ?? '-' }}
                                    </div>
                                    <div class="text-slate-500 text-xs whitespace-nowrap">
                                        {{ t('views.product.fields.brand_id') }}:
                                        {{ (item as
                                            StockTransferItemSerial).stock_transfer_item?.product_unit?.product?.brand?.name
                                        ?? '-' }}
                                    </div>
                                    <div class="text-slate-500 text-xs whitespace-nowrap">
                                        {{ t('views.product.fields.unit_code') }}:
                                        {{ (item as
                                            StockTransferItemSerial).stock_transfer_item?.product_unit?.code
                                        ?? '-' }}
                                    </div>
                                    <div class="text-slate-500 text-xs whitespace-nowrap">
                                        {{ t('views.stock_transfer_item.fields.product_unit_id') }}:
                                        {{ (item as
                                            StockTransferItemSerial).stock_transfer_item?.product_unit?.unit?.name
                                        ?? '-' }}
                                    </div>
                                </div>

                                <div class="col-span-12 lg:col-span-2 md:col-span-2 self-start">
                                    <div class="text-primary text-xs font-semibold uppercase tracking-wide mb-1">
                                        {{ t('views.product.fields.serial_number') }}
                                    </div>
                                    <div class="text-slate-500 text-xs break-all">
                                        {{ (item as StockTransferItemSerial).serial || '-' }}
                                    </div>
                                </div>

                                <div
                                    class="col-span-12 lg:col-span-1 md:col-span-12 flex justify-end items-center gap-2">
                                    <Button size="sm" variant="outline-secondary" class="flex items-center gap-1"
                                        @click="editSelected(index)">
                                        <Lucide icon="Pen" class="w-4 h-4" />
                                    </Button>
                                </div>
                            </template>
                    </DataListFlex>
                </div>
            </div>
        </LoadingOverlay>
    </div>
</template>
