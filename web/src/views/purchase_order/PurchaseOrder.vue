<template>
    <AlertPlaceholder :messages="alertErrors" />
    <div class="intro-y" v-if="mode === 'list'">
        <DataList :title="t('views.purchase_order.table.list_table.title')" :data="poList" v-on:createNew="createNew" v-on:dataListChange="onDataListChange" :enableSearch="true">
        </DataList>
    </div>
    <div class="intro-y box" v-if="mode !== 'list'">
        <div class="flex flex-col sm:flex-row items-center p-5 border-b border-gray-200 dark:border-dark-5">
            <h2 class="font-medium text-base mr-auto" v-if="mode === 'create'">{{ t('views.purchase_order.actions.create') }}</h2>
            <h2 class="font-medium text-base mr-auto" v-if="mode === 'edit'">{{ t('views.purchase_order.actions.edit') }}</h2>
        </div>
        <div class="loader-container bg-gray-200 border-2 border-l-white border-r-white">
            <VeeForm id="poForm" class="p-5" @submit="onSubmit" @invalid-submit="invalidSubmit" v-slot="{ handleReset, errors }">
                <div class="intro-y grid grid-cols-2 mb-5">
                    <div class="">
                        <button type="button" class="btn btn-primary shadow-md mr-2" @click="gotoTabs('po')">{{ t('views.purchase_order.tabs.purchase_order') }}</button>
                        <button type="button" class="btn btn-primary shadow-md mr-2" @click="gotoTabs('supplier')">{{ t('views.purchase_order.tabs.supplier') }}</button>
                        <button type="button" class="btn btn-primary shadow-md" @click="gotoTabs('warehouse')">{{ t('views.purchase_order.tabs.warehouse') }}</button>
                    </div>
                    <div class="flex flex-row-reverse">
                        <div class="pos-dropdown dropdown ml-auto sm:ml-0">
                            <button class="dropdown-toggle btn px-2 box text-gray-700 dark:text-gray-300" aria-expanded="false">
                                <span class="w-5 h-5 flex items-center justify-center"> <ChevronDownIcon class="w-4 h-4" /> </span>
                            </button>
                            <div class="pos-dropdown__dropdown-menu dropdown-menu">
                                <div class="dropdown-menu__content box dark:bg-dark-1 p-2">
                                    <a href="" class="flex items-center block p-2 transition duration-300 ease-in-out bg-white dark:bg-dark-1 hover:bg-gray-200 dark:hover:bg-dark-2 rounded-md"><span class="truncate">INV-0206020 - Kevin Spacey</span> </a>
                                    <a href="" class="flex items-center block p-2 transition duration-300 ease-in-out bg-white dark:bg-dark-1 hover:bg-gray-200 dark:hover:bg-dark-2 rounded-md"><span class="truncate">INV-0206022 - Arnold Schwarzenegger</span> </a>
                                    <a href="" class="flex items-center block p-2 transition duration-300 ease-in-out bg-white dark:bg-dark-1 hover:bg-gray-200 dark:hover:bg-dark-2 rounded-md"><span class="truncate">INV-0206021 - Robert De Niro</span> </a>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-primary shadow-md mr-2" @click="gotoTabs('a')">{{ t('components.buttons.new_order') }}</button>
                    </div>
                </div>
                <div class="grid grid-cols-12 gap-5">
                    <div class="intro-y col-span-12 lg:col-span-8">
                        <div class="intro-y py-3" v-if="tabs.includes('po')">
                            <div class="box p-5">
                                <div class="mb-3">
                                    <label for="inputInvoiceNo" class="form-label">{{ t('views.purchase_order.fields.invoice_no') }}</label>
                                    <input id="inputInvoiceNo" name="invoice_no" type="text" class="form-control" :placeholder="t('views.purchase_order.fields.invoice_no')" v-model="po.invoice_no" />
                                </div>
                                <div class="mb-3">
                                    <label for="selectInvoiceDate" class="form-label">{{ t('views.purchase_order.fields.invoice_date') }}</label>
                                    <VeeField name="invoice_date" v-slot="{ field }" rules="required" :label="t('views.purchase_order.fields.invoice_date')">
                                        <Litepicker v-model="po.invoice_date" class="form-control" v-bind="field" :options="invoiceDatePOpt" />
                                    </VeeField>
                                    <ErrorMessage name="invoice_date" class="text-danger" />
                                </div>
                            </div>
                        </div>
                        <div class="intro-y py-3" v-if="tabs.includes('supplier')">
                            <div class="box p-5">
                                <label for="selectSupplier" class="form-label">{{ t('views.purchase_order.fields.supplier') }}</label>
                                <VeeField as="select" id="selectSupplier" name="supplier" :class="{'form-control form-select':true, 'border-danger': errors['supplier']}" v-model="po.supplier.hId" rules="required" @blur="reValidate(errors)">
                                    <option value="">{{ t('components.dropdown.placeholder') }}</option>
                                    <option v-for="s in supplierDDL" :value="s.hId">{{ s.code }} - {{ s.name }}</option>
                                </VeeField>
                                <ErrorMessage name="supplier" class="text-danger" />
                            </div>
                        </div>
                        <div class="intro-y py-3" v-if="tabs.includes('warehouse')">
                            <div class="box p-5">
                                <label for="selectWarehouse" class="form-label">{{ t('views.purchase_order.fields.warehouse') }}</label>
                                <VeeField as="select" id="selectWarehouse" name="warehouse" :class="{'form-control form-select':true, 'border-danger': errors['warehouse']}" v-model="po.warehouse.hId" rules="required" @blur="reValidate(errors)">
                                    <option value="">{{ t('components.dropdown.placeholder') }}</option>
                                    <option v-for="w in warehouseDDL" :value="w.hId">{{ s.code }} - {{ s.name }}</option>
                                </VeeField>
                                <ErrorMessage name="warehouse" class="text-danger" />
                            </div>
                        </div>
                        <div class="intro-y" v-if="tabs.includes('products')">
                            <div class="text-gray-700 dark:text-gray-300">
                                <input type="text" v-model="productSearch" class="form-control py-3 px-4 w-full box pr-10" placeholder="Search item..." @change="filterProducts">
                                <SearchIcon class="w-4 h-4 absolute my-auto inset-y-0 mr-3 right-0" /> 
                            </div>
                            <table class="table table-report mt-2">
                                <tbody>
                                    <tr class="intro-x">
                                        <td>aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa</td>
                                    </tr>
                                    <tr class="intro-x">
                                        <td>aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa</td>
                                    </tr>
                                    <tr class="intro-x">
                                        <td>aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa</td>
                                    </tr>
                                    <tr class="intro-x">
                                        <td>aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div>
                            <div class="grid grid-cols-9 mb-3 bg-gray-700 dark:bg-dark-1 gap-2">
                                <div class="text-white p-3 font-bold col-span-2">{{ t('views.purchase_order.table.item_table.cols.product_name') }}</div>
                                <div class="text-white p-3 font-bold col-span-2">{{ t('views.purchase_order.table.item_table.cols.quantity') }}</div>
                                <div class="text-white p-3 font-bold col-span-2 text-right">{{ t('views.purchase_order.table.item_table.cols.unit') }}</div>
                                <div class="flex justify-center text-white p-3 font-bold">{{ t('views.purchase_order.table.item_table.cols.price_unit') }}</div>
                                <div class="flex justify-center text-white p-3 font-bold">{{ t('views.purchase_order.table.item_table.cols.total_price') }}</div>
                                <div class="text-white p-3 font-bold"></div>
                            </div>

                        </div>
                    </div>

                    <div class="col-span-12 lg:col-span-4 py-3">
                        <div>&nbsp;</div>
                    </div>
                </div>








                











            </VeeForm>
            <div class="loader-overlay" v-if="loading"></div>
        </div>
        <hr/>
        <div>
            <button type="button" class="btn btn-secondary w-15 m-3" @click="backToList">{{ t('components.buttons.back') }}</button>
        </div>
    </div>
