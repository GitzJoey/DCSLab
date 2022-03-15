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
                        </div>
                    </div>
                    <!-- END: Item List -->
                    <!-- BEGIN: Ticket -->
                    <div class="col-span-12 lg:col-span-4 py-3">
                        <div class="intro-y pr-1">
                            <div class="box p-2">
                                <div class="pos__tabs nav nav-tabs justify-center" role="tablist"> <a id="ticket-tab" data-toggle="tab" data-target="#ticket" href="javascript:;" class="flex-1 py-2 rounded-md text-center active" role="tab" aria-controls="ticket" aria-selected="true">Ticket</a> <a id="details-tab" data-toggle="tab" data-target="#details" href="javascript:;" class="flex-1 py-2 rounded-md text-center" role="tab" aria-controls="details" aria-selected="false">Details</a> </div>
                            </div>
                        </div>
                        <div class="tab-content">
                            <div id="ticket" class="tab-pane active" role="tabpanel" aria-labelledby="ticket-tab">
                                <div class="pos__ticket box p-2 mt-5">
                                    <a href="javascript:;" data-toggle="modal" data-target="#add-item-modal" class="flex items-center p-3 cursor-pointer transition duration-300 ease-in-out bg-white dark:bg-dark-3 hover:bg-gray-200 dark:hover:bg-dark-1 rounded-md">
                                        <div class="pos__ticket__item-name truncate mr-1">Vanilla Latte</div>
                                        <div class="text-gray-600">x 1</div>
                                        <i data-feather="edit" class="w-4 h-4 text-gray-600 ml-2"></i> 
                                        <div class="ml-auto font-medium">$110</div>
                                    </a>
                                    <a href="javascript:;" data-toggle="modal" data-target="#add-item-modal" class="flex items-center p-3 cursor-pointer transition duration-300 ease-in-out bg-white dark:bg-dark-3 hover:bg-gray-200 dark:hover:bg-dark-1 rounded-md">
                                        <div class="pos__ticket__item-name truncate mr-1">Fried Calamari</div>
                                        <div class="text-gray-600">x 1</div>
                                        <i data-feather="edit" class="w-4 h-4 text-gray-600 ml-2"></i> 
                                        <div class="ml-auto font-medium">$103</div>
                                    </a>
                                    <a href="javascript:;" data-toggle="modal" data-target="#add-item-modal" class="flex items-center p-3 cursor-pointer transition duration-300 ease-in-out bg-white dark:bg-dark-3 hover:bg-gray-200 dark:hover:bg-dark-1 rounded-md">
                                        <div class="pos__ticket__item-name truncate mr-1">Root Beer</div>
                                        <div class="text-gray-600">x 1</div>
                                        <i data-feather="edit" class="w-4 h-4 text-gray-600 ml-2"></i> 
                                        <div class="ml-auto font-medium">$31</div>
                                    </a>
                                    <a href="javascript:;" data-toggle="modal" data-target="#add-item-modal" class="flex items-center p-3 cursor-pointer transition duration-300 ease-in-out bg-white dark:bg-dark-3 hover:bg-gray-200 dark:hover:bg-dark-1 rounded-md">
                                        <div class="pos__ticket__item-name truncate mr-1">Fried/Grilled Banana</div>
                                        <div class="text-gray-600">x 1</div>
                                        <i data-feather="edit" class="w-4 h-4 text-gray-600 ml-2"></i> 
                                        <div class="ml-auto font-medium">$108</div>
                                    </a>
                                    <a href="javascript:;" data-toggle="modal" data-target="#add-item-modal" class="flex items-center p-3 cursor-pointer transition duration-300 ease-in-out bg-white dark:bg-dark-3 hover:bg-gray-200 dark:hover:bg-dark-1 rounded-md">
                                        <div class="pos__ticket__item-name truncate mr-1">Chicken Wings</div>
                                        <div class="text-gray-600">x 1</div>
                                        <i data-feather="edit" class="w-4 h-4 text-gray-600 ml-2"></i> 
                                        <div class="ml-auto font-medium">$27</div>
                                    </a>
                                </div>
                                <div class="box flex p-5 mt-5">
                                    <div class="w-full relative text-gray-700">
                                        <input type="text" class="form-control py-3 px-4 w-full bg-gray-200 border-gray-200 pr-10 placeholder-theme-13" placeholder="Use coupon code...">
                                        <i class="w-4 h-4 hidden absolute-sm my-auto inset-y-0 mr-3 right-0" data-feather="search"></i> 
                                    </div>
                                    <button class="btn btn-primary ml-2">Apply</button>
                                </div>
                                <div class="box p-5 mt-5">
                                    <div class="flex">
                                        <div class="mr-auto">Subtotal</div>
                                        <div class="font-medium">$250</div>
                                    </div>
                                    <div class="flex mt-4">
                                        <div class="mr-auto">Discount</div>
                                        <div class="font-medium text-danger">-$20</div>
                                    </div>
                                    <div class="flex mt-4">
                                        <div class="mr-auto">Tax</div>
                                        <div class="font-medium">15%</div>
                                    </div>
                                    <div class="flex mt-4 pt-4 border-t border-gray-200 dark:border-dark-5">
                                        <div class="mr-auto font-medium text-base">Total Charge</div>
                                        <div class="font-medium text-base">$220</div>
                                    </div>
                                </div>
                                <div class="flex mt-5">
                                    <button class="btn w-32 border-gray-400 dark:border-dark-5 text-gray-600 dark:text-gray-300">Clear Items</button>
                                    <button class="btn btn-primary w-32 shadow-md ml-auto">Charge</button>
                                </div>
                            </div>
                            <div id="details" class="tab-pane" role="tabpanel" aria-labelledby="details-tab">
                                <div class="box p-5 mt-5">
                                    <div class="flex items-center border-b border-gray-200 dark:border-dark-5 pb-5">
                                        <div>
                                            <div class="text-gray-600">Time</div>
                                            <div class="mt-1">02/06/20 02:10 PM</div>
                                        </div>
                                        <i data-feather="clock" class="w-4 h-4 text-gray-600 ml-auto"></i> 
                                    </div>
                                    <div class="flex items-center border-b border-gray-200 dark:border-dark-5 py-5">
                                        <div>
                                            <div class="text-gray-600">Customer</div>
                                            <div class="mt-1">Johnny Depp</div>
                                        </div>
                                        <i data-feather="user" class="w-4 h-4 text-gray-600 ml-auto"></i> 
                                    </div>
                                    <div class="flex items-center border-b border-gray-200 dark:border-dark-5 py-5">
                                        <div>
                                            <div class="text-gray-600">People</div>
                                            <div class="mt-1">3</div>
                                        </div>
                                        <i data-feather="users" class="w-4 h-4 text-gray-600 ml-auto"></i> 
                                    </div>
                                    <div class="flex items-center pt-5">
                                        <div>
                                            <div class="text-gray-600">Table</div>
                                            <div class="mt-1">21</div>
                                        </div>
                                        <i data-feather="mic" class="w-4 h-4 text-gray-600 ml-auto"></i> 
                                    </div>
                                </div>
                            </div>
                        </div>
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
import { inject, onMounted, ref, computed, watch } from "vue";
import axios from "@/axios";
import { useI18n } from "vue-i18n";
import { route } from "@/ziggy";
import { useUserContextStore } from "@/stores/user-context";
import dom from "@left4code/tw-starter/dist/js/dom";
import DataList from "@/global-components/data-list/Main";
import AlertPlaceholder from "@/global-components/alert-placeholder/Main";
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
function getAllPO(args) {
    poList.value = {};
    if (args.pageSize === undefined) args.pageSize = 10;
    if (args.search === undefined) args.search = '';

    let companyId = selectedUserCompany.value;

    axios.get(route('api.get.db.purchase_order.purchaseorder.read', { "companyId": companyId, "page": args.page, "perPage": args.pageSize, "search": args.search })).then(response => {
        poList.value = response.data;
        loading.value = false;
    });
}