</template>

<script setup>
//#region Imports
import { onMounted, ref, computed, watch } from "vue";
import axios from "@/axios";
import { useI18n } from "vue-i18n";
import route from "@/ziggy";
import { useUserContextStore } from "@/stores/user-context";
import dom from "@left4code/tw-starter/dist/js/dom";
import DataList from "@/global-components/data-list/Main.vue";
import AlertPlaceholder from "@/global-components/alert-placeholder/Main.vue";
//#endregion

//#region Declarations
const { t } = useI18n();
//#endregion

//#region Data - Pinia
const userContextStore = useUserContextStore();
const selectedUserCompany = computed(() => userContextStore.selectedUserCompany );
//#endregion

//#region Data - UI
const mode = ref('list');
const loading = ref(false);
const alertErrors = ref([]);
const deleteId = ref('');
const expandDetail = ref(null);
const tabs = ref(['po', 'supplier', 'warehouse', 'products']);
const invoiceDatePOpt = ref({
    autoApply: false, showWeekNumbers: false, 
    dropdowns: { minYear: 1990, maxYear: null, months: true, years: true, }, 
});
//#endregion

//#region Data - Views
const poList = ref([]);
const po = ref({
    invoice_no: '',
    invoice_date: '',
    supplier: {
        hId: '',
        name: ''
    },
    warehouse: {
        hId: '',
        name: ''
    },
    shipping_date: '',
    items:[],
    expenses: [],
    subtotal: 0,
    discount_pct: 0,
    discount_val: 0,
    downpayment: 0,
    grandtotal: 0,
    remarks: ''
});
const supplierDDL = ref([]);
const statusDDL = ref([]);
const warehouseDDL = ref([]);
const productDDL = ref([]);
const productSearch = ref('');
//#endregion

//#region onMounted
onMounted(() => {
    if (selectedUserCompany.value !== '') {
        getDDL();
        getDDLSync();
    }

    mode.value = "create";

    loading.value = false;
});
//#endregion

//#region Methods
const getAllPO = (args) => {
    poList.value = {};
    if (args.pageSize === undefined) args.pageSize = 10;
    if (args.search === undefined) args.search = '';

    let companyId = selectedUserCompany.value;

    axios.get(route('api.get.db.purchase_order.purchaseorder.list', { "companyId": companyId, "page": args.page, "perPage": args.pageSize, "search": args.search })).then(response => {
        poList.value = response.data;
        loading.value = false;
    });
}

const onSubmit = (values, actions) => {
    loading.value = true;

    var formData = new FormData(dom('#poForm')[0]); 

    if (mode.value === 'create') {
    } else if (mode.value === 'edit') {
    } else { }
}

const resetAlertErrors = () => {
    alertErrors.value = [];
}

const backToList = () => {
    resetAlertErrors();
    mode.value = 'list';
    getAllPO({ page: poList.value.meta.current_page, pageSize: poList.value.meta.per_page });
}

const getDDL = () => {
    axios.get(route('api.get.db.common.ddl.list.statuses')).then(response => {
        statusDDL.value = response.data;
    });
}

const getDDLSync = () => {
    axios.get(route('api.get.db.supplier.supplier.list', {
            companyId: selectedUserCompany.value,
            paginate: false,
            search: ''
        })).then(response => {
            supplierDDL.value = response.data;
    });

    axios.get(route('api.get.db.product.product.list', {
            companyId: selectedUserCompany.value,
            paginate: false,
            search: ''
        })).then(response => {
            productDDL.value = response.data;
    });
}

const gotoTabs = (selectedTab) => {
    if (tabs.value.includes(selectedTab)) {
        tabs.value.splice(tabs.value.indexOf(selectedTab),1);
    } else {
        tabs.value.push(selectedTab);
    }
}

const reValidate = (errors) => {
    alertErrors.value = errors;
}

const filterProducts = () => {
    
}
//#endregion

//#region Computed
//#endregion

//#region Watcher
watch(selectedUserCompany, () => {
    if (selectedUserCompany.value !== '') {
        getDDLSync();
    }
});
//#endregion
</script>