function onSubmit(values, actions) {
    loading.value = true;

    var formData = new FormData(dom('#poForm')[0]); 

    if (mode.value === 'create') {
    } else if (mode.value === 'edit') {
    } else { }
}

function resetAlertErrors() {
    alertErrors.value = [];
}

function backToList() {
    resetAlertErrors();
    mode.value = 'list';
    getAllPO({ page: poList.value.current_page, pageSize: poList.value.per_page });
}

function getDDL() {
    axios.get(route('api.get.db.common.ddl.list.statuses')).then(response => {
        statusDDL.value = response.data;
    });
}

function getDDLSync() {
    axios.get(route('api.get.db.supplier.supplier.read', {
            companyId: selectedUserCompany.value,
            paginate: false
        })).then(response => {
            supplierDDL.value = response.data;
    });

    axios.get(route('api.get.db.product.product.read', {
            companyId: selectedUserCompany.value,
            paginate: false
        })).then(response => {
            productDDL.value = response.data;
    });
}

function gotoTabs(selectedTab) {
    if (tabs.value.includes(selectedTab)) {
        tabs.value.splice(tabs.value.indexOf(selectedTab),1);
    } else {
        tabs.value.push(selectedTab);
    }
}

function reValidate(errors) {
    alertErrors.value = errors;
}

function filterProducts() {
    
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